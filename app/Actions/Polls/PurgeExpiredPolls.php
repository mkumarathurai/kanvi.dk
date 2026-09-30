<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\AdminRecoveryLink;
use App\Domain\Polls\Models\Poll;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class PurgeExpiredPolls
{
    /**
     * Deletes every poll whose retention window has passed, together with its
     * options, participants, responses, revisions, admin access and audit trail.
     * Returns the number of polls deleted.
     */
    public function handle(): int
    {
        $deleted = 0;
        $this->expired($this->cutoff())->chunkById(100, function ($polls) use (&$deleted) {
            foreach ($polls as $poll) {
                $this->purge($poll);
                $deleted++;
            }
        });

        return $deleted;
    }

    public function cutoff(): Carbon
    {
        return now()->subMonths((int) config('kanvi.retention_months'));
    }

    private function expired(Carbon $cutoff)
    {
        // A poll with no recorded activity is left alone rather than guessed about.
        return Poll::withTrashed()->whereNotNull('last_activity_at')->where('last_activity_at', '<=', $cutoff);
    }

    private function purge(Poll $poll): void
    {
        DB::transaction(function () use ($poll) {
            // admin_recovery_links does not cascade from poll_admin_access, so it goes first.
            // A finalized poll references its own option, which MySQL will not let the delete
            // cascade through, so the reference is cleared first.
            // Everything else is removed by the database when the poll row is deleted.
            AdminRecoveryLink::whereIn('admin_access_id', $poll->adminAccess()->select('id'))->delete();
            Poll::withTrashed()->whereKey($poll->id)->update(['final_option_id' => null]);
            Poll::withTrashed()->whereKey($poll->id)->forceDelete();
        }, 3);
    }
}
