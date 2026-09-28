<?php

namespace App\Http;

use App\Domain\Polls\Models\AdminAccess;
use App\Domain\Polls\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

final class PollAdminSession
{
    public function grant(AdminAccess $access, #[\SensitiveParameter] string $token): void
    {
        Session::regenerate();
        Session::put("poll_admin.{$access->poll_id}", [
            'access_id' => $access->id,
            // Even database-backed sessions must not contain the raw token.
            'encrypted_token' => Crypt::encryptString($token),
        ]);
        $access->update(['last_used_at' => now()]);
    }

    public function access(Request $request, Poll $poll): ?AdminAccess
    {
        $id = $request->session()->get("poll_admin.{$poll->id}.access_id");

        return $id ? $poll->adminAccess()->active()->find($id) : null;
    }

    public function token(Request $request, Poll $poll): string
    {
        abort_unless($this->access($request, $poll), 403);

        return Crypt::decryptString($request->session()->get("poll_admin.{$poll->id}.encrypted_token"));
    }
}
