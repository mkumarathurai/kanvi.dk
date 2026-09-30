<?php

namespace App\Http\Controllers;

use App\Content\Articles;
use App\Domain\Polls\Services\PollPreview;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * The three listings. Each names the route it answers, the crumb it adds to
     * the pages beneath it, and which entries it shows.
     */
    private const INDEXES = [
        'articles.index' => [
            'path' => '/guides',
            'crumb' => 'Guides',
            'kind' => 'guide',
            'prefix' => null,
            'heading' => 'Fra “hvornår kan vi?” til en fast dato',
            'kicker' => 'Guides og inspiration',
            'intro' => 'En middag, en arbejdsdag eller den store familiefest. Her finder du hjælp til at få datoen på plads.',
            'seo_title' => 'Guides og inspiration | Kanvi',
            'description' => 'Enkle guides til at finde en fælles dato med venner, familie, bestyrelse og forening. Få aftalen i kalenderen med Kanvi.',
        ],
        'articles.situations' => [
            'path' => '/til',
            'crumb' => 'Til',
            'kind' => 'guide',
            'prefix' => '/til/',
            'heading' => 'Find en dag til det, I skal sammen',
            'kicker' => 'Guides og inspiration',
            'intro' => 'En middag, en arbejdsdag eller den store familiefest. Her finder du hjælp til at få datoen på plads.',
            'seo_title' => 'Anledninger | Kanvi',
            'description' => 'Enkle guides til at finde en fælles dato med venner, familie, bestyrelse og forening. Få aftalen i kalenderen med Kanvi.',
        ],
        'articles.help' => [
            'path' => '/hjaelp',
            'crumb' => 'Hjælp',
            'kind' => 'help',
            'prefix' => null,
            'heading' => 'Hjælp til Kanvi',
            'kicker' => 'Hjælp',
            'intro' => 'Korte vejledninger til det, du skal bruge: oprette, dele, svare, rette og vælge dagen.',
            'seo_title' => 'Hjælp | Kanvi',
            'description' => 'Korte vejledninger til Kanvi: opret en afstemning, del den, svar, ret dit svar, vælg den endelige dato og få en afstemning slettet.',
        ],
    ];

    public function show(string $article, Articles $articles): View
    {
        $page = $articles->find($article);
        $breadcrumbs = [['label' => 'Forside', 'url' => $articles->url('/')]];
        foreach (self::INDEXES as $index) {
            if ($index['path'] !== '/guides' && str_starts_with($page['path'], $index['path'].'/')) {
                $breadcrumbs[] = ['label' => $index['crumb'], 'url' => $articles->url($index['path'])];
            }
        }
        $breadcrumbs[] = ['label' => $page['label'], 'url' => $articles->url($page['path'])];

        return view('articles.show', [
            'article' => $page,
            'articles' => $articles->guides(),
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => $page['seo_title'],
                'description' => $page['description'],
                'canonical' => $articles->url($page['path']),
                'image' => $articles->url('/deling/'.$article.'.png'),
                'breadcrumbs' => $breadcrumbs,
            ],
        ]);
    }

    /**
     * The sharing image, drawn from the page title with the same renderer as poll
     * previews. Without it every shared article previewed identically.
     */
    public function preview(string $article, Articles $articles, PollPreview $preview): Response
    {
        return response($preview->render($articles->meta($article)['title'], 'Læs på kanvi.dk'), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function index(Articles $articles): View
    {
        $index = collect(self::INDEXES)->first(fn ($candidate, $route) => request()->routeIs($route));

        return view('articles.index', [
            'articles' => array_filter($articles->all(), fn ($article) => $article['kind'] === $index['kind']
                && (! $index['prefix'] || str_starts_with($article['path'], $index['prefix']))),
            'index' => $index,
            'seo' => [
                'title' => $index['seo_title'],
                'description' => $index['description'],
                'canonical' => $articles->url($index['path']),
            ],
        ]);
    }

    public function sitemap(Articles $articles): Response
    {
        $paths = ['/', ...array_column(self::INDEXES, 'path'), ...array_column($articles->all(), 'path')];

        return response()->view('articles.sitemap', ['urls' => array_map($articles->url(...), array_unique($paths))])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
