<?php

namespace App\Http\Controllers;

use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Services\PollPreview;
use Illuminate\Http\Response;

class PollPreviewController extends Controller
{
    public function __invoke(Poll $poll, PollPreview $preview): Response
    {
        abort_if($poll->status === 'archived', 404);

        return response($preview->render($poll->title), 200, ['Content-Type' => 'image/png']);
    }
}
