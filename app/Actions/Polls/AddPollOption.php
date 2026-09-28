<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\Poll;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AddPollOption
{
    public function __construct(private AdminPollMutation $mutation) {}

    public function handle(Poll $poll, string $accessId, int $version, string $date): Poll
    {
        Validator::make(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']], [
            'date.required' => 'Vælg en dato.', 'date.date_format' => 'Vælg en gyldig dato.',
        ])->validate();

        return $this->mutation->run($poll, $accessId, $version, ['open'], 'option_added', function (Poll $locked) use ($date) {
            if ($locked->options()->whereDate('date_value', $date)->exists()) {
                throw ValidationException::withMessages(['date' => 'Datoen er allerede med i afstemningen.']);
            }
            if ($locked->options()->count() >= 60) {
                throw ValidationException::withMessages(['date' => 'Afstemningen kan højst have 60 aktive datoer.']);
            }
            // Re-adding a previously removed date creates a new option with no old votes.
            $option = $locked->options()->create(['kind' => 'date', 'date_value' => $date, 'sort_order' => 0]);
            foreach ($locked->options()->reorder('date_value')->orderBy('id')->get() as $order => $active) {
                $active->update(['sort_order' => $order]);
            }

            return ['option_id' => $option->id, 'date' => $date];
        });
    }
}
