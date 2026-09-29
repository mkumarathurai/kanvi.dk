<?php

namespace Tests\Feature;

use App\Actions\Polls\CreatePoll;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\PollOption;
use App\Livewire\CreatePoll as CreatePollComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class PollCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_can_create_a_poll_and_open_both_views(): void
    {
        Livewire::test(CreatePollComponent::class)
            ->set('title', ' Sommerfest med naboerne ')
            ->call('next')
            ->assertSet('step', 2)
            ->call('toggleDate', '2026-10-10')
            ->call('toggleDate', '2026-10-09')
            ->call('review')
            ->assertSet('step', 3)
            ->assertSee('Er alt klar?')
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(route('polls.share', Poll::sole()));

        $poll = Poll::sole();
        $this->assertSame('Sommerfest med naboerne', $poll->title);
        $this->assertSame(['2026-10-09', '2026-10-10'], $poll->options->map(fn ($option) => $option->date_value->toDateString())->all());
        $this->get(route('polls.share', $poll))->assertOk()->assertSee('Din afstemning');
        $this->get(route('polls.show', $poll))->assertOk()->assertSee($poll->title)->assertSee('9. oktober 2026');
    }

    public function test_homepage_offers_examples_and_answers_without_creating_polls(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('Prøveafstemning · dine valg gemmes ikke.')
            ->assertSee('Der er altid noget, der skal passe sammen.')
            ->assertSee('Kan deltagerne ændre deres svar?')
            ->assertSee(route('polls.create', ['title' => 'Bestyrelsesmøde']), false);
        $this->assertDatabaseCount('polls', 0);
    }

    public function test_occasion_prefills_editable_title_and_survives_next_step(): void
    {
        Livewire::withQueryParams(['title' => '  Bestyrelsesmøde  '])
            ->test(CreatePollComponent::class, ['landing' => false])
            ->assertSet('title', 'Bestyrelsesmøde')
            ->set('title', 'Bestyrelsesmøde i klubben')
            ->call('next')->assertHasNoErrors()->assertSet('step', 2)
            ->assertSet('title', 'Bestyrelsesmøde i klubben');
        $this->assertDatabaseCount('polls', 0);
    }

    public function test_title_suggestion_handles_array_input_and_limits_length(): void
    {
        Livewire::withQueryParams(['title' => ['unexpected']])
            ->test(CreatePollComponent::class, ['landing' => false])->assertSet('title', '');
        Livewire::withQueryParams(['title' => str_repeat('ø', 200)])
            ->test(CreatePollComponent::class, ['landing' => false])->assertSet('title', str_repeat('ø', 140));
    }

    public function test_calendar_removes_dates_and_keeps_them_when_going_back(): void
    {
        Livewire::test(CreatePollComponent::class)
            ->set('title', 'Fødselsdag')->call('next')
            ->call('toggleDate', '2026-10-09')
            ->call('toggleDate', '2026-10-10')
            ->call('toggleDate', '2026-10-09')
            ->assertSet('dates', ['2026-10-10'])
            ->call('back')->call('next')
            ->assertSet('dates', ['2026-10-10'])
            ->call('create')->assertHasErrors('dates');
        $this->assertDatabaseCount('polls', 0);
    }

    public function test_dedicated_creation_starts_with_title_and_back_keeps_input(): void
    {
        $this->get(route('polls.create'))->assertOk()->assertSee('Titel på afstemningen')->assertDontSee('home-hero');
        Livewire::test(CreatePollComponent::class, ['landing' => false])
            ->assertSet('step', 1)->assertSee('Titel på afstemningen')
            ->set('title', 'Sommerfest')->call('next')
            ->call('toggleDate', '2026-10-09')->call('back')
            ->assertSet('step', 1)->assertSet('title', 'Sommerfest')
            ->assertSet('dates', ['2026-10-09'])->assertSee('Titel på afstemningen');
    }

    public function test_review_requires_two_dates_and_preserves_edits_without_creating_a_poll(): void
    {
        Livewire::test(CreatePollComponent::class)
            ->set('title', 'Fælles middag')->call('next')
            ->call('toggleDate', '2026-10-09')
            ->call('review')->assertHasErrors('dates')->assertSet('step', 2)
            ->call('toggleDate', '2026-10-10')
            ->call('review')->assertHasNoErrors()->assertSet('step', 3)
            ->assertSee('Fælles middag')->assertSee('2 datoer')
            ->call('back')->assertSet('step', 2)
            ->assertSet('dates', ['2026-10-09', '2026-10-10'])
            ->call('toggleDate', '2026-10-11')
            ->call('review')->assertSet('step', 3)->assertSee('3 datoer');

        $this->assertDatabaseCount('polls', 0);
    }

    public function test_action_hashes_admin_secret_and_never_uses_public_id_as_token(): void
    {
        $created = app(CreatePoll::class)->handle('Middag', ['2026-10-09', '2026-10-10']);
        $this->assertSame(hash('sha256', $created->adminToken), $created->adminAccess->token_hash);
        $this->assertNotSame($created->adminToken, $created->adminAccess->token_hash);
        $this->assertNotSame($created->poll->public_id, $created->adminToken);
        $this->assertStringNotContainsString('token_hash', $created->adminAccess->toJson());
    }

    #[DataProvider('invalidInput')]
    public function test_action_rejects_invalid_input_without_writes(string $title, array $dates): void
    {
        try {
            app(CreatePoll::class)->handle($title, $dates);
            $this->fail('Expected validation failure.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('polls', 0);
            $this->assertDatabaseCount('poll_options', 0);
            $this->assertDatabaseCount('poll_admin_access', 0);
        }
    }

    public static function invalidInput(): array
    {
        return [
            'blank title' => ['  ', ['2026-10-09', '2026-10-10']],
            'one date' => ['Middag', ['2026-10-09']],
            'duplicate dates' => ['Middag', ['2026-10-09', '2026-10-09']],
            'nonexistent date' => ['Middag', ['2026-02-30', '2026-10-09']],
            'timestamp instead of date' => ['Middag', ['2026-10-09T12:00:00Z', '2026-10-10']],
        ];
    }

    public function test_option_failure_rolls_back_entire_poll(): void
    {
        $count = 0;
        PollOption::creating(function () use (&$count) {
            if (++$count === 2) {
                throw new RuntimeException('Simulated storage failure');
            }
        });
        try {
            app(CreatePoll::class)->handle('Middag', ['2026-10-09', '2026-10-10']);
            $this->fail('Expected storage failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated storage failure', $exception->getMessage());
            $this->assertDatabaseCount('polls', 0);
            $this->assertDatabaseCount('poll_options', 0);
            $this->assertDatabaseCount('poll_admin_access', 0);
        } finally {
            PollOption::flushEventListeners();
        }
    }

    public function test_public_page_escapes_title_and_does_not_expose_admin_access(): void
    {
        $created = app(CreatePoll::class)->handle('<script>alert(1)</script>', ['2026-10-09', '2026-10-10']);
        $this->get(route('polls.show', $created->poll))
            ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee($created->adminToken)->assertDontSee($created->adminAccess->token_hash)
            ->assertDontSee('Del afstemningen')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->get(route('polls.share', $created->poll))->assertForbidden();
        $this->get('/admin/'.$created->poll->public_id)->assertNotFound();
        $this->get('/admin/'.str_repeat('a', 64))->assertNotFound();
    }

    public function test_admin_link_grants_only_its_own_poll_and_stores_no_plaintext_token_in_session(): void
    {
        $first = app(CreatePoll::class)->handle('Middag', ['2026-10-09', '2026-10-10']);
        $second = app(CreatePoll::class)->handle('Fødselsdag', ['2026-10-09', '2026-10-10']);
        $this->get(route('polls.admin', $first->adminToken))
            ->assertRedirect(route('polls.share', $first->poll));
        $this->assertStringNotContainsString($first->adminToken, json_encode(session()->all()));
        $this->get(route('polls.share', $first->poll))->assertOk()->assertSee($first->adminToken);
        $this->get(route('polls.share', $second->poll))->assertForbidden();
    }

    public function test_revocation_applies_to_existing_browser_session(): void
    {
        $created = app(CreatePoll::class)->handle('Middag', ['2026-10-09', '2026-10-10']);
        $this->get(route('polls.admin', $created->adminToken))->assertRedirect();
        $created->adminAccess->update(['revoked_at' => now()]);
        $this->get(route('polls.share', $created->poll))->assertForbidden();
        $this->get(route('polls.admin', $created->adminToken))->assertNotFound();
    }

    public function test_expiration_applies_to_existing_browser_session(): void
    {
        $created = app(CreatePoll::class)->handle('Middag', ['2026-10-09', '2026-10-10']);
        $created->adminAccess->update(['expires_at' => now()->addMinute()]);
        $this->get(route('polls.admin', $created->adminToken))->assertRedirect();
        $this->travel(2)->minutes();
        $this->get(route('polls.share', $created->poll))->assertForbidden();
        $this->get(route('polls.admin', $created->adminToken))->assertNotFound();
    }

    public function test_deleted_poll_cannot_be_opened_through_either_link(): void
    {
        $created = app(CreatePoll::class)->handle('Middag', ['2026-10-09', '2026-10-10']);
        $created->poll->delete();
        $this->get(route('polls.show', $created->poll))->assertNotFound();
        $this->get(route('polls.admin', $created->adminToken))->assertNotFound();
    }
}
