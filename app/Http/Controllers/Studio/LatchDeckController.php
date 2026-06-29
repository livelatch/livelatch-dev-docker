<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Services\LatchDeckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Livelatch's UI over the LatchDeck (Encore) API. Every screen is driven by the
 * creator's access status; Livelatch holds no LatchDeck state of its own.
 */
class LatchDeckController extends Controller
{
    public function index(Request $request, LatchDeckService $latchdeck)
    {
        $user = $request->user();
        $latchIdUserId = $user->supabase_user_id;

        if (!$latchIdUserId) {
            return view('studio.latchdeck.index', [
                'state' => 'no_latchid',
                'access' => null,
                'cards' => [],
            ]);
        }

        // Keep the creator's tier in sync with their Livelatch plan (best effort).
        $tier = $this->tierForUser($user);

        $access = $latchdeck->accessStatus($latchIdUserId);

        if ($access === null) {
            return view('studio.latchdeck.index', [
                'state' => 'unavailable',
                'access' => null,
                'cards' => [],
            ]);
        }

        $status = $access['status'] ?? 'not_applied';
        $cards = [];

        if (in_array($status, ['pending_review', 'active'], true)) {
            // Self-heal: keep the LatchDeck tier aligned with the Livelatch plan
            // (there is no billing webhook syncing it yet). Re-fetch so Encore
            // remains the source of truth for tier capabilities.
            if (($access['tier'] ?? 'free') !== $tier && $latchdeck->syncTier($latchIdUserId, $tier)) {
                $access = $latchdeck->accessStatus($latchIdUserId) ?? $access;
            }

            $cards = $latchdeck->listCards($latchIdUserId);
        }

        return view('studio.latchdeck.index', [
            'state' => $status,
            'access' => $access,
            'cards' => $cards,
            'tier' => $access['tier'] ?? $tier,
            'capabilities' => $access['capabilities'] ?? [],
            'canPublish' => $status === 'active',
        ]);
    }

