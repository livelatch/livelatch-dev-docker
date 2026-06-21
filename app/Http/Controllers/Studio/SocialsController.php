<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Services\LivelatchSocialService;

class SocialsController extends Controller
{
    public function index()
    {
        $socials = LivelatchSocialService::activeForDisplay();

        // Attach live Discord widget data (online count, members, invite) to the
        // discord card so it renders natively rather than as a third-party iframe.
        foreach ($socials as &$social) {
            if (($social['widget'] ?? null) === 'discord' && !empty($social['widget_id'])) {
                $social['discord'] = LivelatchSocialService::discordWidget($social['widget_id']);
            }
        }
        unset($social);

        return view('studio.community.socials', [
            'socials' => $socials,
        ]);
    }
}
