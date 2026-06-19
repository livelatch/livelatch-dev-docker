<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Services\LatchDeckService;
use Illuminate\Http\Request;
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

    public function storeCard(Request $request, LatchDeckService $latchdeck)
    {
        $user = $request->user();
        $latchIdUserId = $user->supabase_user_id;

        if (!$latchIdUserId) {
            return back()->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        $validated = $request->validate([
            'name_mvp' => ['required', 'string', 'max:120'],
            'short_description_mvp' => ['required', 'string', 'max:255'],
            'long_description_mvp' => ['nullable', 'string', 'max:2000'],
            'rarity_mvp' => ['required', 'string', 'max:40'],
            'background_color_mvp' => ['nullable', 'string', 'max:9'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $card = [
            'name_mvp' => $validated['name_mvp'],
            'short_description_mvp' => $validated['short_description_mvp'],
            'long_description_mvp' => $validated['long_description_mvp'] ?? null,
            'rarity_mvp' => $validated['rarity_mvp'],
            'creator_name_mvp' => (string) ($user->name ?: $user->littlelink_name ?: 'Creator'),
            'background_color_mvp' => $validated['background_color_mvp'] ?? '#1b1b29',
        ];

        if ($request->hasFile('image')) {
            $card['image_url_mvp'] = $this->uploadCardImage($request, $latchIdUserId);
        }

        $created = $latchdeck->createCard($latchIdUserId, $card);

        if ($created === null) {
            return back()->with('latchdeck_error', 'Could not save your card.');
        }

        return back()->with('latchdeck_success', 'Draft card saved.');
    }

    public function updateCard(Request $request, LatchDeckService $latchdeck, string $id)
    {
        $user = $request->user();
        $latchIdUserId = $user->supabase_user_id;

        if (!$latchIdUserId) {
            return back()->with('latchdeck_error', 'Your account is not linked to LatchID yet.');
        }

        $validated = $request->validate([
            'name_mvp' => ['sometimes', 'string', 'max:120'],
            'short_description_mvp' => ['sometimes', 'string', 'max:255'],
            'long_description_mvp' => ['nullable', 'string', 'max:2000'],
            'rarity_mvp' => ['sometimes', 'string', 'max:40'],
            'background_color_mvp' => ['nullable', 'string', 'max:9'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $fields = collect($validated)->except('image')->filter(fn ($v) => $v !== null)->all();

        if ($request->hasFile('image')) {
            $fields['image_url_mvp'] = $this->uploadCardImage($request, $latchIdUserId);
        }

        $updated = $latchdeck->updateCard($latchIdUserId, $id, $fields);

        if ($updated === null) {
            return back()->with('latchdeck_error', 'Could not update your card.');
        }

        return back()->with('latchdeck_success', 'Card updated.');
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
