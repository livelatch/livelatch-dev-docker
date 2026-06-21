<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LivelatchSocialService;
use Illuminate\Http\Request;

class SocialLinksController extends Controller
{
    public function edit()
    {
        return view('studio.admin.socials', [
            'platforms' => LivelatchSocialService::platforms(),
            'stored' => LivelatchSocialService::all(),
        ]);
    }

    public function update(Request $request)
    {
        $input = $request->input('socials', []);
        $platforms = LivelatchSocialService::platforms();
        $rows = [];
        $order = 0;

        foreach (array_keys($platforms) as $platform) {
            $entry = $input[$platform] ?? [];

            $rows[$platform] = [
                'handle' => $entry['handle'] ?? null,
                'profile_url' => $entry['profile_url'] ?? null,
                'featured_post_url' => $entry['featured_post_url'] ?? null,
                'widget_id' => $entry['widget_id'] ?? null,
                'is_active' => !empty($entry['is_active']),
                'display_order' => $order++,
            ];
        }

        $saved = LivelatchSocialService::saveAll($rows);

        return redirect()
            ->route('admin.socials')
            ->with('status', $saved ? 'socials-saved' : 'socials-error');
    }
}
