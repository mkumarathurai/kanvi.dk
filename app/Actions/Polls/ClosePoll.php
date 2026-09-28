<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\Poll;

final class ClosePoll
{
    public function __construct(private AdminPollMutation $mutation) {}

    public function handle(Poll $poll, string $accessId, int $version): Poll
    {
        return $this->mutation->run($poll, $accessId, $version, ['open', 'finalized'], 'closed', function (Poll $locked) {
            $locked->status = 'closed';

            return [];
        });
    }
}
