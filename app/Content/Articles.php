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

    /** The long SEO guides, without the informational pages that share this renderer. */
    public function guides(): array
    {
        return array_filter($this->all(), fn ($article) => $article['kind'] === 'guide');
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
        foreach ($document->getElementsByTagName('img') as $image) {
            $src = $image->getAttribute('src');
            if (! preg_match('~^/images/articles/[a-z0-9-]+\.webp$~D', $src) || ! is_file(public_path($src))) {
                continue;
            }
            [$width, $height] = getimagesize(public_path($src));
            $image->setAttribute('width', (string) $width);
            $image->setAttribute('height', (string) $height);
            $image->setAttribute('loading', 'lazy');
            $image->setAttribute('decoding', 'async');
            $variants = [];
            foreach ([390, 720] as $variantWidth) {
                $variant = substr($src, 0, -5).'-'.$variantWidth.'.webp';
                if (is_file(public_path($variant))) {
                    $variants[] = $variant.' '.$variantWidth.'w';
                }
            }
            $variants[] = $src.' '.$width.'w';
            $image->setAttribute('srcset', implode(', ', $variants));
            $image->setAttribute('sizes', '(max-width: 699px) calc(100vw - 40px), (max-width: 1000px) calc(100vw - 330px), (max-width: 1244px) calc(100vw - 414px), 720px');
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
