<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentationLibrary
{
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?: base_path('docs');
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getLibrary(): array
    {
        $documents = $this->getAllDocuments();
        $grouped = [];

        foreach ($documents as $document) {
            $categorySlug = $document['category_slug'];

            if (!isset($grouped[$categorySlug])) {
                $grouped[$categorySlug] = [
                    'slug' => $categorySlug,
                    'name' => $document['category_name'],
                    'documents' => [],
                ];
            }

            $grouped[$categorySlug]['documents'][] = $document;
        }

        $categories = array_values($grouped);

        usort($categories, function (array $left, array $right) {
            return $this->categoryOrder($left['slug']) <=> $this->categoryOrder($right['slug']);
        });

        foreach ($categories as &$category) {
            usort($category['documents'], function (array $left, array $right) {
                return [$this->documentPriority($left), Str::lower($left['title'])]
                    <=> [$this->documentPriority($right), Str::lower($right['title'])];
            });
        }

        return [
            'categories' => $categories,
            'documents' => $documents,
            'default_path' => $this->defaultDocumentPath($documents),
        ];
    }

    public function getDocument(string $path): ?array
    {
        $relativePath = trim(str_replace('\\', '/', $path), '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return null;
        }

        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath) . '.md';

        if (!File::exists($fullPath)) {
            return null;
        }

        $realBase = realpath($this->basePath);
        $realFile = realpath($fullPath);

        if ($realBase === false || $realFile === false || !Str::startsWith($realFile, $realBase)) {
            return null;
        }

        return $this->buildDocument($realFile);
    }

    public function renderDocument(array $document): array
    {
        $markdown = File::get($document['source_path']);

        return array_merge($document, [
            'html' => Str::markdown($markdown, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

    private function getAllDocuments(): array
    {
        if (!File::isDirectory($this->basePath)) {
            return [];
        }

        $documents = [];

        foreach (File::allFiles($this->basePath) as $file) {
            if (Str::lower($file->getExtension()) !== 'md') {
                continue;
            }

            $documents[] = $this->buildDocument($file->getRealPath());
        }

        usort($documents, function (array $left, array $right) {
            return [$this->categoryOrder($left['category_slug']), $this->documentPriority($left), Str::lower($left['title'])]
                <=> [$this->categoryOrder($right['category_slug']), $this->documentPriority($right), Str::lower($right['title'])];
        });

        return $documents;
    }

    private function buildDocument(string $fullPath): array
    {
        $relativePath = str_replace('\\', '/', Str::after($fullPath, $this->basePath . DIRECTORY_SEPARATOR));
        $path = Str::beforeLast($relativePath, '.md');
        $segments = explode('/', $path);
        $categorySlug = $segments[0] ?? 'general';
        $filename = end($segments) ?: 'document';
        $markdown = File::get($fullPath);
        $title = $this->extractTitle($markdown, $filename);
        $summary = $this->extractSummary($markdown, $title);

        return [
            'path' => $path,
            'title' => $title,
            'slug' => $filename,
            'category_slug' => $categorySlug,
            'category_name' => $this->humanize($categorySlug),
            'summary' => $summary,
            'source_path' => $fullPath,
            'relative_source_path' => 'docs/' . $relativePath,
            'updated_at' => Carbon::createFromTimestamp(File::lastModified($fullPath)),
            'word_count' => str_word_count(strip_tags($markdown)),
        ];
    }

    private function extractTitle(string $markdown, string $fallback): string
    {
        foreach (preg_split("/\r\n|\n|\r/", $markdown) as $line) {
            $trimmed = trim($line);

            if (Str::startsWith($trimmed, '# ')) {
                return trim(Str::after($trimmed, '# '));
            }
        }

        return $this->humanize($fallback);
    }

    private function extractSummary(string $markdown, string $title): string
    {
        $plain = preg_replace('/^#{1,6}\s+/m', '', $markdown);
        $plain = preg_replace('/^\s*[-*+]\s+/m', '', $plain);
        $plain = preg_replace('/^\s*\d+\.\s+/m', '', $plain);
        $plain = preg_replace('/`([^`]+)`/', '$1', $plain);
        $plain = strip_tags(Str::markdown($plain));
        $plain = preg_replace('/\s+/', ' ', $plain ?? '');
        $plain = trim((string) $plain);

        if ($plain === '') {
            return $title;
        }

        if (Str::startsWith(Str::lower($plain), Str::lower($title))) {
            $plain = trim(Str::after($plain, $title));
        }

        return Str::limit(ltrim($plain, ". \t\n\r\0\x0B"), 170);
    }

    private function defaultDocumentPath(array $documents): ?string
    {
        foreach (['livelatch-meta/welcome', 'livelatch-meta/fork-history'] as $preferredPath) {
            foreach ($documents as $document) {
                if ($document['path'] === $preferredPath) {
                    return $preferredPath;
                }
            }
        }

        return $documents[0]['path'] ?? null;
    }

    private function humanize(string $value): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $value));
    }

    private function categoryOrder(string $slug): string
    {
        $preferred = [
            'livelatch-meta' => '01',
            'supabase' => '02',
            'latchdeck' => '03',
            'stripe' => '04',
            'encore' => '05',
            'platform-runtime' => '06',
            'open-graph' => '07',
        ];

        return ($preferred[$slug] ?? '99') . '-' . $slug;
    }

    private function documentPriority(array $document): string
    {
        return $document['slug'] === 'welcome' ? '00' : '10';
    }
}
