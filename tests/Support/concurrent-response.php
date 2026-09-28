<?php

use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\SubmitResponse;
use App\Domain\Polls\Models\Participant;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\Response;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$input = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);

if ($input['operation'] === 'create') {
    $created = app(CreatePoll::class)->handle('Concurrency test', ['2026-10-09', '2026-10-10']);
    echo json_encode(['poll' => $created->poll->id, 'option' => $created->poll->options()->first()->id]);
} elseif ($input['operation'] === 'submit') {
    $poll = Poll::findOrFail($input['poll']);
    echo json_encode(app(SubmitResponse::class)->handle($poll, $input['token'], $input['payload']));
} else {
    echo json_encode([
        'participants' => Participant::count(),
        'responses' => Response::count(),
        'value' => Response::sole()->value,
    ]);
}
