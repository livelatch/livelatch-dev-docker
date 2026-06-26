{{--
    Shared profile card for the immersive blade themes. Expects (inherited from
    the including theme view): $user, $links, $userName, $userBio, plus an
    optional $hint string. The theme supplies a full-screen #llx-canvas behind it
    and sets the --llx-* CSS variables. Styling lives in assets/themes/shared/glass.css.
--}}
<main class="llx-stage">
  <section class="llx-card" data-llx-card>
    <div class="llx-avatar-wrap">
      <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="llx-avatar" width="120" height="120">
    </div>

    <h1 class="llx-name" data-text="{{ $userName }}">{{ $userName }}</h1>

    @if($userBio)
      <p class="llx-bio">{{ $userBio }}</p>
    @endif

    @if(count($links) > 0)
      <nav class="llx-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'llx-link'])
      </nav>
    @endif

    @if(!empty($hint))
      <p class="llx-hint">{{ $hint }}</p>
    @endif
  </section>
</main>
