@extends('layouts.sidebar')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <section class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <span class="badge bg-primary mb-3">Community</span>
            <h1 class="fw-bold mb-2">Affiliate Program</h1>
            <p class="text-muted fs-5 mb-4">
                Invite creators to Livelatch and track community rewards from one place.
            </p>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Referrals</small>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Rewards</small>
                        <h2 class="mb-0">$0</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Status</small>
                        <h2 class="mb-0">Preparing</h2>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border mt-4 mb-0">
                Affiliate tools are being prepared for creator alpha testing.
            </div>
        </div>
    </section>
</div>
@endsection
