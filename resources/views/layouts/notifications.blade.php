@php
use App\Models\UserData;
use App\Services\LivelatchNotificationService;

$GLOBALS['activenotify'] = false;

$compromised = false;
$notifyID = Auth::user()->id;
$latchIdUserId = Auth::user()->supabase_user_id ?? null;

/*
|--------------------------------------------------------------------------
| Admin security check
|--------------------------------------------------------------------------
*/

if (auth()->user()->role == 'admin') {
    function getUrlSatusCodesb($urlsb, $timeoutsb = 3)
    {
        $chsb = curl_init();

        $optssb = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $urlsb,
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => $timeoutsb,
        ];

        curl_setopt_array($chsb, $optssb);
        curl_exec($chsb);

        $status = curl_getinfo($chsb, CURLINFO_HTTP_CODE);

        curl_close($chsb);

        return $status;
    }

    $url1sb = getUrlSatusCodesb(url('.env'));
    $url2sb = getUrlSatusCodesb(url('database/database.sqlite'));

    $compromised = ($url1sb == '200' || $url2sb == '200');
}

/*
|--------------------------------------------------------------------------
| Supabase notifications
|--------------------------------------------------------------------------
*/

$supabaseNotifications = collect();
$unreadNotificationCount = 0;

try {
    if ($latchIdUserId) {
        $supabaseNotifications = LivelatchNotificationService::latestForUser($latchIdUserId, 6);
        $unreadNotificationCount = LivelatchNotificationService::unreadCount($latchIdUserId);
    }
} catch (\Throwable $e) {
    $supabaseNotifications = collect();
    $unreadNotificationCount = 0;
}

/*
|--------------------------------------------------------------------------
| Legacy local notifications
|--------------------------------------------------------------------------
*/

$legacyNotifications = collect([
    [
        'id' => 'modal-1',
        'icon' => 'bi bi-exclamation-triangle-fill',
        'title' => __('messages.Your security is at risk!'),
        'message' => __('messages.Immediate action is required!'),
        'condition' => $compromised,
        'dismiss' => '',
        'adminonly' => true,
        'severity' => 'danger',
    ],
    [
        'id' => 'modal-star',
        'icon' => 'bi bi-heart-fill',
        'title' => __('messages.Enjoying Linkstack?'),
        'message' => __('messages.Help Us Out'),
        'condition' => UserData::getData($notifyID, 'hide-star-notification') !== true,
        'dismiss' => __('messages.Hide this notification'),
        'adminonly' => true,
        'severity' => 'info',
    ],
])->filter(function ($notification) {
    return $notification['condition'] && (!$notification['adminonly'] || auth()->user()->role == 'admin');
});

$totalNotifications = $supabaseNotifications->count() + $legacyNotifications->count();

if ($totalNotifications > 0 || $unreadNotificationCount > 0) {
    $GLOBALS['activenotify'] = true;
}

function severityIconClass($severity)
{
    return match ($severity) {
        'success' => 'notification-success',
        'warning' => 'notification-warning',
        'danger' => 'notification-danger',
        default => 'notification-info',
    };
}
@endphp

@push('notifications')
<style>
.livelatch-notification-panel {
    width: min(380px, calc(100vw - 2rem));
    overflow: hidden;
    border-radius: 22px;
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, rgba(15, 23, 42, 0.12));
    box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
}

