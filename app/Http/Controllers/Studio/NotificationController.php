<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Services\LivelatchNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backs the notification center modal in the studio shell. All data lives in
 * Supabase and is reached through LivelatchNotificationService.
 */
class NotificationController extends Controller
{
    /**
     * Return the current user's notifications split into unread and read
     * (inbox) buckets, for the notification center modal.
     */
    public function index(Request $request): JsonResponse
    {
        $latchIdUserId = $request->user()?->supabase_user_id;

        $notifications = LivelatchNotificationService::forUser($latchIdUserId, 50);

        $unread = $notifications->filter(fn ($n) => empty($n['is_read']))->values();
        $read = $notifications->filter(fn ($n) => !empty($n['is_read']))->values();

        return response()->json([
            'unread_count' => $unread->count(),
            'unread' => $unread,
            'read' => $read,
        ]);
    }

    /**
     * Mark a single notification as read for the current user.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $latchIdUserId = $request->user()?->supabase_user_id;

        $ok = LivelatchNotificationService::markAsRead($latchIdUserId, $id);

        return response()->json([
            'ok' => $ok,
            'unread_count' => LivelatchNotificationService::unreadCount($latchIdUserId),
        ], $ok ? 200 : 422);
    }

    /**
     * Move a notification back to unread for the current user.
     */
    public function markUnread(Request $request, string $id): JsonResponse
    {
        $latchIdUserId = $request->user()?->supabase_user_id;

        $ok = LivelatchNotificationService::markAsUnread($latchIdUserId, $id);

        return response()->json([
            'ok' => $ok,
            'unread_count' => LivelatchNotificationService::unreadCount($latchIdUserId),
        ], $ok ? 200 : 422);
    }

    /**
     * Mark every visible notification as read for the current user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $latchIdUserId = $request->user()?->supabase_user_id;

        $count = LivelatchNotificationService::markAllAsRead($latchIdUserId);

        return response()->json([
            'ok' => true,
            'marked' => $count,
            'unread_count' => LivelatchNotificationService::unreadCount($latchIdUserId),
        ]);
    }
}
