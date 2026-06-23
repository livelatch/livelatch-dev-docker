<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Durable, off-box audit sink for the admin Shell page (/admin/shell).
 *
 * The container-local `shell` log channel (storage/logs/shell-*.log) is wiped
 * whenever Railway redeploys or recycles the container, so this mirrors every
 * command into Supabase (public.shell_audit_log) where it survives. Both sinks
 * are written before the command executes.
 *
 * Access uses the Supabase REST API with the service-role key (bypasses RLS),
 * mirroring BillingProfileService / EmailPreferenceService. The write is
 * best-effort and degrades to a no-op when Supabase is unavailable — auditing
 * must never block or fail an admin command (and the file channel still has it).
 */
class ShellAuditService
{
    private const TABLE = 'shell_audit_log';

    /**
     * Record a single shell command. Returns true on a successful insert.
     */
    public static function record(array $entry): bool
    {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $serviceKey = (string) config('services.supabase_service_role_key');

        if ($baseUrl === '' || $serviceKey === '') {
            // No Supabase configured — the file channel remains the record.
            return false;
        }

        $url = $baseUrl . '/rest/v1/' . self::TABLE;

        try {
            $response = Http::withHeaders([
                'apikey' => $serviceKey,
                'Authorization' => 'Bearer ' . $serviceKey,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal',
            ])->post($url, [
                'laravel_user_id' => $entry['user_id'] ?? null,
                'email' => $entry['email'] ?? null,
                'name' => $entry['name'] ?? null,
                'ip' => $entry['ip'] ?? null,
                'cwd' => $entry['cwd'] ?? null,
                'command' => $entry['command'] ?? '',
            ]);

            if (!$response->successful()) {
                Log::warning('ShellAuditService: Supabase insert failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('ShellAuditService: Supabase insert threw', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
