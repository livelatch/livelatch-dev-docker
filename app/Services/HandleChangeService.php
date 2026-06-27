<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Name / URL-handle changes.
 *
 * Creators can only pick names connected to their registered account (their
 * linked LatchID identities), and any change is a *request* an admin approves
 * in the "User Requests" page. On approval, Laravel mutates Railway MySQL
 * (users.littlelink_name / users.name) and records the previous handle as an
 * alias so old /@oldname links keep resolving. Requests live in Supabase
 * (public.handle_change_requests); MySQL stays the source of truth for the live
 * handle + aliases.
 */
class HandleChangeService
{
    /**
     * Names a creator may pick from — their linked LatchID identities (Supabase
     * profile + connected socials) plus their current name. Each entry:
     * ['name' => display, 'handle' => slug, 'source' => label].
     *
     * @return array<int, array{name:string,handle:string,source:string}>
     */
    public static function candidateNames(User $user): array
    {
        $out = [];
        $add = function (?string $name, string $source) use (&$out) {
            $name = trim((string) $name);
            if ($name !== '') {
                $out[] = ['name' => $name, 'handle' => self::slugHandle($name), 'source' => $source];
            }
        };

        $add($user->name, 'Current name');

        $uuid = $user->supabase_user_id;
        if ($uuid) {
            $profile = self::get('profiles', ['id' => 'eq.' . $uuid, 'select' => 'display_name']);
            $add($profile[0]['display_name'] ?? null, 'LatchID');

            $tiktok = self::get('latchid_tiktok_accounts', ['latchid_user_id' => 'eq.' . $uuid, 'select' => 'display_name']);
            $add($tiktok[0]['display_name'] ?? null, 'TikTok');
        }

        // Dedupe by display name (case-insensitive), keep first source seen.
        $seen = [];
        $unique = [];
        foreach ($out as $c) {
            $k = mb_strtolower($c['name']);
            if (isset($seen[$k]) || $c['handle'] === '') {
                continue;
            }
            $seen[$k] = true;
            $unique[] = $c;
        }

        return $unique;
    }

    /** Handle-safe slug of a display name. */
    public static function slugHandle(string $name): string
    {
        return mb_substr(preg_replace('/[^a-z0-9]+/', '', mb_strtolower($name)) ?? '', 0, 60);
    }

    /** A handle is free if no other user owns it and it isn't another user's alias. */
    public static function handleAvailable(string $handle, int $exceptUserId): bool
    {
        $handle = strtolower(trim($handle));
        if ($handle === '') {
            return false;
        }

        $takenByUser  = User::where('littlelink_name', $handle)->where('id', '!=', $exceptUserId)->exists();
        $takenByAlias = DB::table('handle_aliases')->where('alias', $handle)->where('user_id', '!=', $exceptUserId)->exists();

        return !$takenByUser && !$takenByAlias;
    }

    /** Submit a pending change request to Supabase. */
    public static function submit(User $user, ?string $requestedHandle, ?string $requestedDisplayName, ?string $email): bool
    {
        $response = self::post('handle_change_requests', [
            'laravel_user_id'        => $user->id,
            'supabase_user_id'       => $user->supabase_user_id,
            'email'                  => $email ?: $user->email,
            'current_handle'         => $user->littlelink_name,
            'current_display_name'   => $user->name,
            'requested_handle'       => $requestedHandle ? self::slugHandle($requestedHandle) : null,
            'requested_display_name' => $requestedDisplayName ?: null,
            'status'                 => 'pending',
        ]);

        return (bool) ($response && $response->successful());
    }

    /** Has this user already got an open request? (One at a time keeps it clean.) */
    public static function hasPending(User $user): bool
    {
        $rows = self::get('handle_change_requests', [
            'laravel_user_id' => 'eq.' . $user->id,
            'status'          => 'eq.pending',
            'select'          => 'id',
            'limit'           => '1',
        ]);

        return !empty($rows);
    }

