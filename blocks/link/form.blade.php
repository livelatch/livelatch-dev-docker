@php
    // Curated creator platforms — slug + brand hex come straight from Simple Icons
    // (https://simpleicons.org). This same list powers the searchable icon picker;
    // users can also free-type any other Simple Icons slug.
    $iconLibrary = [
        ['slug' => 'youtube',        'label' => 'YouTube',        'hex' => 'FF0000'],
        ['slug' => 'tiktok',         'label' => 'TikTok',         'hex' => '000000'],
        ['slug' => 'instagram',      'label' => 'Instagram',      'hex' => 'E4405F'],
        ['slug' => 'x',              'label' => 'X',              'hex' => '000000'],
        ['slug' => 'twitch',         'label' => 'Twitch',         'hex' => '9146FF'],
        ['slug' => 'discord',        'label' => 'Discord',        'hex' => '5865F2'],
        ['slug' => 'spotify',        'label' => 'Spotify',        'hex' => '1DB954'],
        ['slug' => 'github',         'label' => 'GitHub',         'hex' => '181717'],
        ['slug' => 'facebook',       'label' => 'Facebook',       'hex' => '0866FF'],
        ['slug' => 'patreon',        'label' => 'Patreon',        'hex' => 'FF424D'],
        ['slug' => 'kofi',           'label' => 'Ko-fi',          'hex' => 'FF5E5B'],
        ['slug' => 'buymeacoffee',   'label' => 'Buy Me a Coffee','hex' => 'FFDD00'],
        ['slug' => 'substack',       'label' => 'Substack',       'hex' => 'FF6719'],
        ['slug' => 'threads',        'label' => 'Threads',        'hex' => '000000'],
        ['slug' => 'snapchat',       'label' => 'Snapchat',       'hex' => 'FFFC00'],
        ['slug' => 'linkedin',       'label' => 'LinkedIn',       'hex' => '0A66C2'],
        ['slug' => 'reddit',         'label' => 'Reddit',         'hex' => 'FF4500'],
        ['slug' => 'pinterest',      'label' => 'Pinterest',      'hex' => 'BD081C'],
        ['slug' => 'bluesky',        'label' => 'Bluesky',        'hex' => '0285FF'],
        ['slug' => 'mastodon',       'label' => 'Mastodon',       'hex' => '6364FF'],
        ['slug' => 'telegram',       'label' => 'Telegram',       'hex' => '26A5E4'],
        ['slug' => 'whatsapp',       'label' => 'WhatsApp',       'hex' => '25D366'],
        ['slug' => 'soundcloud',     'label' => 'SoundCloud',     'hex' => 'FF5500'],
        ['slug' => 'applemusic',     'label' => 'Apple Music',    'hex' => 'FA243C'],
        ['slug' => 'bandcamp',       'label' => 'Bandcamp',       'hex' => '408294'],
        ['slug' => 'behance',        'label' => 'Behance',        'hex' => '1769FF'],
        ['slug' => 'dribbble',       'label' => 'Dribbble',       'hex' => 'EA4C89'],
        ['slug' => 'medium',         'label' => 'Medium',         'hex' => '000000'],
        ['slug' => 'etsy',           'label' => 'Etsy',           'hex' => 'F16521'],
        ['slug' => 'steam',          'label' => 'Steam',          'hex' => '000000'],
        ['slug' => 'xbox',           'label' => 'Xbox',           'hex' => '107C10'],
        ['slug' => 'playstation',    'label' => 'PlayStation',    'hex' => '003791'],
        ['slug' => 'linktree',       'label' => 'Linktree',       'hex' => '43E660'],
        ['slug' => 'gumroad',        'label' => 'Gumroad',        'hex' => 'FF90E8'],
        ['slug' => 'cashapp',        'label' => 'Cash App',       'hex' => '00C244'],
        ['slug' => 'paypal',         'label' => 'PayPal',         'hex' => '003087'],
    ];

    $currentIcon = $custom_icon ?? '';
@endphp

<div class="ll-lf" data-ll-link-form>
    <script type="application/json" data-ll-icon-library>@json($iconLibrary)</script>

    <div class="ll-lf-field">
        <label class="form-label">Platform</label>
        <select class="form-control ll-lf-platform" data-ll-platform>
            <option value="custom">Custom link</option>
            @foreach($iconLibrary as $p)
                <option value="{{ $p['slug'] }}" data-hex="{{ $p['hex'] }}" data-label="{{ $p['label'] }}">{{ $p['label'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="ll-lf-field">
        <label class="form-label">{{ __('messages.Title') }}</label>
        <input type="text" name="title" value="{{ $title }}" class="form-control ll-lf-title" placeholder="Link title" />
    </div>

    <div class="ll-lf-field">
        <label class="form-label">{{ __('messages.URL') }}</label>
        <input type="url" name="link" value="{{ $link }}" class="form-control ll-lf-url" placeholder="https://example.com" required />
    </div>

    <div class="ll-lf-field" data-ll-icon-picker>
        <label class="form-label">Icon</label>
        <div class="ll-lf-iconrow">
            <span class="ll-lf-iconpreview" data-ll-icon-preview aria-hidden="true"></span>
            <input type="search" class="form-control ll-lf-iconsearch" data-ll-icon-search placeholder="Search Simple Icons — e.g. youtube, ko-fi, notion" autocomplete="off" />
        </div>
        <div class="ll-lf-iconresults" data-ll-icon-results></div>
        <span class="small text-muted">Pick a brand icon, or type any slug from <a href="https://simpleicons.org/" target="_blank" rel="noopener noreferrer">simpleicons.org</a>. Whether icons show, and their colour, is set on the <a href="{{ url('/studio/theme') }}">Themes page</a>.</span>
    </div>

    <div class="ll-lf-field form-check ll-lf-favicon-wrap">
        <input type="checkbox" class="form-check-input" value="1" name="GetSiteIcon" id="GetSiteIcon" data-ll-favicon @if(($button_id ?? 1) == 2) checked @endif>
        <label class="form-check-label" for="GetSiteIcon">{{ __('messages.Show website icon on button') }} (use the site favicon instead)</label>
    </div>

    <input type="hidden" name="custom_icon" value="{{ $currentIcon }}" data-ll-icon-value>
</div>
