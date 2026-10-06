<?php

namespace Tests\Feature;

use App\Actions\Polls\CreatePoll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_include_umami_in_production(): void
    {
        $this->app['env'] = 'production';

        foreach (['/', '/opret'] as $url) {
            $response = $this->get($url)->assertOk();
            $head = explode('</head>', $response->getContent())[0];
            $this->assertStringContainsString('https://stats.mathi.dev/script.js', $head);
            $this->assertStringContainsString('data-website-id="fa2c9fe6-7537-4afb-835c-f47f75a9d546"', $head);
            $this->assertStringContainsString('data-domains="kanvi.dk,www.kanvi.dk"', $head);
            $this->assertStringContainsString('data-exclude-search="true"', $head);
            $this->assertStringContainsString('data-exclude-hash="true"', $head);
            // A deferred tracker that hangs delays DOMContentLoaded, so Livewire never boots.
            $this->assertStringContainsString('<script async src="https://stats.mathi.dev/script.js"', $head);
        }
    }

    public function test_local_pages_do_not_load_the_tracker(): void
    {
        $this->app['env'] = 'local';

        $this->get('/')->assertOk()->assertDontSee('stats.mathi.dev');
        $this->get('/opret')->assertOk()->assertDontSee('stats.mathi.dev');
    }

    public function test_private_poll_and_recovery_pages_never_load_the_tracker(): void
    {
        $this->app['env'] = 'production';
        // Creating a poll in production queues a funnel event; this test is about the page tracker only.
        Queue::fake();
        $created = app(CreatePoll::class)->handle('Privat sommerfest', [now()->addDays(10)->toDateString(), now()->addDays(11)->toDateString()]);

        $this->get(route('polls.show', $created->poll))->assertOk()->assertDontSee('stats.mathi.dev');
        $this->get(route('recovery.request', $created->poll))->assertOk()->assertDontSee('stats.mathi.dev');
        $this->get(route('recovery.confirm'))->assertOk()->assertDontSee('stats.mathi.dev');
        $this->get(route('polls.admin', $created->adminToken))->assertRedirect();
        $this->get(route('polls.share', $created->poll))->assertOk()->assertDontSee('stats.mathi.dev');
        $this->get(route('polls.manage', $created->poll))->assertOk()->assertDontSee('stats.mathi.dev');
    }
}
