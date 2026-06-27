<?php

namespace App\Http\Controllers;

use App\Services\CreatorRequestService;
use Illuminate\Http\Request;

class CreatorRequestController extends Controller
{
    /**
     * Public endpoint — a visitor asking a non-existent creator to join.
     * Fails soft: we always thank the visitor so the UX never looks broken.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'handle'      => ['required', 'string', 'max:80'],
            'email'       => ['nullable', 'email', 'max:190'],
            'platforms'   => ['nullable', 'array', 'max:20'],
            'platforms.*' => ['string', 'max:32'],
        ]);

        CreatorRequestService::record($data['handle'], $request, $data['email'] ?? null, $data['platforms'] ?? []);

        return response()->json([
            'ok'      => true,
            'message' => "Thanks — we're tracking the interest.",
        ]);
    }

    /**
     * Admin — demand leaderboard of requested handles (admin middleware).
     */
    public function index()
    {
        return view('studio.admin.creator-requests', [
            'totals' => CreatorRequestService::totals(),
        ]);
    }
}
