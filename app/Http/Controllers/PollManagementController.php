<?php

namespace App\Http\Controllers;

use App\Actions\Polls\AddPollOption;
use App\Actions\Polls\ClosePoll;
use App\Actions\Polls\FinalizePoll;
use App\Actions\Polls\RemovePollOption;
use App\Actions\Polls\ReopenPoll;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Services\PollResults;
use App\Http\PollAdminSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PollManagementController extends Controller
{
    public function show(Request $request, Poll $poll, PollAdminSession $session, PollResults $results): View
    {
        abort_unless($session->access($request, $poll), 403);
        abort_if($poll->status === 'archived', 404);

        return view('polls.manage', [
            'poll' => $poll->load(['options' => fn ($query) => $query->withCount('responses'), 'finalOption']),
            'results' => $results->forPoll($poll),
        ]);
    }

    public function update(Request $request, Poll $poll, PollAdminSession $session, string $action): RedirectResponse
    {
        $access = $session->access($request, $poll);
        abort_unless($access, 403);
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:0'],
            'date' => [$action === 'add' ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'option_id' => [in_array($action, ['remove', 'finalize']) ? 'required' : 'nullable', 'ulid'],
            'confirmed' => ['sometimes', 'boolean'],
        ], ['date.required' => 'Vælg en dato.', 'option_id.required' => 'Vælg den dato, I skal mødes.']);
        $version = (int) $data['version'];
        try {
            match ($action) {
                'add' => app(AddPollOption::class)->handle($poll, $access->id, $version, $data['date']),
                'remove' => app(RemovePollOption::class)->handle($poll, $access->id, $version, $data['option_id'], $request->boolean('confirmed')),
                'finalize' => app(FinalizePoll::class)->handle($poll, $access->id, $version, $data['option_id']),
                'reopen' => app(ReopenPoll::class)->handle($poll, $access->id, $version),
                'close' => app(ClosePoll::class)->handle($poll, $access->id, $version),
            };
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409 || $request->expectsJson()) {
                throw $exception;
            }

            return redirect()->route('polls.manage', $poll)->withErrors(['management' => $exception->getMessage()]);
        }
        $message = match ($action) {
            'add' => 'Datoen er tilføjet. Eksisterende deltagere har ikke svaret på den endnu.',
            'remove' => 'Datoen er fjernet. Tidligere svar er bevaret i historikken.',
            'finalize' => 'Så er dagen fundet ✓',
            'reopen' => 'Afstemningen er åben igen. Svarene er bevaret, og der er ikke længere valgt en endelig dato.',
            'close' => 'Afstemningen er lukket for svar.',
        };

        return redirect()->route('polls.manage', $poll)->with('status', $message);
    }
}
