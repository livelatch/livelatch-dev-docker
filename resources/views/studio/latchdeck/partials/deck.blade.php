@php
    $standardRarities = ['common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare'];
    $premiumRarities = ['epic' => 'Epic', 'legendary' => 'Legendary', 'mythic' => 'Mythic'];
    $premiumAllowed = (bool) ($capabilities['premiumRarities'] ?? false);
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

<div class="row g-4">
    {{-- Card editor (create draft) --}}
    <div class="col-lg-5">
        <section class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">New card</h2>
                <form method="POST" action="{{ route('studio.latchdeck.cards.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name_mvp" class="form-control" maxlength="120" required>
                        @error('name_mvp')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Short description</label>
                        <input type="text" name="short_description_mvp" class="form-control" maxlength="255" required>
                        @error('short_description_mvp')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Long description <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="long_description_mvp" class="form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold">Rarity</label>
                            <select name="rarity_mvp" class="form-select" required>
                                @foreach($standardRarities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                                @foreach($premiumRarities as $value => $label)
                                    <option value="{{ $value }}" {{ $premiumAllowed ? '' : 'disabled' }}>
                                        {{ $label }}{{ $premiumAllowed ? '' : ' — Pro' }}
                                    </option>
                                @endforeach
                            </select>
                            @unless($premiumAllowed)
                                <div class="form-text">Premium rarities are a Pro feature.</div>
                            @endunless
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold">Background</label>
                            <input type="color" name="background_color_mvp" class="form-control form-control-color w-100" value="#1b1b29">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Card art <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-save me-1"></i> Save draft
                    </button>
                </form>
            </div>
        </section>
    </div>

    {{-- Card list --}}
    <div class="col-lg-7">
        <section class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Your cards</h2>

                @if(empty($cards))
                    <div class="border rounded-3 p-4 text-center text-muted">
                        No cards yet. Design your first card on the left.
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($cards as $card)
                            @php $isPublished = ($card['status_mvp'] ?? 'draft') === 'published'; @endphp
                            <div class="col-sm-6">
                                <div class="border rounded-3 overflow-hidden h-100 d-flex flex-column">
                                    <div style="height: 96px; background: {{ $card['background_color_mvp'] ?? '#1b1b29' }};">
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
                                        <div class="small text-muted text-capitalize">{{ $card['rarity_mvp'] ?? '' }}</div>
                                        <p class="small text-muted mt-1 mb-3">{{ $card['short_description_mvp'] ?? '' }}</p>

                                        <div class="mt-auto">
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
    </div>
</div>
