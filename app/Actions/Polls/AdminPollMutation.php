<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\PollAuditEntry;
use Closure;
use Illuminate\Support\Facades\DB;

final class AdminPollMutation
{
    public function run(Poll $poll, string $accessId, int $expectedVersion, array $allowedStatuses, string $action, Closure $change): Poll
    {
        return DB::transaction(function () use ($poll, $accessId, $expectedVersion, $allowedStatuses, $action, $change) {
            $locked = Poll::whereKey($poll->id)->lockForUpdate()->firstOrFail();
            $access = $locked->adminAccess()->active()->whereKey($accessId)->lockForUpdate()->first();
            abort_unless($access, 403, 'Din administrationsadgang er ikke længere gyldig.');
            abort_unless($locked->management_version === $expectedVersion, 409,
                'Afstemningen er ændret siden du åbnede siden. Se de aktuelle datoer og prøv igen.');
            abort_unless(in_array($locked->status, $allowedStatuses, true), 409,
                'Handlingen er ikke mulig i afstemningens aktuelle tilstand.');

            $before = ['status' => $locked->status, 'final_option_id' => $locked->final_option_id];
            $details = $change($locked);
            $locked->management_version++;
            $locked->markActive()->save();
            PollAuditEntry::create([
                'poll_id' => $locked->id,
                'admin_access_id' => $access->id,
                'action' => $action,
                'details' => [
                    'before' => $before,
                    'after' => ['status' => $locked->status, 'final_option_id' => $locked->final_option_id],
                    'version' => $locked->management_version,
                    ...$details,
                ],
            ]);

            return $locked;
        }, 3);
    }
}