.livelatch-notification-header {
    padding: 1rem;
    color: #ffffff;
    background:
        radial-gradient(circle at top right, rgba(255,255,255,0.22), transparent 34%),
        linear-gradient(135deg, #7c3aed, #9333ea);
}

.livelatch-notification-header h6 {
    margin: 0;
    color: #ffffff !important;
    font-weight: 800;
}

.livelatch-notification-header span {
    display: block;
    margin-top: 0.15rem;
    color: rgba(255,255,255,0.78) !important;
    font-size: 0.78rem;
}

.livelatch-notification-list {
    max-height: 420px;
    overflow-y: auto;
    background: var(--surface, #ffffff);
}

.livelatch-notification-item {
    display: flex;
    gap: 0.85rem;
    padding: 0.95rem 1rem;
    color: var(--text, #111827) !important;
    text-decoration: none;
    border-bottom: 1px solid var(--border, rgba(15, 23, 42, 0.1));
    transition: background 0.2s ease, transform 0.2s ease;
}

.livelatch-notification-item:hover {
    background: rgba(124, 58, 237, 0.08);
    transform: translateX(2px);
}

.livelatch-notification-item.is-unread {
    background: rgba(124, 58, 237, 0.12);
}

.livelatch-notification-icon {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    border-radius: 14px;
    font-size: 1rem;
}

.notification-info {
    color: #7c3aed;
    background: rgba(124, 58, 237, 0.14);
}

.notification-success {
    color: #16a34a;
    background: rgba(34, 197, 94, 0.14);
}

.notification-warning {
    color: #d97706;
    background: rgba(245, 158, 11, 0.16);
}

.notification-danger {
    color: #dc2626;
    background: rgba(239, 68, 68, 0.15);
}

.livelatch-notification-content {
    min-width: 0;
    flex: 1;
}

.livelatch-notification-title {
    color: var(--text, #111827) !important;
    font-size: 0.9rem;
    font-weight: 800;
    line-height: 1.25;
}

.livelatch-notification-message {
    margin-top: 0.15rem;
    color: var(--text-muted, #64748b) !important;
    font-size: 0.78rem;
    line-height: 1.35;
}

.livelatch-notification-time {
    margin-top: 0.35rem;
    color: var(--text-muted, #94a3b8) !important;
    font-size: 0.7rem;
}

.livelatch-notification-empty {
    padding: 1.25rem;
    color: var(--text-muted, #64748b);
    text-align: center;
    font-size: 0.85rem;
}

.livelatch-notification-footer {
    display: block;
    padding: 0.85rem 1rem;
    color: #7c3aed !important;
    background: rgba(124, 58, 237, 0.08);
    text-align: center;
    text-decoration: none;
    font-size: 0.82rem;
    font-weight: 800;
}

[data-theme="dark"] .livelatch-notification-panel,
[data-theme="dark"] .livelatch-notification-list {
    background: #111025 !important;
    border-color: rgba(255,255,255,0.12) !important;
}

[data-theme="dark"] .livelatch-notification-item {
    color: #f8fafc !important;
    border-color: rgba(255,255,255,0.1) !important;
}

[data-theme="dark"] .livelatch-notification-title {
    color: #f8fafc !important;
}

[data-theme="dark"] .livelatch-notification-message,
[data-theme="dark"] .livelatch-notification-time,
[data-theme="dark"] .livelatch-notification-empty {
    color: #a1a1aa !important;
}
</style>

<div class="livelatch-notification-panel">
    <div class="livelatch-notification-header">
        <h6>Notifications</h6>

        @if($unreadNotificationCount > 0)
            <span>{{ $unreadNotificationCount }} unread notification{{ $unreadNotificationCount === 1 ? '' : 's' }}</span>
        @else
            <span>You're all caught up</span>
        @endif
    </div>

    <div class="livelatch-notification-list">
        @foreach($legacyNotifications as $notification)
            <a data-bs-target="#{{ $notification['id'] }}"
               data-bs-toggle="modal"
               class="livelatch-notification-item is-unread"
               style="cursor:pointer!important;">
                <div class="livelatch-notification-icon {{ severityIconClass($notification['severity']) }}">
                    <i class="{{ $notification['icon'] }}"></i>
                </div>

                <div class="livelatch-notification-content">
                    <div class="livelatch-notification-title">
                        {{ $notification['title'] }}
                    </div>

                    <div class="livelatch-notification-message">
                        {{ $notification['message'] }}
                    </div>
                </div>
            </a>
        @endforeach

        @forelse($supabaseNotifications as $notification)
            <a href="{{ $notification['action_url'] ?? '#' }}"
               class="livelatch-notification-item {{ empty($notification['read_at']) ? 'is-unread' : '' }}">
                <div class="livelatch-notification-icon {{ severityIconClass($notification['severity'] ?? 'info') }}">
                    <i class="bi {{ $notification['icon'] ?? 'bi-bell-fill' }}"></i>
                </div>

                <div class="livelatch-notification-content">
                    <div class="livelatch-notification-title">
                        {{ $notification['title'] ?? 'Notification' }}
                    </div>

                    <div class="livelatch-notification-message">
                        {{ $notification['message'] ?? '' }}
                    </div>

                    @if(!empty($notification['created_at']))
                        <div class="livelatch-notification-time">
                            {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </a>
        @empty
            @if($legacyNotifications->count() === 0)
                <div class="livelatch-notification-empty">
                    No notifications yet.
                </div>
            @endif
        @endforelse
    </div>

    <a href="{{ url('/studio/notifications') }}" class="livelatch-notification-footer">
        View notification center
    </a>
</div>
@endpush

@push('sidebar-scripts')
@php
function notification($dismiss = '', $ntid, $heading, $body) {
    $closeMSG = __('messages.Close');
    $dismissMSG = __('messages.Dismiss');
    $dismissBtn = '';

    if ($dismiss) {
        $dismissBtn = '<a href="' . url()->current() . '?dismiss=' . $dismiss . '" class="btn btn-danger">'.$dismissMSG.'</a>';
    }

    echo <<<MODAL
    <div class="modal fade" id="$ntid" data-bs-backdrop="true" data-bs-keyboard="false" tabindex="-1" aria-labelledby="${ntid}-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="${ntid}-label">$heading</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="$closeMSG"></button>
                </div>
                <div class="modal-body">
                    <div class="bd-example">
                        $body
                    </div>
                </div>
                <div class="modal-footer">
                    $dismissBtn
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">$closeMSG</button>
                </div>
            </div>
        </div>
    </div>
MODAL;
}

notification('', 'modal-1', __('messages.Your security is at risk!'), '<b>'.__('messages.security.msg1').'</b> '.__('messages.security.msg2').'<br><br>'.__('messages.security.msg3').'<br><a href="'.url('admin/config#5').'">'.__('messages.security.msg3').'</a>.');

notification('hide-star-notification', 'modal-star', __('messages.Support Linkstack'), ''.__('messages.support.msg1').' <a target="_blank" href="https://github.com/linkstackorg/linkstack">'.__('messages.support.msg2').'</a>. '.__('messages.support.msg3').'<br><br>'.__('messages.support.msg4').' <a target="_blank" href="https://linkstack.org/donate">'.__('messages.support.msg5').'<br><br>'.__('messages.support.msg6').'');
@endphp
@endpush

@php
if (isset($_GET['dismiss'])) {
    $dismiss = $_GET['dismiss'];
    $param = str_replace('dismiss=', '', $dismiss);

    UserData::saveData($notifyID, $param, true);

    exit(header("Location: " . url()->current()));
}
@endphp