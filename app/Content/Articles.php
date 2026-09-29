<?php

namespace App\Content;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

final class Articles
{
    public function all(): array
    {
        return config('articles');
    }

    public function find(string $key): array
    {
        $article = $this->all()[$key] ?? null;
        abort_unless($article, 404);

        $markdown = file_get_contents(resource_path("content/articles/{$key}.md"));
        $html = Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $contents = [];
        foreach ($document->getElementsByTagName('h2') as $index => $heading) {
            $id = 'afsnit-'.($index + 1).'-'.Str::slug($heading->textContent);
            $heading->setAttribute('id', $id);
            $contents[] = ['id' => $id, 'title' => $heading->textContent];
        }
        foreach ((new DOMXPath($document))->query('//p[count(*)=1]/a[@href="/opret"]') as $link) {
            $link->setAttribute('class', 'button primary article-cta');
            $link->parentNode->setAttribute('class', 'article-cta-row');
        }
        $body = '';
        foreach ($document->documentElement->childNodes as $node) {
            $body .= $document->saveHTML($node);
        }

        return $article + ['key' => $key, 'html' => $body, 'contents' => $contents];
    }

    public function url(string $path): string
    {
        return rtrim(config('app.url'), '/').$path;
    }
}
