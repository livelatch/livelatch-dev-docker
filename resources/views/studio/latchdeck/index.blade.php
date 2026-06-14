@extends('layouts.sidebar')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <section class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <span class="badge bg-primary mb-3">LatchDeck</span>
            <h1 class="fw-bold mb-2">Creator Cards</h1>
            <p class="text-muted fs-5 mb-4">
                Build collectible cards and reward moments for your audience.
            </p>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Published cards</small>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Claims</small>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Collections</small>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border mt-4 mb-0">
                LatchDeck tools are being shaped for the alpha experience.
            </div>
        </div>
    </section>
</div>
@endsection
