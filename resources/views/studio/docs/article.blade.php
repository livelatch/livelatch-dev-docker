@php
    $document = $document ?? [];
    $isPdf = ($document['type'] ?? 'markdown') === 'pdf';
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
            <span class="ll-docs-pill"><i class="bi bi-{{ $isPdf ? 'file-earmark-pdf' : 'file-earmark-text' }}"></i> {{ $isPdf ? 'PDF viewer' : 'Markdown rendered' }}</span>
            <span class="ll-docs-pill"><i class="bi bi-folder-fill"></i> {{ $document['relative_source_path'] ?? 'docs' }}</span>
            <span class="ll-docs-pill"><i class="bi bi-calendar3"></i> Updated {{ isset($document['updated_at']) ? $document['updated_at']->format('d M Y') : 'Unknown' }}</span>
            @if($isPdf)
                <span class="ll-docs-pill"><i class="bi bi-hdd"></i> {{ $document['file_size_human'] ?? '' }}</span>
                <a class="ll-docs-pill ll-docs-pill-link" href="{{ $document['file_url'] ?? '#' }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Open in new tab</a>
            @else
                <span class="ll-docs-pill"><i class="bi bi-body-text"></i> {{ number_format($document['word_count'] ?? 0) }} words</span>
            @endif
        </div>
    </div>

    @if($isPdf)
        <div class="ll-doc-pdf-frame">
            <iframe src="{{ $document['file_url'] ?? '' }}" title="{{ $document['title'] ?? 'PDF document' }}" loading="lazy"></iframe>
        </div>
    @else
        <div class="ll-doc-article-prose">
            {!! $document['html'] ?? '' !!}
        </div>
    @endif
</article>
