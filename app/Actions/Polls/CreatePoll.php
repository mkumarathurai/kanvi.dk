<?php

namespace App\Actions\Polls;

use App\Domain\Analytics\FunnelEvent;
use App\Domain\Polls\Models\Poll;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class CreatePoll
{
    public function handle(string $title, array $dates): CreatedPoll
    {
        $data = Validator::make(['title' => trim($title), 'dates' => $dates], [
            'title' => ['required', 'string', 'max:140'],
            'dates' => ['required', 'array', 'min:2', 'max:60'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'distinct'],
        ], [
            'title.required' => 'Skriv, hvad I skal finde en dag til.',
            'title.max' => 'Brug højst 140 tegn til spørgsmålet.',
            'dates.required' => 'Vælg mindst to datoer.',
            'dates.min' => 'Vælg mindst to forskellige datoer.',
            'dates.max' => 'Vælg højst 60 datoer.',
            'dates.*.date_format' => 'Vælg en gyldig dato i kalenderen.',
            'dates.*.distinct' => 'Den samme dato kan kun vælges én gang.',
        ])->validate();

        sort($data['dates']);

        return DB::transaction(function () use ($data) {
            // 96 bits of entropy; public IDs carry no administration rights.
            $poll = Poll::make([
                'public_id' => bin2hex(random_bytes(12)),
                'title' => $data['title'],
                'type' => 'date',
                'status' => 'open',
                'timezone' => 'Europe/Copenhagen',
                'locale' => 'da',
            ])->markActive();
            $poll->save();

            foreach ($data['dates'] as $index => $date) {
                $poll->options()->create(['kind' => 'date', 'date_value' => $date, 'sort_order' => $index]);
            }

            $token = bin2hex(random_bytes(32));
            $access = $poll->adminAccess()->create(['token_hash' => hash('sha256', $token)]);
            FunnelEvent::PollCreated->record();

            return new CreatedPoll($poll, $access, $token);
        });
    }
}
