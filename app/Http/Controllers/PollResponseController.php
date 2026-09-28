<?php

namespace App\Http\Controllers;

use App\Actions\Polls\SubmitResponse;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Services\PollResults;
use App\Http\PollAdminSession;
use App\Http\PollParticipantCookie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollResponseController extends Controller
{
    public function store(Request $request, Poll $poll, PollParticipantCookie $identity, SubmitResponse $submit): JsonResponse
    {
        $token = $identity->token($request, $poll);
        abort_unless($token, 419, 'Browseradgangen er udløbet. Genindlæs siden, og prøv igen.');

        return response()->json($submit->handle($poll, $token, $request->all()));
    }

    public function results(Request $request, Poll $poll, PollParticipantCookie $identity, PollAdminSession $admin, PollResults $results): JsonResponse
    {
        abort_if($poll->status === 'archived', 404);
        $participant = $identity->participant($request, $poll);
        abort_unless($admin->access($request, $poll) || $participant?->activeResponses()->exists(), 403);

        return response()->json($results->forPoll($poll));
    }
}
