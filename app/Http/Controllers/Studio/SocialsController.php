<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Services\LivelatchSocialService;

class SocialsController extends Controller
{
    public function index()
    {
        return view('studio.community.socials', [
            'socials' => LivelatchSocialService::activeForDisplay(),
        ]);
    }
}
