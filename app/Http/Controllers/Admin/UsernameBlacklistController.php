<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UsernameBlacklistService;
use Illuminate\Http\Request;

/**
 * Admin → Username Blacklist. Banned substrings (profanity, slurs, banned
 * persons, system words). A signup whose generated handle contains one is
 * blocked; single-character handles are blocked by a length rule.
 */
class UsernameBlacklistController extends Controller
{
    public function index()
    {
        return view('studio.admin.username-blacklist', [
            'directory' => UsernameBlacklistService::directory(),
        ]);
    }

    public function bulkAdd(Request $request)
    {
        $data = $request->validate([
            'words' => ['required', 'string', 'max:100000'],
        ]);

        $count = UsernameBlacklistService::bulkAdd($data['words']);

        return back()->with('success', "Submitted {$count} word(s) to the blacklist.");
    }
}
