<?php

/*
|--------------------------------------------------------------------------
| Creator platforms — where a requested creator posts
|--------------------------------------------------------------------------
|
| Shown as tick-boxes on the "creator not found" request page and summarised
| on the admin Creator Requests page, so vetting starts with knowing where to
| find the creator. key => label + Simple Icons slug (https://simpleicons.org)
| + brand colour. The key is the canonical stored value (allowlisted server-side).
|
*/

return [
    'youtube'   => ['label' => 'YouTube',   'slug' => 'youtube',   'color' => '#FF0000'],
    'tiktok'    => ['label' => 'TikTok',    'slug' => 'tiktok',    'color' => '#25F4EE'],
    'twitch'    => ['label' => 'Twitch',    'slug' => 'twitch',    'color' => '#9146FF'],
    'kick'      => ['label' => 'Kick',      'slug' => 'kick',      'color' => '#53FC18'],
    'steam'     => ['label' => 'Steam',     'slug' => 'steam',     'color' => '#66C0F4'],
    'instagram' => ['label' => 'Instagram', 'slug' => 'instagram', 'color' => '#E4405F'],
    'x'         => ['label' => 'X',         'slug' => 'x',         'color' => '#FFFFFF'],
    'discord'   => ['label' => 'Discord',   'slug' => 'discord',   'color' => '#5865F2'],
    'patreon'   => ['label' => 'Patreon',   'slug' => 'patreon',   'color' => '#FF424D'],
    'facebook'  => ['label' => 'Facebook',  'slug' => 'facebook',  'color' => '#0866FF'],
];
