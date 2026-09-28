<?php

namespace App\Http\Controllers;

use App\Actions\Polls\RedeemAdminRecovery;
use App\Actions\Polls\RequestAdminRecovery;
use App\Domain\Polls\Models\AdminRecoveryLink;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Services\RecoveryMail;
use App\Http\PollAdminSession;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PollRecoveryController extends Controller
{
    public function show(Poll $poll, RecoveryMail $mail): View
    {
        abort_if($poll->status === 'archived', 404);

        return view('recovery.request', ['poll' => $poll, 'mailEnabled' => $mail->enabled()]);
    }

    public function register(Request $request, Poll $poll, PollAdminSession $session, RequestAdminRecovery $recovery): RedirectResponse
    {
        $access = $session->access($request, $poll);
        abort_unless($access, 403);
        $data = $request->validate(['email' => ['required', 'string', 'email:rfc', 'max:254']], ['email.*' => 'Skriv en gyldig mailadresse.']);
        $recovery->handle($poll, $data['email'], $access->id);

        return redirect()->route('polls.share', $poll)->with('recovery_status',
            'Mailen er lagt klar til afsendelse. Åbn linket i mailen for at bekræfte adressen. Tjek også spam, hvis den ikke dukker op.');
    }

    public function request(Request $request, Poll $poll, RequestAdminRecovery $recovery): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'string', 'email:rfc', 'max:254']], ['email.*' => 'Skriv en gyldig mailadresse.']);
        $recovery->handle($poll, $data['email']);

        return redirect()->route('recovery.request', $poll)->with('recovery_status',
            'Hvis adressen er bekræftet til denne afstemning, har vi lagt en mail klar til afsendelse. Tjek også spam.');
    }

    public function open(Request $request, #[\SensitiveParameter] string $token): RedirectResponse
    {
        $link = AdminRecoveryLink::usable()->where('token_hash', hash('sha256', $token))->first();
        if (! $link || ! $link->emailStillValid()) {
            $request->session()->forget('recovery_token');

            return redirect()->route('recovery.confirm')->with('recovery_invalid', true);
        }
        // GET never consumes a link: mail scanners may visit it automatically.
        $request->session()->put('recovery_token', encrypt($token));

        return redirect()->route('recovery.confirm');
    }

    public function confirm(Request $request): View
    {
        return view('recovery.confirm', ['valid' => $request->session()->has('recovery_token') && ! session('recovery_invalid')]);
    }

    public function redeem(Request $request, RedeemAdminRecovery $recovery, PollAdminSession $session): RedirectResponse
    {
        $encrypted = $request->session()->get('recovery_token');
        if (! $encrypted) {
            return redirect()->route('recovery.confirm')->with('recovery_invalid', true);
        }
        try {
            $created = $recovery->handle(decrypt($encrypted));
        } catch (ModelNotFoundException|NotFoundHttpException) {
            $request->session()->forget('recovery_token');

            return redirect()->route('recovery.confirm')->with('recovery_invalid', true);
        }
        $request->session()->forget('recovery_token');
        $session->grant($created->adminAccess, $created->adminToken);

        return redirect()->route('polls.share', $created->poll)->with('recovery_status', 'Din mail er bekræftet. Denne browser kan nu administrere afstemningen.');
    }
}
