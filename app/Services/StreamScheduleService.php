<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Stream schedules (a Pro LatchApp). Stored in Supabase
 * (public.stream_schedule_events) as one-off or weekly events. Powers the
 * public "next 7 days" block + a subscribable .ics feed at /@handle/schedule.ics.
 *
 * Recurring events are expanded into concrete UTC instants (not raw RRULE) over
 * a rolling window, so every subscriber's calendar / the on-page display shows
 * them correctly in the viewer's own timezone, DST-safe, with no VTIMEZONE.
 */
class StreamScheduleService
{
    private const TABLE = 'stream_schedule_events';
    private const ICS_WINDOW_DAYS = 70;

    /** All events for a user (active first not required; admin/owner view). */
    public static function forUser(int $userId): array
    {
        return self::get(['laravel_user_id' => 'eq.' . $userId, 'order' => 'created_at.asc', 'select' => '*']);
    }

    public static function create(int $userId, array $data): bool
    {
        $body = array_merge(self::clean($data), ['laravel_user_id' => $userId, 'sequence' => time(), 'updated_at' => now()->toIso8601String()]);
        $ok = (bool) (($r = self::send('post', '?select=id', $body)) && $r->successful());
        if ($ok) {
            self::forget($userId);
        }

        return $ok;
    }

    public static function update(int $id, int $userId, array $data): bool
    {
        $body = array_merge(self::clean($data), ['sequence' => time(), 'updated_at' => now()->toIso8601String()]);
        $ok = (bool) (($r = self::send('patch', '?id=eq.' . $id . '&laravel_user_id=eq.' . $userId, $body)) && $r->successful());
        if ($ok) {
            self::forget($userId);
        }

        return $ok;
    }

    public static function delete(int $id, int $userId): bool
    {
        $ok = (bool) (($r = self::send('delete', '?id=eq.' . $id . '&laravel_user_id=eq.' . $userId)) && $r->successful());
        if ($ok) {
            self::forget($userId);
        }

        return $ok;
    }

    /**
     * Concrete occurrences (UTC) between $from and $to, sorted.
     *
     * @return array<int, array{event:array,start:\DateTime,end:\DateTime,uid:string}>
     */
    public static function occurrences(array $events, \DateTime $from, \DateTime $to): array
    {
        $utc = new \DateTimeZone('UTC');
        $out = [];

        foreach ($events as $e) {
            if (!($e['is_active'] ?? true)) {
                continue;
            }

            if (($e['kind'] ?? 'once') === 'once') {
                if (empty($e['starts_at'])) {
                    continue;
                }
                $start = (new \DateTime($e['starts_at']))->setTimezone($utc);
                if ($start < $from || $start > $to) {
                    continue;
                }
                $end = !empty($e['ends_at']) ? (new \DateTime($e['ends_at']))->setTimezone($utc) : (clone $start)->modify('+1 hour');
                $out[] = ['event' => $e, 'start' => $start, 'end' => $end, 'uid' => 'sse' . ($e['id'] ?? '') . '@livelatch'];
                continue;
            }

            // weekly
            $tz = self::safeTz($e['timezone'] ?? 'UTC');
            $weekday = (int) ($e['weekday'] ?? 0); // 0=Sun..6=Sat (matches PHP/JS)
            [$sh, $sm] = self::hm($e['start_time'] ?? '00:00') ?? [0, 0];
            $endHm = self::hm($e['end_time'] ?? '');

            $cursor = (clone $from)->setTimezone($tz)->setTime(0, 0);
            $stop = (clone $to)->modify('+1 day');
            $guard = 0;
            while ($cursor <= $stop && $guard++ < 400) {
                if ((int) $cursor->format('w') === $weekday) {
                    $localStart = (clone $cursor)->setTime($sh, $sm);
                    $start = (clone $localStart)->setTimezone($utc);
                    if ($start >= $from && $start <= $to) {
                        if ($endHm) {
                            $localEnd = (clone $cursor)->setTime($endHm[0], $endHm[1]);
                            $end = (clone $localEnd)->setTimezone($utc);
                            if ($end <= $start) {
                                $end->modify('+1 day'); // overnight stream
                            }
                        } else {
                            $end = (clone $start)->modify('+1 hour');
                        }
                        $out[] = ['event' => $e, 'start' => $start, 'end' => $end, 'uid' => 'sse' . ($e['id'] ?? '') . '-' . $cursor->format('Ymd') . '@livelatch'];
                    }
                }
                $cursor->modify('+1 day');
            }
        }

        usort($out, fn ($a, $b) => $a['start'] <=> $b['start']);

        return $out;
    }

    /** Occurrences within the next $days days (for the public block). */
    public static function upcoming(array $events, int $days = 7): array
    {
        $from = new \DateTime('now', new \DateTimeZone('UTC'));
        $to = (clone $from)->modify('+' . $days . ' days');

        return self::occurrences($events, $from, $to);
    }

