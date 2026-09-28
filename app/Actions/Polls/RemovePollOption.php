<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\Poll;
use Illuminate\Validation\ValidationException;

final class RemovePollOption
{
    public function __construct(private AdminPollMutation $mutation) {}

    public function handle(Poll $poll, string $accessId, int $version, string $optionId, bool $confirmed = false): Poll
    {
        return $this->mutation->run($poll, $accessId, $version, ['open'], 'option_removed', function (Poll $locked) use ($optionId, $confirmed) {
            $option = $locked->options()->whereKey($optionId)->firstOrFail();
            if ($locked->options()->count() <= 2) {
                throw ValidationException::withMessages(['management' => 'Behold mindst to datoer i en åben afstemning.']);
            }
            $count = $option->responses()->count();
            if ($count > 0 && ! $confirmed) {
                throw ValidationException::withMessages(['management' => 'Datoen har svar. Bekræft fjernelsen; svarene bevares i historikken.']);
            }
            $option->delete();

            return ['option_id' => $option->id, 'responses_preserved' => $count];
        });
    }
}
