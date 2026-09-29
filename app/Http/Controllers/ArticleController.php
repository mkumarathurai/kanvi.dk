<?php

namespace App\Http\Controllers;

use App\Content\Articles;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(string $article, Articles $articles): View
    {
        $page = $articles->find($article);
        $breadcrumbs = [['label' => 'Forside', 'url' => $articles->url('/')]];
        if (str_starts_with($page['path'], '/til/')) {
            $breadcrumbs[] = ['label' => 'Til', 'url' => $articles->url('/til')];
        }
        $breadcrumbs[] = ['label' => $page['label'], 'url' => $articles->url($page['path'])];

        return view('articles.show', [
            'article' => $page,
            'articles' => $articles->all(),
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => $page['seo_title'],
                'description' => $page['description'],
                'canonical' => $articles->url($page['path']),
                'breadcrumbs' => $breadcrumbs,
            ],
        ]);
    }

    public function index(Articles $articles): View
    {
        $situations = request()->routeIs('articles.situations');
        $path = $situations ? '/til' : '/guides';
        $title = $situations ? 'Find en dag til det, I skal sammen' : 'Fra “hvornår kan vi?” til en fast dato';
        $description = 'Enkle guides til at finde en fælles dato med venner, familie, bestyrelse og forening. Få aftalen i kalenderen med Kanvi.';

        return view('articles.index', [
            'articles' => array_filter($articles->all(), fn ($article) => ! $situations || str_starts_with($article['path'], '/til/')),
            'title' => $title,
            'seo' => ['title' => ($situations ? 'Anledninger' : 'Guides og inspiration').' | Kanvi', 'description' => $description, 'canonical' => $articles->url($path)],
        ]);
    }

    public function sitemap(Articles $articles): Response
    {
        $paths = ['/', '/guides', '/til', ...array_column($articles->all(), 'path')];

        return response()->view('articles.sitemap', ['urls' => array_map($articles->url(...), $paths)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
