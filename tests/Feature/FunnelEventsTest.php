<?php

namespace Tests\Feature;

use App\Actions\Polls\ClosePoll;
use App\Actions\Polls\CreatedPoll;
use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\FinalizePoll;
use App\Actions\Polls\SubmitResponse;
use App\Domain\Analytics\FunnelEvent;
use App\Jobs\RecordFunnelEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FunnelEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->app['env'] = 'production';
    }

    private function poll(): CreatedPoll
    {
        return app(CreatePoll::class)->handle('Sommerfest', ['2026-10-09', '2026-10-10']);
    }

    private function answer(CreatedPoll $created, string $name): void
    {
        app(SubmitResponse::class)->handle($created->poll, bin2hex(random_bytes(32)), [
            'editor_id' => bin2hex(random_bytes(16)),
            'changes' => [
                ['field' => 'name', 'value' => $name, 'revision' => 1],
                ['field' => $created->poll->options()->first()->id, 'value' => 'can', 'revision' => 1],
            ],
        ]);
    }

    /** @return list<FunnelEvent> */
    private function recorded(): array
    {
        return Queue::pushed(RecordFunnelEvent::class)->map(fn (RecordFunnelEvent $job) => $job->event)->values()->all();
    }

    public function test_creating_a_poll_records_poll_created(): void
    {
        $this->poll();

        $this->assertSame([FunnelEvent::PollCreated], $this->recorded());
    }

    public function test_only_the_first_participant_records_a_first_response(): void
    {
        $created = $this->poll();

        $this->answer($created, 'Mette');
        $this->answer($created, 'Jonas');

        $this->assertSame([FunnelEvent::PollCreated, FunnelEvent::FirstResponse], $this->recorded());
    }

    public function test_finalizing_records_final_date_chosen(): void
    {
        $created = $this->poll();

        app(FinalizePoll::class)->handle($created->poll, $created->adminAccess->id,
            $created->poll->refresh()->management_version, $created->poll->options()->first()->id);

        $this->assertSame([FunnelEvent::PollCreated, FunnelEvent::FinalDateChosen], $this->recorded());
    }

    public function test_a_rejected_response_records_nothing(): void
    {
        $created = $this->poll();
        app(ClosePoll::class)->handle($created->poll, $created->adminAccess->id, $created->poll->refresh()->management_version);

        try {
            $this->answer($created, 'Mette');
            $this->fail('A closed poll accepted a response.');
        } catch (HttpException) {
        }

        $this->assertSame([FunnelEvent::PollCreated], $this->recorded());
    }

    public function test_nothing_is_recorded_outside_production(): void
    {
        $this->app['env'] = 'local';

        $created = $this->poll();
        $this->answer($created, 'Mette');

        $this->assertSame([], $this->recorded());
    }

    public function test_the_request_carries_the_event_name_and_nothing_that_identifies_a_poll_or_person(): void
    {
        Http::fake(['stats.mathi.dev/*' => Http::response(['ok' => true])]);

        (new RecordFunnelEvent(FunnelEvent::FirstResponse))->handle();

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) {
            $agent = $request->header('User-Agent')[0] ?? '';

            return $request->method() === 'POST'
                && $request->url() === 'https://stats.mathi.dev/api/send'
                && $request->data() === [
                    'type' => 'event',
                    'payload' => [
                        'website' => 'fa2c9fe6-7537-4afb-835c-f47f75a9d546',
                        'hostname' => 'kanvi.dk',
                        'url' => '/',
                        'language' => 'da',
                        'name' => 'first-response',
                    ],
                ]
                && str_starts_with($agent, 'Mozilla/5.0')
                && ! preg_match('/bot|crawl|spider|http|guzzle|curl/i', $agent);
        });
    }

    public function test_a_failed_send_throws_so_the_queue_retries(): void
    {
        Http::fake(['stats.mathi.dev/*' => Http::response('', 500)]);

        $this->expectException(RuntimeException::class);

        (new RecordFunnelEvent(FunnelEvent::PollCreated))->handle();
    }
}
