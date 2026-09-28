<?php

use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\FinalizePoll;
use App\Actions\Polls\RemovePollOption;
use App\Actions\Polls\SubmitResponse;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\PollAuditEntry;
use App\Domain\Polls\Models\Response;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$input = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
try {
    if ($input['operation'] === 'create') {
        $created = app(CreatePoll::class)->handle('Admin concurrency test', ['2026-10-09', '2026-10-10', '2026-10-11']);
        $body = ['poll' => $created->poll->id, 'access' => $created->adminAccess->id, 'options' => $created->poll->options()->get()->modelKeys()];
    } else {
        $poll = Poll::findOrFail($input['poll']);
        $body = match ($input['operation']) {
            'finalize' => app(FinalizePoll::class)->handle($poll, $input['access'], $input['version'], $input['option'])->only('status'),
            'remove' => app(RemovePollOption::class)->handle($poll, $input['access'], $input['version'], $input['option'], true)->only('status'),
            'submit' => app(SubmitResponse::class)->handle($poll, $input['token'], $input['payload']),
            'inspect' => [
                'status' => $poll->status,
                'option_count' => $poll->options()->count(),
                'responses' => Response::where('poll_id', $poll->id)->count(),
                'audits' => PollAuditEntry::where('poll_id', $poll->id)->count(),
                'version' => $poll->management_version,
            ],
        };
    }
    echo json_encode(['status' => 200, 'body' => $body]);
} catch (HttpException $exception) {
    echo json_encode(['status' => $exception->getStatusCode()]);
} catch (ValidationException) {
    echo json_encode(['status' => 422]);
}
