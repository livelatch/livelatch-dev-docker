@extends('layouts.sidebar')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <section class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <span class="badge bg-primary mb-3">LatchDeck</span>
            <h1 class="fw-bold mb-2">Redemptions</h1>
            <p class="text-muted fs-5 mb-4">Track card claims, rewards, and audience redemption activity.</p>

            <div class="border rounded p-4 text-center">
                <h3 class="mb-2">No redemptions yet</h3>
                <p class="text-muted mb-0">Redemption activity will appear here when cards are active.</p>
            </div>
        </div>
    </section>
</div>
@endsection