    /** Cached (60s) next-N-days occurrences for a user — the public-profile hot path. */
    public static function upcomingForUser(int $userId, int $days = 7): array
    {
        $events = Cache::remember('sse:events:' . $userId, 60, fn () => self::forUser($userId));

        return self::upcoming($events, $days);
    }

    private static function forget(int $userId): void
    {
        Cache::forget('sse:events:' . $userId);
    }

    /** Build the subscribable VCALENDAR feed for a user. */
    public static function ics(User $user, array $events): string
    {
        $now = gmdate('Ymd\THis\Z');
        $from = (new \DateTime('now', new \DateTimeZone('UTC')))->modify('-1 day');
        $to = (clone $from)->modify('+' . self::ICS_WINDOW_DAYS . ' days');
        $occ = self::occurrences($events, $from, $to);

        $handle = (string) $user->littlelink_name;
        $profileUrl = url('/@' . $handle);
        $removeUrl = url('/help/calendar');
        $calName = '@' . $handle . ' streams · Livelatch';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Livelatch//Stream Schedule//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . self::esc($calName),
            'NAME:' . self::esc($calName),
            'REFRESH-INTERVAL;VALUE=DURATION:PT1H',
            'X-PUBLISHED-TTL:PT1H',
        ];

        foreach ($occ as $o) {
            $e = $o['event'];
            $platform = !empty($e['platform']) ? ' · ' . ucfirst((string) $e['platform']) : '';
            $game = !empty($e['game_name']) ? ' · ' . $e['game_name'] : '';
            $summary = (!empty($e['is_adult']) ? '[18+] ' : '') . ($e['title'] ?? 'Stream') . $platform . $game;

            $desc = $summary;
            if (!empty($e['game_name'])) {
                $desc .= "\n" . 'Game: ' . $e['game_name'] . (!empty($e['game_esrb']) ? ' (ESRB: ' . $e['game_esrb'] . ')' : '');
            }
            if (!empty($e['tags'])) {
                $desc .= "\n" . 'Tags: ' . implode(', ', (array) $e['tags']);
            }
            $desc .= "\n\n" . 'Visit @' . $handle . "'s Livelatch to explore more: " . $profileUrl . "\n\n"
                . 'To remove this calendar, unsubscribe from it in your calendar app. How: ' . $removeUrl;

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $o['uid'];
            $lines[] = 'DTSTAMP:' . $now;
            $lines[] = 'DTSTART:' . $o['start']->format('Ymd\THis\Z');
            $lines[] = 'DTEND:' . $o['end']->format('Ymd\THis\Z');
            $lines[] = 'SUMMARY:' . self::esc($summary);
            $lines[] = 'DESCRIPTION:' . self::esc($desc);
            if (!empty($e['url'])) {
                $lines[] = 'URL:' . self::esc((string) $e['url']);
            }
            $lines[] = 'LAST-MODIFIED:' . $now;
            $lines[] = 'SEQUENCE:' . (int) ($e['sequence'] ?? 0);
            if (!empty($e['reminder_minutes'])) {
                $lines[] = 'BEGIN:VALARM';
                $lines[] = 'ACTION:DISPLAY';
                $lines[] = 'DESCRIPTION:' . self::esc(($e['title'] ?? 'Stream') . ' starts soon');
                $lines[] = 'TRIGGER:-PT' . max(0, (int) $e['reminder_minutes']) . 'M';
                $lines[] = 'END:VALARM';
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map([self::class, 'fold'], $lines)) . "\r\n";
    }

    /* --------------------------------------------------------------------- */

    /** Validate/normalise incoming event data to storable columns. */
    private static function clean(array $d): array
    {
        $kind = ($d['kind'] ?? 'once') === 'weekly' ? 'weekly' : 'once';
        $tzName = self::isValidTz((string) ($d['timezone'] ?? '')) ? (string) $d['timezone'] : 'UTC';
        $tz = self::safeTz($tzName);

        $out = [
            'title'            => mb_substr(trim((string) ($d['title'] ?? 'Stream')), 0, 120) ?: 'Stream',
            'platform'         => ($d['platform'] ?? '') !== '' ? mb_substr((string) $d['platform'], 0, 32) : null,
            'url'              => ($d['url'] ?? '') !== '' ? mb_substr((string) $d['url'], 0, 400) : null,
            'kind'             => $kind,
            'reminder_minutes' => isset($d['reminder_minutes']) && $d['reminder_minutes'] !== '' ? max(0, min(10080, (int) $d['reminder_minutes'])) : null,
            'is_active'        => array_key_exists('is_active', $d) ? (bool) $d['is_active'] : true,
            'is_adult'         => array_key_exists('is_adult', $d) ? (bool) $d['is_adult'] : false,
            'tags'             => self::cleanTags($d['tags'] ?? []),
            'game_name'        => ($d['game_name'] ?? '') !== '' ? mb_substr((string) $d['game_name'], 0, 120) : null,
            'game_image'       => ($d['game_image'] ?? '') !== '' ? mb_substr((string) $d['game_image'], 0, 500) : null,
            'game_esrb'        => ($d['game_esrb'] ?? '') !== '' ? mb_substr((string) $d['game_esrb'], 0, 40) : null,
            'game_rawg_id'     => isset($d['game_rawg_id']) && $d['game_rawg_id'] !== '' ? (int) $d['game_rawg_id'] : null,
            'timezone'         => $tzName,
            'starts_at'        => null, 'ends_at' => null, 'weekday' => null, 'start_time' => null, 'end_time' => null,
        ];

        if ($kind === 'once') {
            // Times arrive as the creator's wall-clock ("2026-07-01T19:00"); anchor
            // them to the creator's timezone, not the server's, before storing.
            $out['starts_at'] = !empty($d['starts_at']) ? (new \DateTime((string) $d['starts_at'], $tz))->format(\DateTime::ATOM) : null;
            $out['ends_at']   = !empty($d['ends_at']) ? (new \DateTime((string) $d['ends_at'], $tz))->format(\DateTime::ATOM) : null;
        } else {
            $out['weekday']    = max(0, min(6, (int) ($d['weekday'] ?? 0)));
            $out['start_time'] = self::hm($d['start_time'] ?? '') ? sprintf('%02d:%02d', ...self::hm($d['start_time'])) : '19:00';
            $out['end_time']   = self::hm($d['end_time'] ?? '') ? sprintf('%02d:%02d', ...self::hm($d['end_time'])) : null;
        }

        return $out;
    }

