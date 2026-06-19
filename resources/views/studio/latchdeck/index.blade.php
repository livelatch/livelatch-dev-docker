@extends('layouts.sidebar')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">

    @if(session('latchdeck_success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('latchdeck_success') }}</div>
    @endif
    @if(session('latchdeck_error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('latchdeck_error') }}</div>
    @endif

    @switch($state)
        @case('not_applied')
            @include('studio.latchdeck.partials.request-access')
            @break

        @case('pending_review')
        @case('active')
            @include('studio.latchdeck.partials.deck')
            @break

        @case('denied_waitlist')
            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <span class="badge bg-secondary mb-3">LatchDeck</span>
                    <h1 class="fw-bold mb-2">You're on the waitlist</h1>
                    <p class="text-muted fs-5 mb-0">
                        Your application wasn't approved this round. We'll let you know if that changes.
                        @if(!empty($access['restriction']['message'])) <br>{{ $access['restriction']['message'] }} @endif
                    </p>
                </div>
            </section>
            @break

        @case('restricted')
        @case('revoked')
            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <span class="badge bg-danger mb-3">LatchDeck</span>
                    <h1 class="fw-bold mb-2">Access {{ $state === 'revoked' ? 'revoked' : 'restricted' }}</h1>
                    <p class="text-muted fs-5 mb-0">
                        {{ $access['restriction']['message'] ?? 'Your LatchDeck access is currently limited. Contact support if you think this is a mistake.' }}
                    </p>
                </div>
            </section>
            @break

        @case('no_latchid')
            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5 text-center">
                    <h1 class="fw-bold mb-2">Link LatchID first</h1>
                    <p class="text-muted fs-5 mb-0">LatchDeck needs your LatchID. Sign in with LatchID to continue.</p>
                </div>
            </section>
            @break

        @default
            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5 text-center">
                    <h1 class="fw-bold mb-2">LatchDeck is unavailable</h1>
                    <p class="text-muted fs-5 mb-0">We couldn't reach LatchDeck just now. Please try again shortly.</p>
                </div>
            </section>
    @endswitch
</div>
@endsection
