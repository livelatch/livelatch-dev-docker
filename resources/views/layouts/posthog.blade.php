@if(config('services.posthog.key'))
<script>
    !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+" (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
    // OAuth implicit-flow tokens (Supabase/Google) land in the URL fragment on
    // /callback/* pages, e.g. #access_token=<jwt>. Strip auth material out of any
    // URL property so PostHog never stores a usable token.
    function llRedactAuthFromUrl(value) {
        if (typeof value !== 'string' || value === '') return value;
        var out = value;
        var hashAt = out.indexOf('#');
        if (hashAt !== -1 && /(?:access_token|refresh_token|id_token|provider_token|code|state)=/i.test(out.slice(hashAt))) {
            out = out.slice(0, hashAt) + '#redacted';
        }
        return out.replace(/([?&](?:access_token|refresh_token|id_token|provider_token|token|code|otp|state)=)[^&#]*/gi, '$1redacted');
    }

    function llSanitizePostHogProperties(properties) {
        ['$current_url', '$referrer', '$initial_current_url', '$initial_referrer', '$pathname'].forEach(function (key) {
            if (properties[key]) {
                properties[key] = llRedactAuthFromUrl(properties[key]);
            }
        });
        return properties;
    }

    // OAuth callback pages carry the token in the URL fragment. Session replay
    // captures the page URL through its own pipeline (sanitize_properties does not
    // reach it), so never record or autocapture on these transient auth routes.
    var llIsAuthCallbackPage = /^\/(callback|auth)\b/i.test(window.location.pathname);

    posthog.init('{{ config('services.posthog.key') }}', {
        api_host: '{{ config('services.posthog.host') }}',
        defaults: '2026-01-30',
        autocapture: !llIsAuthCallbackPage,
        capture_pageview: !llIsAuthCallbackPage,
        capture_pageleave: !llIsAuthCallbackPage,
        disable_session_recording: llIsAuthCallbackPage,
        cross_subdomain_cookie: true,
        secure_cookie: true,
        person_profiles: 'identified_only',
        sanitize_properties: llSanitizePostHogProperties,
        loaded: function (posthog) {
            posthog.register({
                app: 'livelatch',
                app_host: window.location.hostname,
            });
        },
    });

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
