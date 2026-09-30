<?php

namespace App\Actions\Polls;

use App\Domain\Analytics\FunnelEvent;
use App\Domain\Polls\Models\Participant;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\Response;
use App\Domain\Polls\Models\ResponseRevision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class SubmitResponse
{
    public function handle(Poll $poll, #[\SensitiveParameter] string $token, array $input): array
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token), 403);
        if (isset($input['changes']) && is_array($input['changes'])) {
            foreach ($input['changes'] as &$change) {
                if (is_array($change) && ($change['field'] ?? null) === 'name' && is_string($change['value'] ?? null)) {
                    $change['value'] = trim($change['value']);
                }
            }
            unset($change);
        }
        $data = Validator::make($input, [
            'editor_id' => ['required', 'regex:/\A[a-f0-9]{32}\z/'],
            'changes' => ['required', 'array', 'min:1', 'max:61'],
            'changes.*' => ['required', 'array:field,value,revision'],
            'changes.*.field' => ['required', 'string', 'distinct', 'regex:/\A(?:name|[0-9a-hjkmnp-tv-z]{26})\z/i'],
            'changes.*.value' => ['required', 'string', 'max:80'],
            'changes.*.revision' => ['required', 'integer', 'min:1', 'max:1000000000'],
        ], ['changes.*.value.required' => 'Skriv dit navn, før svaret kan gemmes.'])->validate();

        return DB::transaction(function () use ($poll, $token, $data) {
            // This same poll lock must also be acquired by future close/finalize/option actions.
            $lockedPoll = Poll::whereKey($poll->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedPoll->status === 'open', 409, 'Afstemningen er lukket for svar. Din ændring blev ikke gemt.');
            $changes = collect($data['changes'])->keyBy('field');
            foreach ($changes as $field => $change) {
                if ($field !== 'name') {
                    if (! in_array($change['value'], ['can', 'maybe', 'cannot'], true) || ! $lockedPoll->options()->whereKey($field)->exists()) {
                        throw ValidationException::withMessages(['changes' => 'Datoen findes ikke længere, eller svaret er ugyldigt. Genindlæs afstemningen.']);
                    }
                }
            }
            $participant = Participant::withTrashed()->where('poll_id', $lockedPoll->id)
                ->where('edit_token_hash', hash('sha256', $token))->first();
            abort_if($participant?->trashed(), 403, 'Du har ikke længere adgang til at ændre disse svar.');
            if (! $participant) {
                if (! $changes->has('name') || $changes->except('name')->isEmpty()) {
                    throw ValidationException::withMessages(['changes' => 'Skriv dit navn og vælg mindst ét svar.']);
                }
                $participant = Participant::create([
                    'poll_id' => $lockedPoll->id,
                    'display_name' => $changes['name']['value'],
                    'edit_token_hash' => hash('sha256', $token),
                ]);
                // Decided under the poll lock, so simultaneous first answers still count once.
                if (Participant::withTrashed()->where('poll_id', $lockedPoll->id)->count() === 1) {
                    FunnelEvent::FirstResponse->record();
                }
            }

            $acks = [];
            foreach ($changes as $field => $change) {
                $key = ['participant_id' => $participant->id, 'editor_id' => $data['editor_id'], 'field_key' => $field];
                $previous = ResponseRevision::where($key)->first();
                $fingerprint = hash('sha256', $change['value']);
                if ($previous && $previous->revision === (int) $change['revision'] && ! hash_equals($previous->payload_hash, $fingerprint)) {
                    abort(422, 'Samme revision kan ikke bruges til forskellige svar. Genindlæs siden.');
                }
                if (! $previous || $previous->revision < $change['revision']) {
                    if ($field === 'name') {
                        $participant->update(['display_name' => $change['value']]);
                    } else {
                        Response::updateOrCreate([
                            'participant_id' => $participant->id,
                            'poll_option_id' => $field,
                        ], ['poll_id' => $lockedPoll->id, 'value' => $change['value']]);
                    }
                    ResponseRevision::updateOrCreate($key, ['revision' => $change['revision'], 'payload_hash' => $fingerprint]);
                }
                // Replayed/older mutations are acknowledged without writing again.
                $acks[] = ['field' => $field, 'revision' => (int) $change['revision']];
            }
            $lockedPoll->markActive()->save();

            return ['acknowledged' => $acks, 'participant' => $participant->fresh()->snapshot()];
        }, 3);
    }
}