    public function requestAccess(Request $request, LatchDeckService $latchdeck)
    {
        $user = $request->user();

        if (!$user->supabase_user_id) {
            return back()->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:120'],
            'community_context' => ['nullable', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $latchdeck->requestAccess($user->supabase_user_id, [
            'email' => (string) $user->email,
            'display_name' => (string) ($user->name ?: $user->littlelink_name ?: $user->email),
            'platform' => $validated['platform'],
            'community_context' => $validated['community_context'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'tier' => $this->tierForUser($user),
        ]);

        if ($result === null) {
            return back()->with('latchdeck_error', 'Could not submit your request. Please try again.');
        }

        return redirect()->route('studio.latchdeck')
            ->with('latchdeck_success', 'Your LatchDeck access request has been submitted.');
    }

    /**
     * The full-page card editor (V1 template). New card when $id is null, edit an
     * existing draft otherwise. Access-gated like the studio hub.
     */
    public function editor(Request $request, LatchDeckService $latchdeck, ?string $id = null)
    {
        $user = $request->user();
        $latchIdUserId = $user->supabase_user_id;

        if (!$latchIdUserId) {
            return redirect()->route('studio.latchdeck')
                ->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        $access = $latchdeck->accessStatus($latchIdUserId);
        $status = $access['status'] ?? 'not_applied';

        if (!in_array($status, ['pending_review', 'active'], true)) {
            return redirect()->route('studio.latchdeck');
        }

        $card = null;
        if ($id !== null) {
            $card = collect($latchdeck->listCards($latchIdUserId))->firstWhere('id', $id);
            if (!$card) {
                return redirect()->route('studio.latchdeck')
                    ->with('latchdeck_error', 'That card could not be found.');
            }
        }

        return view('studio.latchdeck.editor', [
            'card' => $card,
            'tier' => $access['tier'] ?? 'free',
            'capabilities' => $access['capabilities'] ?? [],
            'unsplashEnabled' => $this->unsplashConfigured(),
            'creatorName' => (string) ($user->name ?: $user->littlelink_name ?: 'Creator'),
        ]);
    }

    /** Proxy an Unsplash photo search (keeps the API key server-side). Returns JSON. */
    public function unsplashSearch(Request $request)
    {
        $key = (string) config('services.unsplash.access_key');
        if ($key === '') {
            return response()->json(['results' => [], 'error' => 'unconfigured']);
        }

        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json(['results' => []]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $key,
                'Accept-Version' => 'v1',
            ])->timeout(10)->get('https://api.unsplash.com/search/photos', [
                'query' => $query,
                'per_page' => 24,
                'orientation' => 'portrait',
                'content_filter' => 'high',
            ]);

            if (!$response->successful()) {
                return response()->json(['results' => [], 'error' => 'upstream']);
            }

            $results = collect($response->json('results') ?? [])
                ->map(fn ($photo) => $this->mapUnsplashPhoto($photo))
                ->all();

            return response()->json(['results' => $results]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['results' => [], 'error' => 'error']);
        }
    }

    public function storeCard(Request $request, LatchDeckService $latchdeck)
    {
        $user = $request->user();
        $latchIdUserId = $user->supabase_user_id;

        if (!$latchIdUserId) {
            return back()->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        $validated = $this->validateCard($request, true);
        ['card' => $card, 'download_location' => $download] =
            $this->buildCardPayload($request, $validated, $user, $latchIdUserId);

        [$created, $error] = $latchdeck->createCard($latchIdUserId, $card);

        if ($created === null) {
            return back()->withInput()->with('latchdeck_error', $error ?? 'Could not save your card.');
        }

        if ($download) {
            $this->triggerUnsplashDownload($download);
        }

        return redirect()->route('studio.latchdeck')->with('latchdeck_success', 'Draft card saved.');
    }

    public function updateCard(Request $request, LatchDeckService $latchdeck, string $id)
    {
        $user = $request->user();
        $latchIdUserId = $user->supabase_user_id;

        if (!$latchIdUserId) {
            return back()->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        $validated = $this->validateCard($request, false);
        ['card' => $card, 'download_location' => $download] =
            $this->buildCardPayload($request, $validated, $user, $latchIdUserId);

        // creator_name_mvp is only meaningful on create; drop it from updates.
        unset($card['creator_name_mvp']);

        [$updated, $error] = $latchdeck->updateCard($latchIdUserId, $id, $card);

        if ($updated === null) {
            return back()->withInput()->with('latchdeck_error', $error ?? 'Could not update your card.');
        }

        if ($download) {
            $this->triggerUnsplashDownload($download);
        }

        return redirect()->route('studio.latchdeck')->with('latchdeck_success', 'Card updated.');
    }

    /** Shared validation for the card editor. */
    private function validateCard(Request $request, bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes';

        return $request->validate([
            'name_mvp' => [$req, 'string', 'max:120'],
            'short_description_mvp' => [$req, 'string', 'max:255'],
            'long_description_mvp' => ['nullable', 'string', 'max:2000'],
            'rarity_mvp' => [$req, 'string', 'max:40'],
            'background_color_mvp' => ['nullable', 'string', 'max:9'],
            'text_color_mvp' => ['nullable', 'string', 'max:9'],
            'effect_mvp' => ['nullable', 'in:none,holo,shiny,foil'],
            'redeem_code_mvp' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9._-]+$/'],
            'supply_cap_mvp' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'image_source' => ['nullable', 'in:upload,unsplash'],
            'image' => ['nullable', 'image', 'max:5120'],
            'unsplash_url' => ['nullable', 'url', 'max:1000'],
            'unsplash_photographer' => ['nullable', 'string', 'max:200'],
            'unsplash_photographer_url' => ['nullable', 'url', 'max:1000'],
            'unsplash_photo_url' => ['nullable', 'url', 'max:1000'],
            'unsplash_download_location' => ['nullable', 'url', 'max:1000'],
        ]);
    }

    /**
     * Build the Encore card payload from validated input, resolving the image
     * (S3 upload or Unsplash) and its attribution. Returns the card array plus an
     * optional Unsplash download_location to ping for ToS compliance.
     *
     * @return array{card: array<string,mixed>, download_location: ?string}
     */
    private function buildCardPayload(Request $request, array $validated, $user, string $latchIdUserId): array
    {
        $card = [];
        foreach ([
            'name_mvp', 'short_description_mvp', 'long_description_mvp', 'rarity_mvp',
            'background_color_mvp', 'text_color_mvp', 'effect_mvp', 'redeem_code_mvp', 'supply_cap_mvp',
        ] as $key) {
            if (array_key_exists($key, $validated) && $validated[$key] !== null && $validated[$key] !== '') {
                $card[$key] = $validated[$key];
            }
        }

        $card['creator_name_mvp'] = (string) ($user->name ?: $user->littlelink_name ?: 'Creator');

        // Send a real JSON number so Encore's typed handler + the integer column accept it.
        if (isset($card['supply_cap_mvp'])) {
            $card['supply_cap_mvp'] = (int) $card['supply_cap_mvp'];
        }

        $download = null;
        $source = $validated['image_source'] ?? null;

        if ($source === 'upload' && $request->hasFile('image')) {
            $url = $this->uploadCardImage($request, $latchIdUserId);
            if ($url) {
                $card['image_url_mvp'] = $url;
                $card['image_source_mvp'] = 'upload';
                $card['image_credit_mvp'] = null;
            }
        } elseif ($source === 'unsplash' && !empty($validated['unsplash_url'])) {
            $card['image_url_mvp'] = $validated['unsplash_url'];
            $card['image_source_mvp'] = 'unsplash';
            $card['image_credit_mvp'] = [
                'source' => 'unsplash',
                'photographer' => $validated['unsplash_photographer'] ?? null,
                'photographer_url' => $validated['unsplash_photographer_url'] ?? null,
                'photo_url' => $validated['unsplash_photo_url'] ?? null,
            ];
            $download = $validated['unsplash_download_location'] ?? null;
        }

        return ['card' => $card, 'download_location' => $download];
    }

    /** Map a raw Unsplash photo to the fields the editor + attribution need. */
    private function mapUnsplashPhoto(array $photo): array
    {
        $utm = '?utm_source=' . rawurlencode((string) config('services.unsplash.utm_source', 'livelatch'))
            . '&utm_medium=referral';

        return [
            'id' => $photo['id'] ?? null,
            'thumb' => $photo['urls']['thumb'] ?? null,
            'small' => $photo['urls']['small'] ?? null,
            'regular' => $photo['urls']['regular'] ?? null,
            'alt' => $photo['alt_description'] ?? ($photo['description'] ?? ''),
            'photographer' => $photo['user']['name'] ?? 'Unknown',
            'photographer_url' => ($photo['user']['links']['html'] ?? 'https://unsplash.com') . $utm,
            'photo_url' => ($photo['links']['html'] ?? 'https://unsplash.com') . $utm,
            'download_location' => $photo['links']['download_location'] ?? null,
        ];
    }

    /**
     * Trigger an Unsplash download event. Required by the Unsplash API Guidelines
     * whenever a photo is "used" (selected for the card). Fire-and-forget.
     */
    private function triggerUnsplashDownload(string $downloadLocation): void
    {
        $key = (string) config('services.unsplash.access_key');
        if ($key === '') {
            return;
        }

        try {
            Http::withHeaders(['Authorization' => 'Client-ID ' . $key])
                ->timeout(8)
                ->get($downloadLocation);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function unsplashConfigured(): bool
    {
        return (string) config('services.unsplash.access_key') !== '';
    }

    public function publishCard(Request $request, LatchDeckService $latchdeck, string $id)
    {
        $user = $request->user();

        if (!$user->supabase_user_id) {
            return back()->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        [$card, $error] = $latchdeck->publishCard($user->supabase_user_id, $id);

        if ($card === null) {
            return back()->with('latchdeck_error', $error ?? 'Could not publish this card.');
        }

        return back()->with('latchdeck_success', 'Card published.');
    }

    public function unpublishCard(Request $request, LatchDeckService $latchdeck, string $id)
    {
        $user = $request->user();

        if (!$user->supabase_user_id) {
            return back()->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        $latchdeck->unpublishCard($user->supabase_user_id, $id);

        return back()->with('latchdeck_success', 'Card moved back to draft.');
    }

    /** Upload card art to S3 (public) and return its URL, or null on failure. */
    private function uploadCardImage(Request $request, string $latchIdUserId): ?string
    {
        try {
            $file = $request->file('image');
            $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("latchdeck/cards/{$latchIdUserId}", $name, [
                'disk' => 's3',
                'visibility' => 'public',
            ]);

            return Storage::disk('s3')->url($path);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Map the Livelatch plan to a LatchDeck tier (free/pro). */
    private function tierForUser($user): string
    {
        return $user->planKey();
    }
}
