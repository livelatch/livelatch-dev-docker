@php
    $isPrivacy = ($type ?? 'privacy') === 'privacy';
@endphp

<article class="ll-legal-card">
    <p class="ll-kicker">{{ $isPrivacy ? 'Privacy' : 'Terms' }}</p>
    <h2>{{ $isPrivacy ? 'Privacy Notice' : 'Terms of Service' }}</h2>

    @if($isPrivacy)
        <p>Livelatch uses account, profile, link, analytics, billing, and social connection data to operate creator profiles and connected tools.</p>
        <p>LatchID is used to manage identity across Livelatch experiences. Connected platforms are only used when you choose to link them.</p>
        <p>You can request access to your data, corrections, export, or deletion review from your account area.</p>
    @else
        <p>Livelatch provides creator profile, link, theme, analytics, and audience tools. You are responsible for the content and links you publish.</p>
        <p>Do not use Livelatch for spam, phishing, impersonation, unlawful content, or attempts to bypass platform security.</p>
        <p>Livelatch is based on LinkStack and respects the AGPL-3.0 obligations that apply to the software platform.</p>
    @endif

    <a class="ll-button ll-button-ghost" href="{{ $isPrivacy ? url('/legal/privacy') : url('/legal/terms') }}">
        Open full page
    </a>
</article>