    /** Pending requests for the admin User Requests page, newest first. */
    public static function pending(): array
    {
        return self::get('handle_change_requests', ['status' => 'eq.pending', 'order' => 'created_at.desc']);
    }

    /**
     * Approve or reject a request. On approve: mutate MySQL (handle + name) and
     * record the previous handle as an alias.
     *
     * @return array{ok:bool,error?:string}
     */
    public static function decide(int $id, bool $approve, string $reviewer): array
    {
        $rows = self::get('handle_change_requests', ['id' => 'eq.' . $id, 'select' => '*']);
        $req = $rows[0] ?? null;

        if (!$req) {
            return ['ok' => false, 'error' => 'Request not found.'];
        }
        if (($req['status'] ?? '') !== 'pending') {
            return ['ok' => false, 'error' => 'That request was already reviewed.'];
        }

        if ($approve) {
            $user = User::find($req['laravel_user_id'] ?? 0);
            if (!$user) {
                return ['ok' => false, 'error' => 'That user no longer exists.'];
            }

            $newHandle = $req['requested_handle'] ?? null;
            $newName   = $req['requested_display_name'] ?? null;

            if ($newHandle) {
                $newHandle = self::slugHandle($newHandle);
                if (!self::handleAvailable($newHandle, $user->id)) {
                    return ['ok' => false, 'error' => "Handle @{$newHandle} is already taken — reject this and ask for another."];
                }
            }

            DB::transaction(function () use ($user, $newHandle, $newName) {
                if ($newHandle && strtolower($newHandle) !== strtolower((string) $user->littlelink_name)) {
                    $old = strtolower((string) $user->littlelink_name);
                    if ($old !== '') {
                        DB::table('handle_aliases')->insertOrIgnore([
                            'alias'      => $old,
                            'user_id'    => $user->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    // The new handle is now canonical — it must not also be an alias.
                    DB::table('handle_aliases')->where('alias', strtolower($newHandle))->delete();
                    $user->littlelink_name = $newHandle;
                }
                if ($newName) {
                    $user->name = $newName;
                }
                $user->save();
            });
        }

        self::patch('handle_change_requests', ['id' => 'eq.' . $id], [
            'status'      => $approve ? 'approved' : 'rejected',
            'reviewed_at' => now()->toIso8601String(),
            'reviewed_by' => mb_substr($reviewer, 0, 120),
        ]);

        return ['ok' => true];
    }

    /* --------------------------------------------------------------------- */
    /*  Supabase REST plumbing                                                */
    /* --------------------------------------------------------------------- */

    private static function get(string $table, array $query): array
    {
        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return [];
        }
        try {
            $r = Http::withHeaders(self::headers($key))->get($baseUrl . '/rest/v1/' . $table, $query);

            return $r->successful() ? ($r->json() ?: []) : [];
        } catch (\Throwable $e) {
            Log::warning('HandleChangeService: get failed', ['table' => $table, 'error' => $e->getMessage()]);

            return [];
        }
    }

    private static function post(string $table, array $body)
    {
        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return null;
        }
        try {
            return Http::withHeaders(array_merge(self::headers($key), ['Prefer' => 'return=minimal']))
                ->post($baseUrl . '/rest/v1/' . $table, $body);
        } catch (\Throwable $e) {
            Log::warning('HandleChangeService: post failed', ['table' => $table, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private static function patch(string $table, array $query, array $body)
    {
        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return null;
        }
        try {
            return Http::withHeaders(array_merge(self::headers($key), ['Prefer' => 'return=minimal']))
                ->patch($baseUrl . '/rest/v1/' . $table . '?' . http_build_query($query), $body);
        } catch (\Throwable $e) {
            Log::warning('HandleChangeService: patch failed', ['table' => $table, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private static function creds(): array
    {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $key = (string) config('services.supabase_service_role_key');

        return ($baseUrl !== '' && $key !== '') ? [$baseUrl, $key] : [null, null];
    }

    private static function headers(string $key): array
    {
        return [
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ];
    }
}
