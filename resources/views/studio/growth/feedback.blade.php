@extends('layouts.sidebar')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h3 class="mb-1">Provide Feedback</h3>
        <p class="text-muted mb-0">
            Help shape the future of Livelatch.
        </p>
    </div>

    <div class="card-body p-0">
        <iframe
            data-tally-src="https://tally.so/r/RGjZMP?formEventsForwarding=1"
            loading="lazy"
            width="100%"
            height="1200"
            frameborder="0"
            title="Livelatch Feedback">
        </iframe>
    </div>
</div>

<script async src="https://tally.so/widgets/embed.js"></script>

@endsection