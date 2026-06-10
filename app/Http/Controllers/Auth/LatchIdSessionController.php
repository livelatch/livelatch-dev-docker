<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;

class LatchIdSessionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supabase_user_id' => ['required', 'uuid'],
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'access_token' => ['required', 'string'],
        ]);

        $supabaseUser = $this->verifySupabaseUser($validated['access_token']);
        $supabaseEmail = strtolower((string) ($supabaseUser['email'] ?? ''));
        $requestEmail = strtolower($validated['email']);

        if (($supabaseUser['id'] ?? null) !== $validated['supabase_user_id'] || $supabaseEmail !== $requestEmail) {
            throw ValidationException::withMessages([
                'supabase_user_id' => 'Supabase session details did not match the authenticated user.',
            ]);
        }

        $user = DB::transaction(function () use ($validated, $requestEmail) {
            $user = User::where('supabase_user_id', $validated['supabase_user_id'])->first();

            if (!$user) {
                $user = User::where('email', $requestEmail)->first();

                if ($user) {
                    if (!empty($user->supabase_user_id) && $user->supabase_user_id !== $validated['supabase_user_id']) {
                        throw ValidationException::withMessages([
                            'email' => 'This email is already linked to a different LatchID account.',
                        ]);
                    }

                    if (empty($user->supabase_user_id)) {
                        $user->supabase_user_id = $validated['supabase_user_id'];
                        $user->save();
                    }
                }
            }

            if (!$user) {
                $displayName = trim((string) ($validated['name'] ?? ''));

                if ($displayName === '') {
                    $displayName = Str::before($requestEmail, '@');
                }

                $userData = [
                    'name' => $this->uniqueName($displayName),
                    'email' => $requestEmail,
                    'password' => Hash::make(Str::random(48)),
                    'supabase_user_id' => $validated['supabase_user_id'],
                    'littlelink_name' => $this->uniqueLittlelinkName($requestEmail),
                    'email_verified_at' => now(),
                ];

                if (!empty($validated['avatar_url'])) {
                    $userData[Schema::hasColumn('users', 'profile_image') ? 'profile_image' : 'image'] = $validated['avatar_url'];
                }

                $user = User::create($userData);

                Stripe::setApiKey(config('billing.stripe_secret'));

                $customer = Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'metadata' => [
                        'livelatch_user_id' => (string) $user->id,
                        'supabase_user_id' => (string) $user->supabase_user_id,
                    ],
                ]);

                $subscription = Subscription::create([
                    'customer' => $customer->id,
                    'items' => [[
                        'price' => config('billing.free_price_id'),
                    ]],
                    'metadata' => [
                        'livelatch_user_id' => (string) $user->id,
                        'supabase_user_id' => (string) $user->supabase_user_id,
                        'plan_key' => 'free',
                    ],
                ]);

                UserBilling::create([
                    'user_id' => $user->id,
                    'plan_key' => 'free',
                    'stripe_customer_id' => $customer->id,
                    'stripe_subscription_id' => $subscription->id,
                    'stripe_price_id' => config('billing.free_price_id'),
                    'stripe_status' => $subscription->status,
                    'current_period_end' => isset($subscription->current_period_end)
                        ? now()->setTimestamp($subscription->current_period_end)
                        : null,
                    'cancel_at_period_end' => $subscription->cancel_at_period_end ?? false,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => url('/dashboard'),
        ]);
    }

    private function verifySupabaseUser(string $accessToken): array
    {
        $supabaseUrl = rtrim((string) config('services.supabase.url'), '/');
        $anonKey = (string) config('services.supabase.anon_key');

        if ($supabaseUrl === '' || $anonKey === '') {
            Log::error('LatchID Supabase configuration is missing.');
            abort(500, 'LatchID authentication is not configured.');
        }

        $response = Http::withHeaders([
            'apikey' => $anonKey,
            'Authorization' => 'Bearer ' . $accessToken,
        ])->acceptJson()->get($supabaseUrl . '/auth/v1/user');

        if (!$response->successful()) {
            Log::warning('LatchID Supabase user verification failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw ValidationException::withMessages([
                'access_token' => 'Supabase session could not be verified.',
            ]);
        }

        return $response->json();
    }

    private function uniqueName(string $baseName): string
    {
        $baseName = trim($baseName) ?: 'LatchID User';
        $baseName = Str::limit($baseName, 240, '');
        $candidate = $baseName;
        $suffix = 2;

        while (User::where('name', $candidate)->exists()) {
            $candidate = Str::limit($baseName, 240, '') . ' ' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function uniqueLittlelinkName(string $email): string
    {
        $baseName = Str::before($email, '@');
        $baseName = Str::slug($baseName, '-');
        $baseName = trim($baseName, '-') ?: 'latchid';
        $baseName = Str::limit($baseName, 42, '');
        $candidate = $baseName;
        $suffix = 2;

        while (User::where('littlelink_name', $candidate)->exists()) {
            $candidate = Str::limit($baseName, 42, '') . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}