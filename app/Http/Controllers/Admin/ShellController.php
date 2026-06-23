<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin Shell — runs a single shell command inside the running container
 * (the Railway app instance this code is served from) and streams stdout +
 * stderr back to the browser live.
 *
 * This is remote code execution by design. It is gated by the `admin`
 * middleware, CSRF-protected, time-limited, and every command is written to
 * the dedicated `shell` audit log channel before it runs.
 *
 * It is intentionally NOT a true TTY: there is no pseudo-terminal, so
 * interactive programs (vim, top, an interactive `php artisan tinker`) will
 * not work. Use `railway ssh` for those. This covers one-shot commands:
 * artisan, composer, ls/cat, migrations, quick debugging.
 */
class ShellController extends Controller
{
    /** Hard ceiling on how long a single command may run (seconds). */
    private const TIMEOUT = 120;

    public function index()
    {
        return view('studio.admin.shell');
    }

    public function run(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'command' => 'required|string|max:4000',
        ]);

        $command = trim($validated['command']);
        $user = Auth::user();

        // Audit FIRST — before a single byte executes — so an attempt is
        // always recorded even if the command crashes the worker.
        Log::channel('shell')->info('admin shell command', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'name' => $user?->name,
            'ip' => $request->ip(),
            'command' => $command,
        ]);

        return response()->stream(function () use ($command) {
            $this->execStream($command);
        }, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no', // disable proxy buffering (nginx)
        ]);
    }

    /**
     * Run the command via `bash -lc` from the project root and echo output
     * chunk by chunk. stderr is merged into stdout so errors stream inline.
     */
    private function execStream(string $command): void
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open(
            ['bash', '-lc', $command],
            $descriptors,
            $pipes,
            base_path()
        );

        if (!is_resource($process)) {
            echo "[shell] failed to start a process (proc_open unavailable?)\n";
            return;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $start = time();

        while (true) {
            $status = proc_get_status($process);

            foreach ([$pipes[1], $pipes[2]] as $pipe) {
                $chunk = stream_get_contents($pipe);
                if ($chunk !== '' && $chunk !== false) {
                    echo $chunk;
                    $this->flush();
                }
            }

            if (!$status['running']) {
                echo "\n[shell] exited with code {$status['exitcode']}\n";
                $this->flush();
                break;
            }

            if (time() - $start >= self::TIMEOUT) {
                proc_terminate($process, 9);
                echo "\n[shell] killed after " . self::TIMEOUT . "s timeout\n";
                $this->flush();
                break;
            }

            usleep(50_000); // 50ms between polls
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }

    private function flush(): void
    {
        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
        }
        @flush();
    }
}
