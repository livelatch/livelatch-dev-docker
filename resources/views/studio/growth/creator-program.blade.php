@extends('layouts.sidebar')

@section('content')

<div class="container-fluid">

    {{-- Hero --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div
            style="
                background:
                radial-gradient(circle at top left, rgba(255,255,255,.15), transparent 35%),
                linear-gradient(135deg, var(--ll-primary, #0092ec) 0%, var(--ll-primary-2, #0ce5de) 100%);
                min-height:220px;
                position:relative;
            "
            class="card-body d-flex flex-column justify-content-center text-white">

            <span class="badge bg-light text-dark mb-3" style="width:max-content;">
                Post Release Feature
            </span>

            <h1 class="fw-bold mb-2">
                Livelatch Creator Program
            </h1>

            <p class="mb-4 opacity-75 fs-5">
                Create themes, add-ons and integrations and sell them on the Livelatch marketplace.
            </p>

        </div>
    </div>

    {{-- Main Content --}}
    <div class="card">
        <div class="card-body">

            <h3 class="mb-3">
                What is the Creator Program?
            </h3>

            <p>
                Are you an artist or programmer? Your skills can enhance the experience for everyone on Livelatch. After launch, the Livelatch marketplace will open up, giving you the chance to earn income on the platform.
            </p>

            <p>
                Some examples of things you could create:
            </p>

            <ul>
                <li>Dynamic themes for online creators</li>
                <li>LatchApps like LatchDeck</li>
                <li>Integrations with social platforms</li>
            </ul>

        </div>
    </div>

</div>

@endsection