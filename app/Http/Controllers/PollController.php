<?php

namespace App\Http\Controllers;

use App\Domain\Polls\Models\AdminAccess;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Services\RecoveryMail;
use App\Http\PollAdminSession;
use App\Http\PollParticipantCookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PollController extends Controller
{
    public function show(Request $request, Poll $poll, PollAdminSession $session, PollParticipantCookie $identity): Response
    {
        abort_if($poll->status === 'archived', 404);
        $participant = $identity->participant($request, $poll);
        $isAdmin = $session->access($request, $poll) !== null;
        $response = response()->view('polls.show', [
            'poll' => $poll->load('options'),
            'isAdmin' => $isAdmin,
            'initial' => [
                'participant' => $participant?->snapshot(),
                'canSeeResults' => $isAdmin || ($participant?->activeResponses()->exists() ?? false),
                'isOpen' => $poll->status === 'open',
                'finalDate' => $poll->finalDateLabel(),
                'optionIds' => $poll->options->modelKeys(),
                'saveUrl' => route('polls.responses', $poll),
                'resultsUrl' => route('polls.results', $poll),
                'csrf' => csrf_token(),
            ],
        ]);

        if ($poll->status === 'open' && ! $identity->token($request, $poll)) {
            $response->withCookie($identity->issue($request, $poll));
        }

        return $response;
    }

    public function share(Request $request, Poll $poll, PollAdminSession $session): View
    {
        $token = $session->token($request, $poll);

        return view('polls.share', [
            'poll' => $poll->load('options'),
            'publicUrl' => route('polls.show', $poll),
            'adminUrl' => route('polls.admin', $token),
            'recoveryEmail' => $session->access($request, $poll)?->email,
            'mailEnabled' => app(RecoveryMail::class)->enabled(),
        ]);
    }

    public function admin(Request $request, #[\SensitiveParameter] string $token, PollAdminSession $session): RedirectResponse
    {
        $access = AdminAccess::query()->active()->whereHas('poll')
            ->where('token_hash', hash('sha256', $token))->firstOrFail();

        $session->grant($access, $token);

        return redirect()->route('polls.share', $access->poll);
    }
}
