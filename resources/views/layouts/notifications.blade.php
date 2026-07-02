@php
use App\Services\LivelatchNotificationService;

$latchIdUserId = Auth::user()->supabase_user_id ?? null;

$supabaseNotifications = collect();
$unreadNotificationCount = 0;

try {
    $supabaseNotifications = LivelatchNotificationService::latestForUser($latchIdUserId, 8);
    $unreadNotificationCount = LivelatchNotificationService::unreadCount($latchIdUserId);
} catch (\Throwable $e) {
    $supabaseNotifications = collect();
    $unreadNotificationCount = 0;
}

$GLOBALS['activenotify'] = ($supabaseNotifications->count() > 0 || $unreadNotificationCount > 0);

function livelatchNotificationSeverityClass($severity)
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
    width: min(390px, calc(100vw - 2rem));
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
        linear-gradient(135deg, var(--ll-primary, #0092ec), var(--ll-primary-2, #0ce5de));
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
    max-height: 440px;
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
    background: rgba(0, 146, 236, 0.08);
    transform: translateX(2px);
}

.livelatch-notification-item.is-unread {
    background: rgba(0, 146, 236, 0.12);
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
    color: var(--ll-primary, #0092ec);
    background: rgba(0, 146, 236, 0.14);
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

.livelatch-notification-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 0.45rem;
    color: var(--text-muted, #94a3b8) !important;
    font-size: 0.7rem;
}

.livelatch-notification-pill {
    padding: 0.14rem 0.45rem;
    border-radius: 999px;
    background: rgba(0, 146, 236, 0.1);
    color: var(--ll-primary, #0092ec);
    font-weight: 700;
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
    color: var(--ll-primary, #0092ec) !important;
    background: rgba(0, 146, 236, 0.08);
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
[data-theme="dark"] .livelatch-notification-meta,
[data-theme="dark"] .livelatch-notification-empty {
    color: #a1a1aa !important;
}

[data-theme="dark"] .livelatch-notification-pill {
    background: rgba(37, 244, 238, 0.14);
    color: #7ce4ff;
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
        @forelse($supabaseNotifications as $notification)
            <a href="{{ $notification['action_url'] ?? '#' }}"
               class="livelatch-notification-item {{ empty($notification['read_at']) ? 'is-unread' : '' }}">

                <div class="livelatch-notification-icon {{ livelatchNotificationSeverityClass($notification['severity'] ?? 'info') }}">
                    <i class="bi {{ $notification['icon'] ?? 'bi-bell-fill' }}"></i>
                </div>

                <div class="livelatch-notification-content">
                    <div class="livelatch-notification-title">
                        {{ $notification['title'] ?? 'Notification' }}
                    </div>

                    @if(!empty($notification['message']))
                        <div class="livelatch-notification-message">
                            {{ $notification['message'] }}
                        </div>
                    @endif

                    <div class="livelatch-notification-meta">
                        @if(!empty($notification['source']))
                            <span class="livelatch-notification-pill">{{ ucfirst($notification['source']) }}</span>
                        @endif

                        @if(!empty($notification['created_at']))
                            <span>{{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="livelatch-notification-empty">
                No notifications yet.
            </div>
        @endforelse
    </div>

    <a href="{{ url('/studio/notifications') }}" class="livelatch-notification-footer">
        View notification center
    </a>
</div>
@endpush