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
                Profile Features
            </span>

            <h1 class="fw-bold mb-2">
                LatchApps
            </h1>

            <p class="mb-4 opacity-75 fs-5">
                Interactive features that live on your Livelatch profile and turn visitors into a community.
            </p>

        </div>
    </div>

    {{-- Main Content --}}
    <div class="card">
        <div class="card-body">

            <h3 class="mb-3">
                What are LatchApps?
            </h3>

            <p>
                LatchApps are the building blocks that make your Livelatch profile more than just a list of links.
                Each one is a self-contained feature you can switch on and drop straight onto your page &mdash; designed
                to spark community engagement, keep your audience coming back, and give them something to do once they
                land on your profile.
            </p>

            <p>
                Instead of sending followers off to a dozen different platforms, LatchApps bring the interaction home to
                your profile, where you stay in control of the experience.
            </p>

            <h3 class="mb-3 mt-4">
                Available LatchApps
            </h3>

            <ul>
                <li>
                    <strong>LatchDeck</strong>
                    <span class="badge bg-warning text-dark ms-1">In development</span>
                    &mdash; a deck of interactive cards you can publish to your profile for your community to explore.
                </li>
                <li>
                    <strong>Fax</strong>
                    <span class="badge bg-secondary ms-1">Soon&trade;</span>
                    &mdash; a lightweight way for your community to send you messages directly from your profile.
                </li>
            </ul>

            <p class="text-muted mb-0 mt-3">
                More LatchApps are on the way. Each one is built to display on your Livelatch profile and work for
                community engagement.
            </p>

        </div>
    </div>

</div>

@endsection
