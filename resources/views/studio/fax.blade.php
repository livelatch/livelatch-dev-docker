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
                Soon&trade;
            </span>

            <h1 class="fw-bold mb-2">
                Fax
            </h1>

            <p class="mb-4 opacity-75 fs-5">
                Let your community send print jobs straight to you while you're live.
            </p>

        </div>
    </div>

    {{-- Main Content --}}
    <div class="card">
        <div class="card-body">

            <h3 class="mb-3">
                What is Fax?
            </h3>

            <p>
                Fax is a LatchApp that turns your Livelatch profile into a printer your audience can reach. While you're
                streaming, viewers will be able to send print jobs &mdash; messages, fan art, memes, song requests &mdash;
                that land on a real printer next to you in real time.
            </p>

            <p>
                It's a playful, physical way to bring your community into the moment: instead of another comment
                scrolling past, they get to put something tangible in your hands while you're on air.
            </p>

            <div class="alert alert-secondary mb-0 mt-4" role="alert">
                <i class="bi bi-hourglass-split me-1"></i>
                Fax is still in the workshop. We're building it now &mdash; check back soon.
            </div>

        </div>
    </div>

</div>

@endsection
