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
                Post Release Feature
            </span>

            <h1 class="fw-bold mb-2">
                Livelatch Creator Program 
            </h1>

            <p class="mb-4 opacity-75 fs-5">
                Create themes, addons and integrations and sell the on the livelatch marketplace.
            </p>

            </div>

        </div>
    </div>

    {{-- Main Content --}}
    <div class="card">
        <div class="card-body">

            <h3 class="mb-3">
                What is the Creator Program?
            </h3>

            <p>
                Are you an artist or programmer? Your skills can enhance user experience within Livelatch. Post launch, the livelatch marketplace will open up, giving you the chance to generate income on this platform.
            </p>

            <p>
                Some examples of things you could create:
            </p>

            <ul>
                <li>Dynamic themes for online creators</li>
                <li>Latches like latchdeck</li>
                <li>integrations with social platforms</li>
            </ul>

        </div>
    </div>

</div>

@endsection