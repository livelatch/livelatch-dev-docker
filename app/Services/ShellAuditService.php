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
     * Read the most recent audit entries (newest first) for the admin UI.
     * Returns [] when Supabase isn't configured or the request fails.
     */
    public static function recent(int $limit = 100): array
    {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $serviceKey = (string) config('services.supabase_service_role_key');

        if ($baseUrl === '' || $serviceKey === '') {
            return [];
        }

        $limit = max(1, min($limit, 500));
        $url = $baseUrl . '/rest/v1/' . self::TABLE
            . '?select=created_at,laravel_user_id,email,name,ip,cwd,command'
            . '&order=created_at.desc&limit=' . $limit;

        try {
            $response = Http::withHeaders([
                'apikey' => $serviceKey,
                'Authorization' => 'Bearer ' . $serviceKey,
            ])->acceptJson()->timeout(8)->get($url);

            if (!$response->successful()) {
                Log::warning('ShellAuditService: Supabase read failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('ShellAuditService: Supabase read threw', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

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
