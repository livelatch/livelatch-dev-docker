@extends('layouts.sidebar')

@section('content')
<style data-ll-my-data-style>
    .ll-data-page {
        display: grid;
        gap: 18px;
    }

    .ll-data-header,
    .ll-data-card,
    .ll-data-request {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
    }

    .ll-data-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: clamp(18px, 3vw, 28px);
    }

    .ll-data-kicker {
        width: fit-content;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 11px;
        border-radius: 999px;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        font-size: 0.78rem;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .ll-data-header h2,
    .ll-data-header p {
        margin: 0;
    }

    .ll-data-header p {
        color: var(--ll-muted);
        max-width: 720px;
    }

    .ll-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        align-items: start;
    }

    .ll-data-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px;
        border-bottom: 1px solid var(--ll-border);
    }

    .ll-data-card-header h3 {
        margin: 0;
        font-size: 1.1rem;
    }

    .ll-data-card-header span {
        color: var(--ll-muted);
        font-size: 0.82rem;
        font-weight: 700;
    }

    .ll-data-prose {
        padding: 18px;
        color: var(--ll-text);
    }

    .ll-data-prose h1 {
        font-size: 1.55rem;
        margin: 0 0 12px;
    }

    .ll-data-prose h2 {
        font-size: 1.08rem;
        margin: 22px 0 8px;
    }

    .ll-data-prose p,
    .ll-data-prose li {
        color: var(--ll-muted);
        line-height: 1.65;
    }

    .ll-data-prose ul {
        padding-left: 1.25rem;
    }

    .ll-data-request {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 20px;
        flex-wrap: wrap;
    }

    .ll-data-request h3,
    .ll-data-request p {
        margin: 0;
    }

    .ll-data-request p {
        color: var(--ll-muted);
        margin-top: 4px;
    }

    @media (max-width: 991.98px) {
        .ll-data-header,
        .ll-data-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="ll-data-page">
        <section class="ll-data-header">
            <div>
                <span class="ll-data-kicker">
                    <i class="bi bi-shield-lock-fill"></i>
                    Compliance
                </span>
                <h2>Manage My Data</h2>
                <p>Review how Livelatch collects, uses, and manages account data.</p>
            </div>
            <a href="{{ url('/studio/latchid') }}" class="btn btn-primary">
                <i class="bi bi-person-vcard-fill"></i>
                Manage LatchID
            </a>
        </section>

        <div class="ll-data-grid">
            <article class="ll-data-card">
                <div class="ll-data-card-header">
                    <h3>Privacy</h3>
                    <span>{{ $privacyDocument['updated_at'] ? $privacyDocument['updated_at']->format('d M Y') : 'Draft' }}</span>
                </div>
                <div class="ll-data-prose">
                    {!! $privacyDocument['html'] !!}
                </div>
            </article>

            <article class="ll-data-card">
                <div class="ll-data-card-header">
                    <h3>Terms</h3>
                    <span>{{ $tosDocument['updated_at'] ? $tosDocument['updated_at']->format('d M Y') : 'Draft' }}</span>
                </div>
                <div class="ll-data-prose">
                    {!! $tosDocument['html'] !!}
                </div>
            </article>
        </div>

        <section class="ll-data-request">
            <div>
                <h3>Request a copy of all held data</h3>
                <p>Request an export of the account data Livelatch holds about you.</p>
            </div>
            <button type="button" class="btn btn-light" disabled>
                <i class="bi bi-envelope-paper"></i>
                Request data copy
            </button>
        </section>

        <section class="ll-data-request">
            <div>
                <h3>Request account deletion</h3>
                <p>Ask Livelatch to review your account for deletion and offboarding.</p>
            </div>
            <button type="button" class="btn btn-light" disabled>
                <i class="bi bi-person-x"></i>
                Request account deletion
            </button>
        </section>

        <section class="ll-data-request">
            <div>
                <h3>View source</h3>
                <p>Review the public source code and license information for Livelatch.</p>
            </div>
            <button type="button" class="btn btn-light" disabled>
                <i class="bi bi-github"></i>
                View source
            </button>
        </section>
    </div>
</div>
@endsection
