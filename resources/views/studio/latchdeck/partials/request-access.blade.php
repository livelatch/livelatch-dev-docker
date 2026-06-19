<section class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 p-lg-5">
        <span class="badge bg-primary mb-3">LatchDeck</span>
        <h1 class="fw-bold mb-2">Collectible cards for your community</h1>
        <p class="text-muted fs-5 mb-0" style="max-width: 60ch;">
            LatchDeck lets you turn moments into collectible creator cards your audience can claim and keep.
            Design cards in the studio, publish them once you're approved, and (soon) run claim campaigns and
            redemptions. LatchDeck is platform-agnostic — the same cards can live across the apps and games
            that build on the LatchDeck API.
        </p>
    </div>
</section>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5">
        <h2 class="fw-bold mb-4">What you get</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded-3 p-4 h-100">
                    <h5 class="fw-bold">Free</h5>
                    <p class="text-muted small mb-3">For getting started.</p>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Design and save draft cards</li>
                        <li>Publish a starter set of cards</li>
                        <li>Standard rarities</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-4 h-100" style="border-color:#7c3aed !important;">
                    <h5 class="fw-bold">Pro <span class="badge bg-primary align-middle">Upgrade</span></h5>
                    <p class="text-muted small mb-3">For growing creators.</p>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Everything in Free</li>
                        <li>Far higher published-card limits</li>
                        <li>Premium rarities &amp; styling</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-4 h-100">
                    <h5 class="fw-bold">SDK <span class="badge bg-secondary align-middle">Developers</span></h5>
                    <p class="text-muted small mb-3">For apps &amp; games (coming soon).</p>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Create cards via the LatchDeck API</li>
                        <li>Your own LatchDeck API key</li>
                        <li>Bake collectibles into your product</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4 p-lg-5">
        <h2 class="fw-bold mb-2">Request access</h2>
        <p class="text-muted mb-4">Tell us a little about where you create. We'll review and get you set up.</p>

        <form method="POST" action="{{ route('studio.latchdeck.requestAccess') }}" style="max-width: 640px;">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Primary platform</label>
                <input type="text" name="platform" class="form-control" maxlength="120" required
                       placeholder="e.g. Twitch, YouTube, TikTok, Discord">
                @error('platform')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Tell us about your community <span class="text-muted fw-normal">(optional)</span></label>
                <textarea name="community_context" class="form-control" rows="2" maxlength="1000"
                          placeholder="Size, vibe, what your audience loves"></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Why LatchDeck? <span class="text-muted fw-normal">(optional)</span></label>
                <textarea name="reason" class="form-control" rows="2" maxlength="1000"
                          placeholder="What you'd like to make"></textarea>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-send-fill me-1"></i> Request access
            </button>
        </form>
    </div>
</section>