    /** Up to 8 short custom tags from a comma string or array. */
    private static function cleanTags($tags): array
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }
        if (!is_array($tags)) {
            return [];
        }
        $out = [];
        foreach ($tags as $t) {
            $t = trim((string) $t);
            if ($t !== '') {
                $out[] = mb_substr($t, 0, 40);
            }
        }

        return array_values(array_slice(array_unique($out), 0, 8));
    }

    /**
     * Search the RAWG game database for the "show game" picker.
     *
     * @return array<int, array{id:?int,name:string,image:?string,esrb:?string,released:?string}>
     */
    public static function searchGames(string $q): array
    {
        $q = trim($q);
        $key = (string) config('services.rawg.key');
        if ($q === '' || $key === '') {
            return [];
        }

        try {
            $r = Http::timeout(6)->get('https://api.rawg.io/api/games', [
                'key'       => $key,
                'search'    => $q,
                'page_size' => 8,
            ]);
            if (!$r->successful()) {
                return [];
            }

            $out = [];
            foreach ($r->json('results') ?? [] as $g) {
                $out[] = [
                    'id'       => $g['id'] ?? null,
                    'name'     => (string) ($g['name'] ?? ''),
                    'image'    => $g['background_image'] ?? null,
                    'esrb'     => $g['esrb_rating']['name'] ?? null,
                    'released' => $g['released'] ?? null,
                ];
            }

            return $out;
        } catch (\Throwable $e) {
            Log::warning('StreamScheduleService: RAWG search failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private static function hm($value): ?array
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', trim((string) $value), $m)) {
            return null;
        }

        return [max(0, min(23, (int) $m[1])), max(0, min(59, (int) $m[2]))];
    }

    private static function isValidTz(string $tz): bool
    {
        return $tz !== '' && in_array($tz, timezone_identifiers_list(), true);
    }

    private static function safeTz(string $tz): \DateTimeZone
    {
        try {
            return new \DateTimeZone(self::isValidTz($tz) ? $tz : 'UTC');
        } catch (\Throwable $e) {
            return new \DateTimeZone('UTC');
        }
    }

    /** Escape a value for an ICS text field. */
    public static function esc(string $s): string
    {
        $s = str_replace(['\\', ';', ','], ['\\\\', '\\;', '\\,'], $s);

        return str_replace(["\r\n", "\n", "\r"], '\\n', $s);
    }

    /** Fold a content line to <=75 octets per RFC 5545. */
    public static function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }
        $out = '';
        while (strlen($line) > 75) {
            $out .= substr($line, 0, 75) . "\r\n ";
            $line = substr($line, 75);
        }

        return $out . $line;
    }

    /* ---- Supabase REST ---- */

    private static function get(array $query): array
    {
        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return [];
        }
        try {
            $r = Http::withHeaders(self::headers($key))->get($baseUrl . '/rest/v1/' . self::TABLE, $query);

            return $r->successful() ? ($r->json() ?: []) : [];
        } catch (\Throwable $e) {
            Log::warning('StreamScheduleService: get failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private static function send(string $method, string $qs, array $body = null)
    {
        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return null;
        }
        try {
            $req = Http::withHeaders(array_merge(self::headers($key), ['Prefer' => 'return=minimal']));
            $url = $baseUrl . '/rest/v1/' . self::TABLE . $qs;

            return match ($method) {
                'post'   => $req->post($url, $body ?? []),
                'patch'  => $req->patch($url, $body ?? []),
                'delete' => $req->delete($url),
                default  => null,
            };
        } catch (\Throwable $e) {
            Log::warning('StreamScheduleService: ' . $method . ' failed', ['error' => $e->getMessage()]);

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
        return ['apikey' => $key, 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'];
    }
}
