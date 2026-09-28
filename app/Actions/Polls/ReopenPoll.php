<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\Poll;
use Illuminate\Validation\ValidationException;

final class ReopenPoll
{
    public function __construct(private AdminPollMutation $mutation) {}

    public function handle(Poll $poll, string $accessId, int $version): Poll
    {
        return $this->mutation->run($poll, $accessId, $version, ['finalized', 'closed'], 'reopened', function (Poll $locked) {
            if ($locked->options()->count() < 2) {
                throw ValidationException::withMessages(['management' => 'Der skal være mindst to aktive datoer for at genåbne.']);
            }
            $locked->status = 'open';
            $locked->final_option_id = null;
            $locked->finalized_at = null;

            return [];
        });
    }
}
