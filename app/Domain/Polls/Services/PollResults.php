<?php

namespace App\Domain\Polls\Services;

use App\Domain\Polls\Models\Participant;
use App\Domain\Polls\Models\Poll;

final class PollResults
{
    public function forPoll(Poll $poll): array
    {
        $options = $poll->options()->get();
        $participants = Participant::where('poll_id', $poll->id)->whereHas('activeResponses')
            ->with('activeResponses')->orderBy('created_at')->orderBy('id')->get();
        $count = $participants->count();
        $rows = $options->map(function ($option) use ($participants) {
            $counts = ['can' => 0, 'maybe' => 0, 'cannot' => 0, 'unanswered' => 0];
            $people = $participants->map(function ($person) use ($option, &$counts) {
                $value = $person->activeResponses->firstWhere('poll_option_id', $option->id)?->value;
                $counts[$value ?? 'unanswered']++;

                return ['id' => $person->id, 'name' => $person->display_name, 'value' => $value];
            })->all();

            return [
                'id' => $option->id,
                'label' => ucfirst($option->date_value->locale('da')->translatedFormat('D j. F Y')),
                ...$counts,
                'people' => $people,
                'score' => [-$counts['can'], -$counts['maybe'], $counts['cannot']],
                'best' => false,
            ];
        })->sort(fn ($a, $b) => $a['score'] <=> $b['score'])->values();
        $best = $rows->first()['score'] ?? null;

        return [
            'status' => $poll->status,
            'final_date' => $poll->finalDateLabel(),
            'count' => $count,
            'incomplete' => $participants->filter(fn ($person) => $person->activeResponses->count() < $options->count())->count(),
            'options' => $rows->map(function ($row) use ($best, $count) {
                $row['best'] = $count > 0 && $row['score'] === $best;
                unset($row['score']);

                return $row;
            })->all(),
        ];
    }
}
