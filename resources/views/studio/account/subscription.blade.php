@extends('layouts.sidebar')

@section('content')

@php
    $user = Auth::user();
    $billing = $user->billing ?? null;

    $planKey = $billing->plan_key ?? 'free';
    $stripeStatus = $billing->stripe_status ?? 'active';

    $isPro = $planKey === 'pro';
    $memberSince = $user->created_at ? $user->created_at->diffForHumans(null, true) : 'a little while';
@endphp

<div class="container-fluid">

    {{-- Hero --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div
            style="
                background:
                radial-gradient(circle at top left, rgba(255,255,255,.18), transparent 35%),
                linear-gradient(135deg,#6236ff 0%,#8b5cf6 50%,#3b82f6 100%);
                min-height:220px;
                position:relative;
            "
            class="card-body d-flex flex-column justify-content-center text-white">

            <span class="badge bg-light text-dark mb-3" style="width:max-content;">
                Billing Centre
            </span>

            <h1 class="fw-bold mb-2">
                Subscription & Billing
            </h1>

            <p class="mb-4 opacity-75 fs-5">
                Manage your Livelatch plan, invoices, billing settings, and future creator tools.
            </p>

            <div class="d-flex gap-2 flex-wrap">
                @if(!$isPro)
                    <a href="{{ route('billing.checkout.pro') }}" class="btn btn-light">
                        Upgrade to Pro
                    </a>
                @endif

                <a href="{{ route('billing.portal') }}" class="btn btn-outline-light">
                    Billing settings
                </a>
            </div>

        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Current Plan</small>
                    <h2 class="mb-1 text-capitalize">{{ $planKey }}</h2>
                    <span class="badge {{ $stripeStatus === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ucfirst($stripeStatus) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Member For</small>
                    <h2 class="mb-1">{{ $memberSince }}</h2>
                    <p class="text-muted mb-0">
                        Joined {{ optional($user->created_at)->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Billing ID</small>
                    <h2 class="mb-1" style="font-size:1.1rem;">
                        {{ $billing->stripe_customer_id ?? 'Not created yet' }}
                    </h2>
                    <p class="text-muted mb-0">
                        Stripe customer reference
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        {{-- Current Subscription --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Current Subscription</h3>
                            <p class="text-muted mb-0">
                                Your active Livelatch plan and access level.
                            </p>
                        </div>

                        <span class="badge {{ $isPro ? 'bg-primary' : 'bg-secondary' }}">
                            {{ $isPro ? 'Pro' : 'Free' }}
                        </span>
                    </div>

                    <div class="p-3 rounded border bg-light">
                        <div class="d-flex justify-content-between flex-wrap gap-3">
                            <div>
                                <strong class="d-block">Plan</strong>
                                <span class="text-capitalize">{{ $planKey }}</span>
                            </div>

                            <div>
                                <strong class="d-block">Status</strong>
                                <span>{{ ucfirst($stripeStatus) }}</span>
                            </div>

                            <div>
                                <strong class="d-block">Renews / Ends</strong>
                                <span>
                                    {{ $billing?->current_period_end ? $billing->current_period_end->format('d M Y') : 'Not applicable' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Invoices --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h3 class="mb-3">Invoices</h3>

                    @if(!empty($invoices ?? []))
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice['date'] ?? '-' }}</td>
                                            <td>{{ $invoice['number'] ?? 'Invoice' }}</td>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ ucfirst($invoice['status'] ?? 'paid') }}
                                                </span>
                                            </td>
                                            <td>{{ $invoice['total'] ?? '$0.00' }}</td>
                                            <td class="text-end">
                                                @if(!empty($invoice['url']))
                                                    <a href="{{ $invoice['url'] }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        View
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-4 border rounded bg-light">
                            <p class="mb-1 fw-semibold">No invoices yet</p>
                            <p class="text-muted mb-0">
                                Your invoices will appear here once paid billing begins.
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Upgrade / Settings --}}
        <div class="col-lg-5">

            @if(!$isPro)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <span class="badge bg-primary mb-3">
                            Recommended
                        </span>

                        <h3 class="mb-2">Upgrade to Pro</h3>

                        <p class="text-muted">
                            Unlock more Livelatch power for your creator page, LatchDeck tools, and future community features.
                        </p>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">✓ Unlimited latch links</li>
                            <li class="mb-2">✓ Advanced creator tools</li>
                            <li class="mb-2">✓ Priority access to LatchDeck features</li>
                            <li class="mb-2">✓ Early Latchalytics previews</li>
                        </ul>

                        <a href="{{ route('billing.checkout.pro') }}" class="btn btn-primary w-100">
                            Continue to Checkout
                        </a>

                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h3 class="mb-3">Billing Settings</h3>

                    <div class="d-grid gap-2">
                        <a href="{{ route('billing.portal') }}" class="btn btn-outline-primary">
                            Manage payment method
                        </a>

                        <a href="{{ route('billing.portal') }}" class="btn btn-outline-primary">
                            Update billing details
                        </a>

                        <a href="{{ route('billing.portal') }}" class="btn btn-outline-primary">
                            View Stripe billing portal
                        </a>
                    </div>

                    <hr>

                    <small class="text-muted">
                        Billing is securely handled by Stripe. Livelatch stores your plan status, not your card details.
                    </small>

                </div>
            </div>

        </div>

    </div>

</div>

@endsection