@extends('layouts.sidebar')

@section('content')

<div class="container-fluid">

    {{-- Hero --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div
            style="
                background:
                radial-gradient(circle at top left, rgba(255,255,255,.15), transparent 35%),
                linear-gradient(135deg,#6236ff 0%,#8b5cf6 50%,#3b82f6 100%);
                min-height:220px;
                position:relative;
            "
            class="card-body d-flex flex-column justify-content-center text-white">

            <span class="badge bg-light text-dark mb-3" style="width:max-content;">
                Studio Preview
            </span>

            <h1 class="fw-bold mb-2">
                Hello World 👋
            </h1>

            <p class="mb-4 opacity-75 fs-5">
                This is a placeholder Studio page for testing HTMX navigation,
                dark mode, layouts, and future Livelatch features.
            </p>

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light">
                    Primary Action
                </button>

                <button class="btn btn-outline-light">
                    Secondary Action
                </button>
            </div>

        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted">Test Metric</small>
                    <h2 class="mb-0">123</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted">Another Metric</small>
                    <h2 class="mb-0">456</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted">Something Else</small>
                    <h2 class="mb-0">789</h2>
                </div>
            </div>
        </div>

    </div>

    {{-- Main Content --}}
    <div class="card">
        <div class="card-body">

            <h3 class="mb-3">
                Content Area
            </h3>

            <p>
                If you're seeing this page load without the sidebar refreshing,
                HTMX navigation is working correctly.
            </p>

            <p>
                This section can later become:
            </p>

            <ul>
                <li>LatchDeck Dashboard</li>
                <li>Subscription Management</li>
                <li>Affiliate Analytics</li>
                <li>Creator Program Applications</li>
                <li>Notification Centre</li>
            </ul>

        </div>
    </div>

</div>

@endsection