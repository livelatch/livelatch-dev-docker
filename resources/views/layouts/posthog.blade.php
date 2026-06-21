@if(config('services.posthog.key'))
<script>
    !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+" (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
    // Cookie/analytics consent. Bump LL_COOKIE_CONSENT_VERSION when cookie use
    // changes so the homepage banner re-appears and prior consent is voided.
    window.LL_COOKIE_CONSENT_VERSION = '1';
    window.llReadCookieConsent = function () {
        try {
            if (localStorage.getItem('ll_cookie_consent_version') !== window.LL_COOKIE_CONSENT_VERSION) {
                return null;
            }
            var v = localStorage.getItem('ll_cookie_consent');
            return (v === 'all' || v === 'deny') ? v : null;
        } catch (e) { return null; }
    };

    // 'all' => persistent cookies + session replay. Anything else (deny / no
    // choice yet) => cookieless memory mode, no session replay. So PostHog never
    // writes a cookie until the visitor explicitly allows it on the homepage.
    var llConsent = window.llReadCookieConsent();
    var llAllow = llConsent === 'all';

    posthog.init('{{ config('services.posthog.key') }}', {
        api_host: '{{ config('services.posthog.host') }}',
        defaults: '2026-01-30',
        autocapture: true,
        capture_pageview: true,
        capture_pageleave: true,
        cross_subdomain_cookie: true,
        secure_cookie: true,
        person_profiles: 'identified_only',
        persistence: llAllow ? 'localStorage+cookie' : 'memory',
        disable_session_recording: !llAllow,
        loaded: function (posthog) {
            posthog.register({
                app: 'livelatch',
                app_host: window.location.hostname,
            });
        },
    });

    // Called by the homepage cookie banner. Applies the choice live (no reload)
    // and persists it for future page loads.
    window.llSetCookieConsent = function (choice) {
        choice = (choice === 'all') ? 'all' : 'deny';
        try {
            localStorage.setItem('ll_cookie_consent', choice);
            localStorage.setItem('ll_cookie_consent_version', window.LL_COOKIE_CONSENT_VERSION);
        } catch (e) {}

        if (choice === 'all') {
            posthog.set_config({ persistence: 'localStorage+cookie' });
            if (posthog.opt_in_capturing) posthog.opt_in_capturing();
            if (posthog.startSessionRecording) posthog.startSessionRecording();
        } else {
            if (posthog.stopSessionRecording) posthog.stopSessionRecording();
            posthog.set_config({ persistence: 'memory' });
        }
        return choice;
    };

    @auth
    @php($llDistinctId = auth()->user()->supabase_user_id ? 'latchid:'.auth()->user()->supabase_user_id : 'laravel-user:'.auth()->id())
    posthog.identify(@json($llDistinctId), {
        email: @json(auth()->user()->email),
        name: @json(auth()->user()->name ?? ''),
        livelatch_user_id: @json((string) auth()->id()),
        latchid_user_id: @json((string) (auth()->user()->supabase_user_id ?? '')),
    });
    @endauth
</script>
@endif
