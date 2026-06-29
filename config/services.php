<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_CALLBACK_URL'),
    ],
    'twitter' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_CALLBACK_URL'),
    ],
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_CALLBACK_URL'),
    ],
    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => 'http://example.com/callback-url',
    ],

    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'anon_key' => env('SUPABASE_ANON_KEY'),
    ],

    'supabase_url' => env('LATCHID_SUPABASE_URL', env('SUPABASE_URL')),
    'supabase_service_role_key' => env('LATCHID_SERVICE_ROLE_KEY', env('SUPABASE_SERVICE_ROLE_KEY')),
    'supabase_profile_link_clicks_table' => env('SUPABASE_PROFILE_LINK_CLICKS_TABLE', 'livelatch_profile_link_clicks'),
    'supabase_social_metrics_table' => env('SUPABASE_SOCIAL_METRICS_TABLE', 'livelatch_social_metric_snapshots'),
    'tiktok_oauth_authorize_url' => env('TIKTOK_OAUTH_AUTHORIZE_URL', 'https://yaljyfdfnphxzuhqlbfs.functions.supabase.co/tiktok-oauth/authorize'),

    // LatchDeck is a platform-agnostic API served by Encore. Livelatch is one
    // client; it authenticates with the service API key.
    'latchdeck' => [
        'api_url' => env('LATCHDECK_API_URL', 'https://staging-latchdeck-7iu2.encr.app'),
        'api_key' => env('LATCHDECK_SERVICE_API_KEY'),
    ],

    // RAWG video-game database — powers the Stream Schedule "show game" picker
    // (game art + ESRB rating). Free key from https://rawg.io/apidocs.
    'rawg' => [
        'key' => env('RAWG_API_KEY'),
    ],

    // Unsplash — powers the LatchDeck card editor's "search an image" option.
    // Use the Unsplash "Access Key" (the API client_id). ToS requires attribution
    // and a download trigger, both handled in LatchDeckController.
    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
        // utm_source appended to attribution links per Unsplash API guidelines.
        'utm_source' => env('UNSPLASH_UTM_SOURCE', 'livelatch'),
    ],

    'posthog' => [
        'key' => env('POSTHOG_API_KEY'),
        'host' => env('POSTHOG_HOST', 'https://eu.i.posthog.com'),
    ],

    // Resend transactional email + audience/contact sync.
    //
    // api_key must be a full-access key (Contacts/Audiences/Topics scopes) for
    // ResendContactService; the same key is used to send service and
    // notification emails server-side over the HTTP API. audience_id is the
    // Resend audience that Livelatch contacts are imported into.
    'resend' => [
        'api_key' => env('RESEND_FULL_API_KEY'),
        'audience_id' => env('RESEND_AUDIENCE_ID'),
        'from' => env('RESEND_FROM', 'Livelatch <hello@livelatch.com>'),
    ],

];
