<?php

namespace Tests\Feature;

use App\Content\Articles;
use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class ArticlesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_all_articles_render_public_content_and_seo_without_javascript(): void
    {
        $this->assertCount(10, config('articles'));
        foreach (config('articles') as $article) {
            $response = $this->get($article['path'])->assertOk();
            $html = $response->getContent();
            $document = new DOMDocument;
            @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
            $xpath = new DOMXPath($document);
            $this->assertSame(1, $xpath->query('//h1')->length);
            $this->assertSame($article['title'], $xpath->query('//h1')->item(0)->textContent);
            $this->assertSame($article['seo_title'], $xpath->query('//title')->item(0)->textContent);
            $this->assertSame($article['description'], $xpath->query('//meta[@name="description"]')->item(0)->getAttribute('content'));
            $this->assertSame(app(Articles::class)->url($article['path']), $xpath->query('//link[@rel="canonical"]')->item(0)->getAttribute('href'));
            $this->assertGreaterThanOrEqual(3, $xpath->query('//div[@class="article-prose"]//a[@href="/opret"]')->length);
            $this->assertGreaterThan(8, $xpath->query('//div[@class="article-prose"]//h2')->length);
            $schema = json_decode($xpath->query('//script[@type="application/ld+json"]')->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('WebPage', $schema['@graph'][0]['@type']);
            $this->assertSame('BreadcrumbList', $schema['@graph'][1]['@type']);
            $response->assertDontSee('BILLEDE 1')->assertDontSee('Billedplan samlet')
                ->assertDontSee('Redaktionelle afklaringer')->assertDontSee('Artikeludkast')
                ->assertDontSee('Primært søgeord')->assertDontSee('noindex');
            $this->assertFalse($response->headers->has('X-Robots-Tag'));
            foreach ($xpath->query('//div[@class="article-prose"]//a[starts-with(@href,"#")]') as $link) {
                $this->assertSame(1, $xpath->query('//*[@id="'.substr($link->getAttribute('href'), 1).'"]')->length);
            }
        }
    }

    public function test_home_and_guide_index_link_to_every_published_article(): void
    {
        foreach (['/', '/guides'] as $path) {
            $response = $this->get($path)->assertOk();
            foreach (config('articles') as $article) {
                $response->assertSee('href="'.url($article['path']).'"', false);
            }
        }
        $this->get('/til')->assertOk()->assertSee(url('/til/foreninger'))->assertSee(url('/til/klassearrangement'));
        $this->get('/til/ukendt')->assertNotFound();
    }

    public function test_article_links_resolve_and_editorial_corrections_match_the_product(): void
    {
        $paths = ['/', '/opret', ...array_column(config('articles'), 'path')];
        foreach (array_keys(config('articles')) as $key) {
            $article = app(Articles::class)->find($key);
            preg_match_all('~href="(/[^"#]*)(?:#[^"]*)?"~', $article['html'], $matches);
            foreach ($matches[1] as $path) {
                $this->assertContains($path, $paths);
            }
        }
        $this->get('/til/venner')->assertSee('Kanvi har endnu ikke datointervaller')->assertDontSee('konkrete dage og tidspunkter');
        $this->get('/til/bestyrelser')->assertSee('Flere klokkeslæt på samme dato er endnu ikke');
        $this->get('/datoafstemning')->assertSee('holde styr på 50 svar.');
    }

    public function test_sitemap_contains_only_public_pages_and_uses_configured_origin(): void
    {
        config(['app.url' => 'https://kanvi.dk']);
        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());
        $this->assertCount(13, $xml->url);
        foreach (config('articles') as $article) {
            $response->assertSee('https://kanvi.dk'.$article['path']);
        }
        $response->assertDontSee('/p/')->assertDontSee('/admin/')->assertDontSee('/adgang/')->assertDontSee('/opret');
    }

    public function test_class_event_article_has_contextual_links_ctas_and_truthful_product_guidance(): void
    {
        $response = $this->get('/til/klassearrangement')->assertOk();
        $response->assertSee('Find dato til klassearrangement | Kanvi')
            ->assertSee('Find en dato til klassearrangementet')
            ->assertSee('Opret datoafstemning til klassen')
            ->assertSee('Kanvi lukker ikke automatisk afstemningen ved fristen.')
            ->assertSee('Klokkeslættet aftaler I i beskeden til gruppen.')
            ->assertSee('én besvarelse pr. familie')
            ->assertDontSee('datoer eller tidspunkter')
            ->assertDontSee('BILLEDE')->assertDontSee('Filnavn:');

        foreach (['/datoafstemning', '/find-en-dato', '/til/familien', '/til/foreninger', '/til/venner', '/doodle-alternativ', '/#spoergsmaal'] as $path) {
            $response->assertSee('href="'.$path.'"', false);
        }

        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $xpath = new DOMXPath($document);
        $schema = json_decode($xpath->query('//script[@type="application/ld+json"]')->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['Forside', 'Til', 'Klassearrangement'], array_column($schema['@graph'][1]['itemListElement'], 'name'));
        $this->assertSame(3, $xpath->query('//a[contains(@class,"article-cta")]')->length);
    }
}
