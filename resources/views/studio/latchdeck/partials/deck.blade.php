@php
    $maxPublished = $capabilities['maxPublishedCards'] ?? null;
    $publishedCount = collect($cards)->where('status_mvp', 'published')->count();
    $tier = $tier ?? 'free';
@endphp

@if($state === 'pending_review')
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2">
        <i class="bi bi-hourglass-split fs-5"></i>
        <div>
            <strong>Your access request is pending review.</strong>
            You can design and save draft cards now — publishing unlocks once you're approved.
        </div>
    </div>
@endif

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">
            <div>
                <span class="badge bg-primary mb-2">LatchDeck</span>
                <h1 class="fw-bold mb-0">Card studio</h1>
            </div>
            <div class="text-end">
                <span class="badge {{ $tier === 'pro' ? 'bg-primary' : 'bg-light text-dark border' }}">{{ ucfirst($tier) }} plan</span>
                @if($maxPublished !== null)
                    <div class="small text-muted mt-1">{{ $publishedCount }} / {{ $maxPublished }} published</div>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h2 class="h5 fw-bold mb-0">Your cards</h2>
    <a href="{{ route('studio.latchdeck.editor') }}" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-plus-lg me-1"></i> Create card
    </a>
</div>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        @if(empty($cards))
            <div class="border rounded-3 p-5 text-center text-muted">
                <i class="bi bi-collection-fill fs-1 d-block mb-2 opacity-50"></i>
                No cards yet. <a href="{{ route('studio.latchdeck.editor') }}">Design your first card</a> in the editor.
            </div>
        @else
            <div class="row g-3">
                @foreach($cards as $card)
                    @php
                        $isPublished = ($card['status_mvp'] ?? 'draft') === 'published';
                        $cardEffect = $card['effect_mvp'] ?? 'none';
                    @endphp
                    <div class="col-sm-6 col-xl-4">
                        <div class="border rounded-3 overflow-hidden h-100 d-flex flex-column">
                            <div style="height: 110px; background: {{ $card['background_color_mvp'] ?? '#1b1b29' }};">
                                @if(!empty($card['image_url_mvp']))
                                    <img src="{{ $card['image_url_mvp'] }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                @endif
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <strong>{{ $card['name_mvp'] ?? 'Untitled' }}</strong>
                                    <span class="badge {{ $isPublished ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $isPublished ? 'Published' : 'Draft' }}
                                    </span>
                                </div>
                                <div class="small text-muted text-capitalize">
                                    {{ $card['rarity_mvp'] ?? '' }}@if($cardEffect && $cardEffect !== 'none') · {{ ucfirst($cardEffect) }}@endif
                                </div>
                                <p class="small text-muted mt-1 mb-3">{{ $card['short_description_mvp'] ?? '' }}</p>

                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ route('studio.latchdeck.editor', $card['id']) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @if($isPublished)
                                        <form method="POST" action="{{ route('studio.latchdeck.cards.unpublish', $card['id']) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill">Unpublish</button>
                                        </form>
                                    @elseif($canPublish)
                                        <form method="POST" action="{{ route('studio.latchdeck.cards.publish', $card['id']) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-primary rounded-pill">Publish</button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-primary rounded-pill" disabled
                                                title="Publishing unlocks once you're approved">
                                            Publish
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
