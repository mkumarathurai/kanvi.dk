<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\Poll;

final class FinalizePoll
{
    public function __construct(private AdminPollMutation $mutation) {}

    public function handle(Poll $poll, string $accessId, int $version, string $optionId): Poll
    {
        return $this->mutation->run($poll, $accessId, $version, ['open'], 'finalized', function (Poll $locked) use ($optionId) {
            $option = $locked->options()->whereKey($optionId)->firstOrFail();
            $locked->status = 'finalized';
            $locked->final_option_id = $option->id;
            $locked->finalized_at = now();

            return ['option_id' => $option->id];
        });
    }
}
