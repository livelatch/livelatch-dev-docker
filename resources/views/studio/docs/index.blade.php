@extends('layouts.sidebar')

@section('content')

@php
    $selectedPath = $selectedDocument['path'] ?? null;
    $categories = $library['categories'] ?? [];
    $documents = $library['documents'] ?? [];

    $docsIndex = collect($documents)->map(function ($document) {
        return [
            'path' => $document['path'],
            'title' => $document['title'],
            'summary' => $document['summary'],
            'category' => $document['category_name'],
            'category_slug' => $document['category_slug'],
            'type' => $document['type'],
            'url' => url('/studio/docs/' . $document['path']),
            'article_url' => url('/studio/docs/article/' . $document['path']),
        ];
    })->values();
@endphp

<style>
    .ll-docs {
        --docs-bg: rgba(255, 255, 255, 0.72);
        --docs-surface: rgba(255, 255, 255, 0.9);
        --docs-border: rgba(18, 15, 45, 0.08);
        --docs-shadow: 0 24px 60px rgba(22, 12, 50, 0.10);
        --docs-text: var(--ll-text);
        --docs-muted: var(--ll-muted);
        --docs-accent: var(--ll-primary, #0092ec);
        --docs-accent-soft: rgba(0, 146, 236, 0.12);
        --docs-accent-2: #12d6df;
    }

    [data-ll-theme="dark"] .ll-docs {
        --docs-bg: rgba(16, 16, 31, 0.72);
        --docs-surface: rgba(14, 14, 27, 0.92);
        --docs-border: rgba(255, 255, 255, 0.08);
        --docs-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
    }

    .ll-docs-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        border: 1px solid var(--docs-border);
        border-radius: 18px;
        padding: 16px 20px;
        background: var(--docs-surface);
        box-shadow: var(--docs-shadow);
    }

    .ll-docs-topbar-title {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .ll-docs-topbar-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 auto;
        border-radius: 13px;
        background: linear-gradient(135deg, var(--docs-accent), var(--docs-accent-2));
        color: #fff;
        font-size: 1.15rem;
    }

    .ll-docs-topbar-title h1 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--docs-text);
    }

    .ll-docs-topbar-title p {
        margin: 3px 0 0;
        color: var(--docs-muted);
        font-size: 0.84rem;
    }

    .ll-docs-topbar-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ll-docs-grid {
        display: grid;
        grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
        gap: 18px;
        margin-top: 18px;
        align-items: start;
    }

    .ll-docs-card {
        border: 1px solid var(--docs-border);
        border-radius: 20px;
        background: var(--docs-surface);
        box-shadow: var(--docs-shadow);
        backdrop-filter: blur(18px);
    }

    .ll-docs-browser {
        position: sticky;
        top: 96px;
        overflow: hidden;
    }

    .ll-docs-browser-header {
        padding: 20px 20px 0;
    }

    .ll-docs-browser-header h2 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
    }

    .ll-docs-browser-header p {
        margin: 8px 0 0;
        color: var(--docs-muted);
        font-size: 0.92rem;
    }

    .ll-docs-search {
        position: relative;
        padding: 18px 20px 16px;
    }

    .ll-docs-search input {
        width: 100%;
        min-height: 48px;
        border-radius: 14px;
        border: 1px solid var(--docs-border);
        background: var(--docs-bg);
        color: var(--docs-text);
        padding: 0 52px 0 16px;
        font-size: 0.95rem;
        font-weight: 500;
        outline: none;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
    }

    .ll-docs-search i {
        position: absolute;
        right: 36px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--docs-muted);
    }

    .ll-docs-search-results {
        position: absolute;
        left: 20px;
        right: 20px;
        top: calc(100% - 10px);
        z-index: 15;
        display: none;
        max-height: 320px;
        overflow: auto;
        border: 1px solid var(--docs-border);
        border-radius: 14px;
        background: var(--docs-surface);
        box-shadow: 0 18px 48px rgba(22, 12, 50, 0.18);
        padding: 8px;
    }

    .ll-docs-search-results.is-open {
        display: block;
    }

    .ll-docs-search-result {
        display: block;
        padding: 12px 14px;
        border-radius: 14px;
        text-decoration: none;
        color: inherit;
    }

    .ll-docs-search-result:hover,
    .ll-docs-search-result.is-active {
        background: var(--docs-accent-soft);
    }

    .ll-docs-search-result strong {
        display: block;
        font-size: 0.92rem;
    }

    .ll-docs-search-result span {
        display: block;
        margin-top: 4px;
        color: var(--docs-muted);
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .ll-docs-search-empty {
        padding: 14px;
        color: var(--docs-muted);
        font-size: 0.86rem;
    }

    .ll-docs-category-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 0 20px 14px;
    }

    .ll-docs-chip {
        border: 1px solid var(--docs-border);
        border-radius: 999px;
        background: transparent;
        color: var(--docs-muted);
        min-height: 36px;
        padding: 0 12px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .ll-docs-chip.is-active,
    .ll-docs-chip:hover {
        color: var(--docs-accent);
        background: var(--docs-accent-soft);
        border-color: rgba(0, 146, 236, 0.14);
    }

    .ll-docs-nav {
        max-height: calc(100vh - 290px);
        overflow: auto;
        padding: 4px 12px 14px;
    }

    .ll-docs-category {
        margin: 0 0 10px;
        padding: 10px 8px;
        border-radius: 14px;
    }

    .ll-docs-category.is-hidden,
    .ll-docs-link.is-hidden {
        display: none !important;
    }

    .ll-docs-category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        border: 0;
        background: transparent;
        color: var(--docs-text);
        padding: 8px 10px;
        border-radius: 14px;
        font-weight: 800;
        font-size: 0.82rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .ll-docs-category-header i {
        transition: transform 0.2s ease;
    }

    .ll-docs-category-header[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .ll-docs-doc-list {
        list-style: none;
        margin: 4px 0 0;
        padding: 0;
    }

    .ll-docs-link {
        display: block;
        padding: 12px 12px 12px 14px;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
        border: 1px solid transparent;
    }

    .ll-docs-link + .ll-docs-link {
        margin-top: 8px;
    }

    .ll-docs-link:hover {
        transform: translateX(2px);
        background: rgba(0, 146, 236, 0.05);
        border-color: rgba(0, 146, 236, 0.10);
    }

    .ll-docs-link.is-current {
        background: linear-gradient(135deg, rgba(0, 146, 236, 0.16), rgba(18, 214, 223, 0.14));
        border-color: rgba(0, 146, 236, 0.16);
    }

    .ll-docs-link {
        padding: 9px 12px;
    }

    .ll-docs-link + .ll-docs-link {
        margin-top: 4px;
    }

    .ll-docs-link strong {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--docs-text);
    }

    .ll-docs-link.is-current strong {
        font-weight: 700;
        color: var(--docs-accent);
    }

    .ll-docs-link span {
        display: block;
        margin-top: 6px;
        font-size: 0.82rem;
        line-height: 1.55;
        color: var(--docs-muted);
    }

    .ll-docs-category-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .ll-docs-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: var(--docs-accent-soft);
        color: var(--docs-accent);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .ll-docs-toc {
        margin: 0 20px;
        padding: 12px 16px;
        border: 1px solid var(--docs-border);
        border-radius: 14px;
        background: var(--docs-bg);
    }

    .ll-docs-toc[hidden] {
        display: none;
    }

    .ll-docs-toc-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--docs-muted);
    }

    .ll-docs-toc-links {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-top: 10px;
    }

    .ll-docs-toc-links a {
        display: block;
        padding: 5px 8px;
        border-radius: 10px;
        font-size: 0.86rem;
        line-height: 1.4;
        color: var(--docs-muted);
        text-decoration: none;
        border-left: 2px solid transparent;
    }

    .ll-docs-toc-links a:hover {
        color: var(--docs-accent);
        background: var(--docs-accent-soft);
    }

    .ll-docs-toc-links a.is-sub {
        padding-left: 22px;
        font-size: 0.82rem;
    }

    .ll-docs-pane {
        overflow: hidden;
    }

    .ll-docs-pane-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--docs-border);
    }

    .ll-docs-pane-header h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
    }

    .ll-docs-pane-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ll-docs-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        background: var(--docs-accent-soft);
        color: var(--docs-accent);
        padding: 8px 12px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .ll-doc-article-shell {
        padding: 24px;
    }

    .ll-doc-article-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--docs-accent-soft);
        color: var(--docs-accent);
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .ll-doc-article-head {
        margin: 18px 0 24px;
    }

    .ll-doc-article-head h1 {
        margin: 0 0 12px;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        letter-spacing: -0.06em;
    }

    .ll-doc-article-head p {
        margin: 0;
        color: var(--docs-muted);
        font-size: 0.98rem;
        line-height: 1.7;
    }

    .ll-doc-article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .ll-doc-article-prose {
        color: var(--docs-text);
        line-height: 1.8;
        font-size: 1rem;
    }

    .ll-doc-article-prose h1,
    .ll-doc-article-prose h2,
    .ll-doc-article-prose h3,
    .ll-doc-article-prose h4 {
        margin-top: 1.9em;
        margin-bottom: 0.7em;
        letter-spacing: -0.04em;
    }

    .ll-doc-article-prose h1:first-child,
    .ll-doc-article-prose h2:first-child {
        margin-top: 0;
    }

    .ll-doc-article-prose p,
    .ll-doc-article-prose ul,
    .ll-doc-article-prose ol,
    .ll-doc-article-prose blockquote {
        margin-bottom: 1rem;
    }

    .ll-doc-article-prose ul,
    .ll-doc-article-prose ol {
        padding-left: 1.25rem;
    }

    .ll-doc-article-prose code {
        border-radius: 10px;
        background: rgba(0, 146, 236, 0.10);
        color: var(--docs-accent);
        padding: 0.18rem 0.42rem;
        font-size: 0.92em;
    }

    .ll-doc-article-prose pre {
        padding: 18px;
        border-radius: 20px;
        background: #0f1122;
        color: #f6f7fb;
        overflow: auto;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }

    .ll-doc-article-prose pre code {
        background: transparent;
        color: inherit;
        padding: 0;
    }

    .ll-doc-article-prose blockquote {
        margin-left: 0;
        padding: 14px 16px;
        border-left: 4px solid var(--docs-accent);
        border-radius: 0 18px 18px 0;
        background: rgba(0, 146, 236, 0.06);
        color: var(--docs-muted);
    }

    .ll-doc-article-prose a {
        color: var(--docs-accent);
        text-decoration: underline;
        text-decoration-thickness: 1.5px;
        text-underline-offset: 3px;
    }

    .ll-doc-article-prose hr {
        border: 0;
        border-top: 1px solid var(--docs-border);
        margin: 1.8rem 0;
    }

    .ll-docs-pill-link {
        cursor: pointer;
        text-decoration: none;
        border: 1px solid transparent;
        transition: border-color 0.18s ease;
    }

    .ll-docs-pill-link:hover {
        border-color: rgba(0, 146, 236, 0.3);
    }

    .ll-doc-pdf-frame {
        border: 1px solid var(--docs-border);
        border-radius: 16px;
        overflow: hidden;
        background: var(--docs-bg);
        height: min(82vh, 1100px);
    }

    .ll-doc-pdf-frame iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }

    .ll-docs-link-icon {
        display: inline-flex;
        margin-right: 6px;
        color: var(--docs-muted);
    }

    .ll-docs-link.is-current .ll-docs-link-icon {
        color: var(--docs-accent);
    }

    @media (max-width: 1199.98px) {
        .ll-docs-grid {
            grid-template-columns: 1fr;
        }

        .ll-docs-browser {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .ll-docs-card {
            border-radius: 18px;
        }

        .ll-docs-topbar {
            border-radius: 16px;
        }

        .ll-docs-pane-header,
        .ll-doc-article-shell {
            padding: 18px;
        }

        .ll-doc-pdf-frame {
            height: 70vh;
        }
    }
</style>

<div class="container-fluid ll-docs" id="ll-docs-root" data-selected-doc="{{ $selectedPath }}">
    <section class="ll-docs-topbar">
        <div class="ll-docs-topbar-title">
            <span class="ll-docs-topbar-icon"><i class="bi bi-journal-richtext"></i></span>
            <div>
                <h1>Documentation</h1>
                <p>Markdown &amp; PDF files under <code>docs/</code> — add a file to publish a new page.</p>
            </div>
        </div>

        <div class="ll-docs-topbar-stats">
            <span class="ll-docs-pill"><i class="bi bi-folder2-open"></i> {{ count($categories) }} categories</span>
            <span class="ll-docs-pill"><i class="bi bi-file-earmark-text"></i> {{ count($documents) }} docs</span>
        </div>
    </section>

    <div class="ll-docs-grid">
        <aside class="ll-docs-card ll-docs-browser">
            <div class="ll-docs-browser-header">
                <h2>Browse the library</h2>
                <p>Search, filter by category, or open a doc without refreshing the studio shell.</p>
            </div>

            <div class="ll-docs-search">
                <input type="search" id="ll-docs-search-input" placeholder="Search docs, topics, services..." autocomplete="off">
                <i class="bi bi-search"></i>
                <div class="ll-docs-search-results" id="ll-docs-search-results"></div>
            </div>

            <div class="ll-docs-category-bar" id="ll-docs-category-bar">
                <button type="button" class="ll-docs-chip is-active" data-category-filter="all">All</button>
                @foreach($categories as $category)
                    <button type="button" class="ll-docs-chip" data-category-filter="{{ $category['slug'] }}">{{ $category['name'] }}</button>
                @endforeach
            </div>

            <div class="ll-docs-nav" id="ll-docs-nav">
                @foreach($categories as $category)
                    @php $catOpen = collect($category['documents'])->contains(fn ($d) => $d['path'] === $selectedPath); @endphp
                    <section class="ll-docs-category {{ $catOpen ? 'is-open' : '' }}" data-category="{{ $category['slug'] }}" data-default-open="{{ $catOpen ? '1' : '0' }}">
                        <button class="ll-docs-category-header" type="button" data-docs-toggle aria-expanded="{{ $catOpen ? 'true' : 'false' }}">
                            <span class="ll-docs-category-label">
                                <i class="bi bi-folder2"></i>
                                {{ $category['name'] }}
                                <span class="ll-docs-count">{{ count($category['documents']) }}</span>
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <div data-docs-collapse @unless($catOpen) style="display:none" @endunless>
                            @foreach($category['documents'] as $document)
                                <a
                                    class="ll-docs-link {{ $selectedPath === $document['path'] ? 'is-current' : '' }}"
                                    href="{{ url('/studio/docs/' . $document['path']) }}"
                                    hx-get="{{ url('/studio/docs/article/' . $document['path']) }}"
                                    hx-target="#ll-doc-article"
                                    hx-swap="innerHTML"
                                    hx-push-url="{{ url('/studio/docs/' . $document['path']) }}"
                                    data-doc-path="{{ $document['path'] }}"
                                    data-doc-title="{{ $document['title'] }}"
                                    data-doc-summary="{{ $document['summary'] }}"
                                    data-doc-category="{{ $document['category_slug'] }}"
                                    title="{{ $document['summary'] }}"
                                >
                                    <strong><i class="bi bi-{{ $document['type'] === 'pdf' ? 'file-earmark-pdf' : 'file-earmark-text' }} ll-docs-link-icon"></i>{{ $document['title'] }}</strong>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </aside>

        <section class="ll-docs-card ll-docs-pane">
            <div class="ll-docs-pane-header">
                <h2>Current article</h2>
            </div>

            <nav class="ll-docs-toc" id="ll-doc-toc" hidden aria-label="On this page">
                <span class="ll-docs-toc-title"><i class="bi bi-list-nested"></i> On this page</span>
                <div class="ll-docs-toc-links" id="ll-doc-toc-links"></div>
            </nav>

            <div id="ll-doc-article">
                @if($selectedDocument)
                    @include('studio.docs.article', ['document' => $selectedDocument])
                @else
                    <div class="ll-doc-article-shell">
                        <div class="ll-doc-article-head">
                            <h1>No documentation found</h1>
                            <p>Create a category folder under <code>docs/</code> and add your first Markdown or PDF file to populate this library.</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

<script type="application/json" id="ll-docs-index-json">{!! $docsIndex->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script>
    (function () {
        const root = document.getElementById('ll-docs-root');

        if (!root || root.dataset.docsReady === 'true') {
            return;
        }

        root.dataset.docsReady = 'true';

        const searchInput = root.querySelector('#ll-docs-search-input');
        const resultsBox = root.querySelector('#ll-docs-search-results');
        const nav = root.querySelector('#ll-docs-nav');
        const categoryBar = root.querySelector('#ll-docs-category-bar');
        const indexNode = document.getElementById('ll-docs-index-json');
        const docsIndex = indexNode ? JSON.parse(indexNode.textContent) : [];
        let activeCategory = 'all';

        function setCurrentDoc(path) {
            root.dataset.selectedDoc = path || '';

            root.querySelectorAll('.ll-docs-link').forEach(link => {
                link.classList.toggle('is-current', link.dataset.docPath === path);
            });
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function setCategoryExpanded(categorySection, expanded) {
            const toggle = categorySection.querySelector('[data-docs-toggle]');
            const collapse = categorySection.querySelector('[data-docs-collapse]');

            if (!toggle || !collapse) {
                return;
            }

            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            collapse.style.display = expanded ? '' : 'none';
        }

        function applyFilters() {
            const term = (searchInput.value || '').trim().toLowerCase();
            const filtering = term !== '' || activeCategory !== 'all';

            root.querySelectorAll('.ll-docs-category').forEach(categorySection => {
                const categorySlug = categorySection.dataset.category;
                const matchesCategory = activeCategory === 'all' || activeCategory === categorySlug;
                let visibleLinks = 0;

                categorySection.querySelectorAll('.ll-docs-link').forEach(link => {
                    const haystack = [
                        link.dataset.docTitle,
                        link.dataset.docSummary,
                        link.dataset.docCategory,
                        link.dataset.docPath
                    ].join(' ').toLowerCase();

                    const matchesTerm = term === '' || haystack.includes(term);
                    const visible = matchesCategory && matchesTerm;

                    link.classList.toggle('is-hidden', !visible);

                    if (visible) {
                        visibleLinks++;
                    }
                });

                categorySection.classList.toggle('is-hidden', visibleLinks === 0);

                // While filtering, open any category that still has matches so the
                // results are visible. With no filter, restore the default state
                // (only the active doc's category is open) to keep the list compact.
                if (filtering) {
                    setCategoryExpanded(categorySection, visibleLinks > 0);
                } else {
                    setCategoryExpanded(categorySection, categorySection.dataset.defaultOpen === '1');
                }
            });
        }

        function buildToc() {
            const toc = document.getElementById('ll-doc-toc');
            const list = document.getElementById('ll-doc-toc-links');
            const prose = root.querySelector('#ll-doc-article .ll-doc-article-prose');

            if (!toc || !list) {
                return;
            }

            list.innerHTML = '';

            const headings = prose ? prose.querySelectorAll('h2, h3') : [];

            if (!headings.length) {
                toc.hidden = true;
                return;
            }

            const used = {};

            headings.forEach(function (heading) {
                let id = heading.id;

                if (!id) {
                    id = (heading.textContent || '')
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '') || 'section';

                    if (used[id]) {
                        id = id + '-' + (++used[id]);
                    } else {
                        used[id] = 1;
                    }

                    heading.id = id;
                }

                const anchor = document.createElement('a');
                anchor.href = '#' + id;
                anchor.textContent = heading.textContent;

                if (heading.tagName.toLowerCase() === 'h3') {
                    anchor.classList.add('is-sub');
                }

                anchor.addEventListener('click', function (event) {
                    event.preventDefault();
                    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });

                list.appendChild(anchor);
            });

            toc.hidden = false;
        }

        function closeResults() {
            resultsBox.classList.remove('is-open');
            resultsBox.innerHTML = '';
        }

        function openResults(matches) {
            if (!matches.length) {
                resultsBox.innerHTML = '<div class="ll-docs-search-empty">No matching docs yet. Add another Markdown or PDF file under <code>docs/</code> to grow the library.</div>';
                resultsBox.classList.add('is-open');
                return;
            }

            resultsBox.innerHTML = matches.map(function (doc) {
                const icon = doc.type === 'pdf' ? 'file-earmark-pdf' : 'file-earmark-text';

                return '' +
                    '<a class="ll-docs-search-result" ' +
                    'href="' + escapeHtml(doc.url) + '" ' +
                    'hx-get="' + escapeHtml(doc.article_url) + '" ' +
                    'hx-target="#ll-doc-article" ' +
                    'hx-swap="innerHTML" ' +
                    'hx-push-url="' + escapeHtml(doc.url) + '" ' +
                    'data-doc-path="' + escapeHtml(doc.path) + '">' +
                        '<strong><i class="bi bi-' + icon + ' ll-docs-link-icon"></i>' + escapeHtml(doc.title) + '</strong>' +
                        '<span>' + escapeHtml(doc.category + ' • ' + doc.summary) + '</span>' +
                    '</a>';
            }).join('');

            if (window.htmx) {
                window.htmx.process(resultsBox);
            }

            resultsBox.classList.add('is-open');
        }

        function updateSuggestions() {
            const term = (searchInput.value || '').trim().toLowerCase();

            if (term.length < 2) {
                closeResults();
                return;
            }

            const matches = docsIndex.filter(function (doc) {
                const categoryMatch = activeCategory === 'all' || doc.category_slug === activeCategory;
                const haystack = [doc.title, doc.summary, doc.category, doc.path].join(' ').toLowerCase();

                return categoryMatch && haystack.includes(term);
            }).slice(0, 8);

            openResults(matches);
        }

        searchInput.addEventListener('input', function () {
            applyFilters();
            updateSuggestions();
        });

        categoryBar.addEventListener('click', function (event) {
            const button = event.target.closest('[data-category-filter]');

            if (!button) {
                return;
            }

            activeCategory = button.dataset.categoryFilter;

            categoryBar.querySelectorAll('[data-category-filter]').forEach(chip => {
                chip.classList.toggle('is-active', chip === button);
            });

            applyFilters();
            updateSuggestions();
        });

        nav.addEventListener('click', function (event) {
            const toggle = event.target.closest('[data-docs-toggle]');

            if (toggle) {
                const container = toggle.parentElement.querySelector('[data-docs-collapse]');
                const expanded = toggle.getAttribute('aria-expanded') === 'true';

                toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                container.style.display = expanded ? 'none' : '';
                return;
            }

            const link = event.target.closest('.ll-docs-link');

            if (link) {
                setCurrentDoc(link.dataset.docPath);
            }
        });

        resultsBox.addEventListener('click', function (event) {
            const link = event.target.closest('[data-doc-path]');

            if (!link) {
                return;
            }

            setCurrentDoc(link.dataset.docPath);
            closeResults();
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                closeResults();
            }
        });

        document.body.addEventListener('htmx:afterSwap', function (event) {
            if (event.detail.target && event.detail.target.id === 'll-doc-article') {
                const pushedPath = new URL(window.location.href).pathname.replace(/^\/studio\/docs\/?/, '').replace(/\/$/, '');

                if (pushedPath) {
                    setCurrentDoc(pushedPath);
                }

                buildToc();
            }
        });

        applyFilters();
        setCurrentDoc(root.dataset.selectedDoc || '');
        buildToc();
    })();
</script>

@endsection
