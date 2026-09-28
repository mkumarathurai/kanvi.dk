<?php

namespace App\Http;

use App\Domain\Polls\Models\Participant;
use App\Domain\Polls\Models\Poll;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

final class PollParticipantCookie
{
    public function name(Poll $poll): string
    {
        return 'kanvi_participant_'.$poll->public_id;
    }

    public function token(Request $request, Poll $poll): ?string
    {
        $token = $request->cookie($this->name($poll));

        return is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token) ? $token : null;
    }

    public function participant(Request $request, Poll $poll): ?Participant
    {
        $token = $this->token($request, $poll);

        return $token ? Participant::where('poll_id', $poll->id)->where('edit_token_hash', hash('sha256', $token))->first() : null;
    }

    public function issue(Request $request, Poll $poll): Cookie
    {
        // Laravel encrypts this HttpOnly cookie; JavaScript never receives the edit token.
        return cookie($this->name($poll), bin2hex(random_bytes(32)), 60 * 24 * 365,
            '/p/'.$poll->public_id, null, $request->isSecure(), true, false, 'lax');
    }
}
