<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StreamScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Stream Schedule — a Pro LatchApp. Creators manage one-off / weekly streams;
 * the public profile shows the next 7 days and offers a subscribable .ics feed
 * that auto-updates when the schedule changes.
 */
class StreamScheduleController extends Controller
{
    /** Pro-gated management page. */
    public function manage(Request $request)
    {
        $user = $request->user();
        $isPro = $this->isPro($user);

        $events = $isPro ? StreamScheduleService::forUser($user->id) : [];

        return view('studio.stream-schedule', [
            'isPro'       => $isPro,
            'events'      => $events,
            'icsUrl'      => url('/@' . $user->littlelink_name . '/schedule.ics'),
            'webcal'      => 'webcal://' . preg_replace('#^https?://#', '', url('/@' . $user->littlelink_name . '/schedule.ics')),
            'handle'      => $user->littlelink_name,
            'rawgEnabled' => (bool) config('services.rawg.key'),
            'gamesUrl'    => route('streamSchedule.games'),
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->isPro($request->user())) {
            return $this->deny($request);
        }
        $data = $this->validateEvent($request);
        $ok = StreamScheduleService::create($request->user()->id, $data);

        return $this->respond($request, $ok, 'Stream added.');
    }

    public function update(Request $request, int $id)
    {
        if (!$this->isPro($request->user())) {
            return $this->deny($request);
        }
        $data = $this->validateEvent($request);
        $ok = StreamScheduleService::update($id, $request->user()->id, $data);

        return $this->respond($request, $ok, 'Stream updated.');
    }

    public function destroy(Request $request, int $id)
    {
        if (!$this->isPro($request->user())) {
            return $this->deny($request);
        }
        $ok = StreamScheduleService::delete($id, $request->user()->id);

        return $this->respond($request, $ok, 'Stream removed.');
    }

    /** Public subscribable feed: /@handle/schedule.ics */
    public function ics(string $handle)
    {
        $user = User::where('littlelink_name', $handle)->first();
        if (!$user) {
            abort(404);
        }

        $ics = StreamScheduleService::ics($user, StreamScheduleService::forUser($user->id));

        return response($ics, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="' . $handle . '-streams.ics"')
            ->header('Cache-Control', 'public, max-age=300');
    }

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'title'            => ['required', 'string', 'max:120'],
            'platform'         => ['nullable', 'string', 'max:32'],
            'url'              => ['nullable', 'url', 'max:400'],
            'kind'             => ['required', 'in:once,weekly'],
            'starts_at'        => ['nullable', 'string', 'max:40'],
            'ends_at'          => ['nullable', 'string', 'max:40'],
            'weekday'          => ['nullable', 'integer', 'between:0,6'],
            'start_time'       => ['nullable', 'string', 'max:5'],
            'end_time'         => ['nullable', 'string', 'max:5'],
            'timezone'         => ['nullable', 'string', 'max:64'],
            'reminder_minutes' => ['nullable', 'integer', 'between:0,10080'],
            'is_active'        => ['nullable', 'boolean'],
            'is_adult'         => ['nullable', 'boolean'],
            'tags'             => ['nullable', 'string', 'max:400'],
            'game_name'        => ['nullable', 'string', 'max:120'],
            'game_image'       => ['nullable', 'string', 'max:500'],
            'game_esrb'        => ['nullable', 'string', 'max:40'],
            'game_rawg_id'     => ['nullable', 'integer'],
        ]);
    }

    /** RAWG game search for the "show game" picker (Pro). */
    public function games(Request $request)
    {
        if (!$this->isPro($request->user())) {
            return response()->json([], 403);
        }

        return response()->json(StreamScheduleService::searchGames((string) $request->query('q', '')));
    }

    private function isPro($user): bool
    {
        if (!$user) {
            return false;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole('pro')) {
            return true;
        }

        return optional($user->billing)->plan_key === 'pro';
    }

    private function respond(Request $request, bool $ok, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => $ok, 'message' => $ok ? $message : 'Could not save right now.'], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $ok ? $message : 'Could not save right now.');
    }

    private function deny(Request $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => false, 'message' => 'Stream Schedule is a Pro feature.'], 403);
        }

        return back()->with('error', 'Stream Schedule is a Pro feature.');
    }
}
