@php
    $document = $document ?? [];
@endphp

<article class="ll-doc-article-shell" data-doc-path="{{ $document['path'] ?? '' }}">
    <span class="ll-doc-article-kicker">
        <i class="bi bi-folder2-open"></i>
        {{ $document['category_name'] ?? 'Documentation' }}
    </span>

    <div class="ll-doc-article-head">
        <h1>{{ $document['title'] ?? 'Documentation' }}</h1>
        <p>{{ $document['summary'] ?? '' }}</p>

        <div class="ll-doc-article-meta">
            <span class="ll-docs-pill"><i class="bi bi-file-earmark-text"></i> {{ $document['relative_source_path'] ?? 'docs' }}</span>
            <span class="ll-docs-pill"><i class="bi bi-calendar3"></i> Updated {{ isset($document['updated_at']) ? $document['updated_at']->format('d M Y') : 'Unknown' }}</span>
            <span class="ll-docs-pill"><i class="bi bi-body-text"></i> {{ number_format($document['word_count'] ?? 0) }} words</span>
        </div>
    </div>

    <div class="ll-doc-article-prose">
        {!! $document['html'] ?? '' !!}
    </div>
</article>
