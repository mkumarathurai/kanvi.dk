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

    /** The long SEO guides. Informational pages are held to their own bar, further down. */
    private function guides(): array
    {
        return array_filter(config('articles'), fn ($article) => $article['kind'] === 'guide');
    }

    public function test_all_articles_render_public_content_and_seo_without_javascript(): void
    {
        $this->assertCount(10, $this->guides());
        foreach ($this->guides() as $article) {
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
            foreach ($this->guides() as $article) {
                $response->assertSee('href="'.url($article['path']).'"', false);
            }
        }
        $this->get('/til')->assertOk()->assertSee(url('/til/foreninger'))->assertSee(url('/til/klassearrangement'));
        $this->get('/til/ukendt')->assertNotFound();
    }

    public function test_article_links_resolve_and_editorial_corrections_match_the_product(): void
    {
        $paths = ['/', '/opret', ...array_column(config('articles'), 'path')];
        foreach (array_keys($this->guides()) as $key) {
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
        $this->assertCount(14, $xml->url);
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

    public function test_class_article_illustrations_have_real_assets_dimensions_and_lazy_loading(): void
    {
        $response = $this->get('/til/klassearrangement')->assertOk();
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $xpath = new DOMXPath($document);
        $images = $xpath->query('//div[@class="article-prose"]//img');
        $this->assertCount(2, $images);
        foreach ($images as $image) {
            $file = public_path($image->getAttribute('src'));
            $this->assertFileExists($file);
            [$width, $height, $type] = getimagesize($file);
            $this->assertSame(IMAGETYPE_WEBP, $type);
            $this->assertSame((string) $width, $image->getAttribute('width'));
            $this->assertSame((string) $height, $image->getAttribute('height'));
            $this->assertSame('lazy', $image->getAttribute('loading'));
            $this->assertNotEmpty($image->getAttribute('alt'));
            $this->assertNotEmpty($image->getAttribute('srcset'));
            foreach (explode(',', $image->getAttribute('srcset')) as $candidate) {
                [$src, $descriptor] = explode(' ', trim($candidate));
                $this->assertSame((string) getimagesize(public_path($src))[0].'w', $descriptor);
            }
        }
        $response->assertSee('Illustreret eksempel: 7. november passer 19 familier');
    }

    public function test_the_privacy_page_names_the_controller_contact_retention_and_analytics(): void
    {
        $response = $this->get('/privatliv')->assertOk();

        $response->assertSee('Mathi ApS')
            ->assertSee('mail@kanvi.dk')
            ->assertSee('tolv måneder')
            ->assertSee('Umami')
            ->assertDontSee('noindex');
        $this->assertFalse($response->headers->has('X-Robots-Tag'));

        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $xpath = new DOMXPath($document);
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertSame(app(Articles::class)->url('/privatliv'),
            $xpath->query('//link[@rel="canonical"]')->item(0)->getAttribute('href'));
    }

    public function test_the_privacy_page_is_not_listed_among_the_guides_but_is_in_the_sitemap(): void
    {
        // The footer links to it from every page, so only the listings themselves are checked.
        foreach (['/guides' => 'guide-card-grid', '/' => 'article-link-list'] as $path => $listing) {
            $document = new DOMDocument;
            @$document->loadHTML('<?xml encoding="utf-8" ?>'.$this->get($path)->assertOk()->getContent());
            $links = (new DOMXPath($document))->query('//*[contains(@class,"'.$listing.'")]//a[@href="'.url('/privatliv').'"]');
            $this->assertSame(0, $links->length, "The privacy page is listed in {$listing} on {$path}.");
        }
        $this->get('/sitemap.xml')->assertOk()->assertSee(app(Articles::class)->url('/privatliv'));
    }

    public function test_every_public_page_links_to_the_privacy_page(): void
    {
        foreach (['/', '/opret', '/guides', '/til', '/privatliv', '/til/klassearrangement'] as $path) {
            $this->get($path)->assertOk()->assertSee('href="'.url('/privatliv').'"', false);
        }
    }
}
