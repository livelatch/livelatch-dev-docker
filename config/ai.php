<?php

/*
|--------------------------------------------------------------------------
| AI disclosure — approved tools & badge colours
|--------------------------------------------------------------------------
|
| Source of truth for the AI Use Policy (docs/policies/ai-use-policy.md).
| Each approved tool maps to a Simple Icons slug (https://simpleicons.org)
| and its brand hex, which drives the default AI badge colour. A theme may
| override the colour per-bundle via manifest.ai.badgeColor.
|
*/

return [
    // Default badge colour when no approved tool is matched (Claude).
    'default_color' => '#D97757',

    // Allowed manifest.ai.category values.
    'categories' => ['none', 'assisted', 'generated'],

    // Allowed manifest.ai.scope values. Art / video / audio are NEVER allowed
    // to be AI-generated (policy §4), so they are intentionally absent here.
    'scopes' => ['code', 'text'],

    // Approved AI tools: canonical name => Simple Icons slug + brand hex.
    'tools' => [
        'Claude'         => ['slug' => 'claude',        'color' => '#D97757'],
        'ChatGPT'        => ['slug' => 'openai',        'color' => '#0081A5'],
        'GitHub Copilot' => ['slug' => 'githubcopilot', 'color' => '#000000'],
        'Gemini'         => ['slug' => 'googlegemini',  'color' => '#8E75B2'],
    ],
];
