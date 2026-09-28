<?php

namespace Tests\Feature;

use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\SubmitResponse;
use App\Domain\Polls\Services\PollPreview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_metadata_is_escaped_canonical_and_contains_no_private_data(): void
    {
        config(['app.url' => 'https://kanvi.example']);
        $created = app(CreatePoll::class)->handle('Sommerfest "<script>bad</script>', ['2026-10-09', '2026-10-10']);
        app(SubmitResponse::class)->handle($created->poll, bin2hex(random_bytes(32)), [
            'editor_id' => bin2hex(random_bytes(16)), 'changes' => [
                ['field' => 'name', 'value' => 'PrivatePerson', 'revision' => 1],
                ['field' => $created->poll->options()->first()->id, 'value' => 'can', 'revision' => 2],
            ],
        ]);
        $response = $this->get(route('polls.show', $created->poll));
        $response->assertOk()->assertSee('property="og:title"', false)
            ->assertSee('https://kanvi.example/p/'.$created->poll->public_id.'/preview.png', false)
            ->assertSee('&lt;script&gt;bad&lt;/script&gt;', false)
            ->assertDontSee('PrivatePerson')->assertDontSee($created->adminToken)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get(route('polls.admin', $created->adminToken));
        $this->get(route('polls.share', $created->poll))->assertDontSee('property="og:image"', false);
    }

    public function test_preview_is_png_and_does_not_change_when_answers_change(): void
    {
        $created = app(CreatePoll::class)->handle('Sommerfest med naboerne', ['2026-10-09', '2026-10-10']);
        $first = $this->get(route('polls.preview', $created->poll))->assertOk()->assertHeader('Content-Type', 'image/png')->getContent();
        $size = getimagesizefromstring($first);
        $this->assertSame([1200, 630], [$size[0], $size[1]]);
        $created->poll->update(['status' => 'closed']);
        $this->assertSame($first, $this->get(route('polls.preview', $created->poll))->getContent());
        $created->poll->update(['status' => 'archived']);
        $this->get(route('polls.preview', $created->poll))->assertNotFound();
        $created->poll->delete();
        $this->get(route('polls.preview', $created->poll))->assertNotFound();
    }

    public function test_long_unbroken_and_danish_titles_render_without_error(): void
    {
        foreach ([str_repeat('W', 140), 'Æblefest på Østerbro – blåbær og fællesspisning', str_repeat('En lang titel ', 10)] as $title) {
            $png = app(PollPreview::class)->render($title);
            $this->assertSame('image/png', getimagesizefromstring($png)['mime']);
        }
    }
}
