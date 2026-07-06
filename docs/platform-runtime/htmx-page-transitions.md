# HTMX Page Transitions

Livelatch Studio uses [HTMX](https://htmx.org/) to swap dashboard screens into
the persistent sidebar layout without a full page reload. Screen navigation is
animated with a cross-fade transition; there are **no skeleton loaders**.

> History: the Studio previously showed themed skeleton loaders during swaps
> (removed 2026-07-03). The skeletons hid the outgoing screen immediately on
> every click (`#ll-content[aria-busy="true"] { opacity: 0 }`) and overlaid a
> shimmer, so even fast responses produced a blank → skeleton → content flash
> that made navigation feel slower than it was.

## How it works

All screen-swap chrome lives in `resources/views/layouts/sidebar.blade.php`.

**Transition.** `htmx.config.globalViewTransitions = true` makes HTMX wrap
every swap in the
[View Transitions API](https://developer.mozilla.org/en-US/docs/Web/API/View_Transition_API)
where the browser supports it (Chromium, Safari). `#ll-content` carries
`view-transition-name: ll-content`, and the
`::view-transition-old/new(ll-content)` keyframes cross-fade the outgoing
screen up and the incoming screen in with a small rise
(`ll-vt-out` / `ll-vt-in`).

**Fallback.** Browsers without the API (Firefox) use the existing
`htmx-swapping` (fade out) and `htmx-settling` (`ll-content-enter` fade/rise)
classes, toggled by the `htmx:beforeSwap` / `htmx:afterSwap` listeners.

**Loading feedback.** A fixed 2px `.ll-progress` bar sits at the top of the
viewport. `htmx:beforeRequest` adds `body.ll-loading`; the bar's CSS
`transition-delay: 0.18s` means it only becomes visible when a response is
genuinely slow — fast swaps show no loading chrome at all. A pending-request
counter keeps it correct across overlapping requests
(`htmx:afterRequest` / `htmx:sendAbort` / `htmx:timeout` decrement).

Both the transition and the progress bar respect `prefers-reduced-motion`.

## Link markup

`#ll-content` remains the only screen swap target. Nav links use the
`llHtmxAttrs($url)` helper (sidebar layout) or the equivalent explicit
attributes:

```blade
<a href="{{ url('/studio/links') }}"
   hx-get="{{ url('/studio/links') }}"
   hx-target="#ll-content"
   hx-select="#ll-content > *"
   hx-push-url="true"
   hx-swap="innerHTML">
```

No `hx-indicator` is needed for screen navigation. Nav metadata in
`app/Http/Livewire/StudioNavigation.php` no longer carries `skeleton` keys.

Local widgets that want their own loading state (for example the admin users
table) can still use a scoped `hx-indicator` pointing at their own element —
that mechanism is untouched.

## Related notes

- LinkStack's `skeleton-auto.css` / `skeleton-dark.css` / `skeleton-light.css`
  (public themes, installer) are an unrelated CSS framework and were not part
  of this system.
