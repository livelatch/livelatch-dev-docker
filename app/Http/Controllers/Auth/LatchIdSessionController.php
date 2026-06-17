<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
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
            'redirect_to' => ['nullable', 'string', 'max:2048'],
            'oauth_provider' => ['nullable', 'string', 'in:google,discord,youtube'],
            'provider_token' => ['nullable', 'string'],
            'provider_refresh_token' => ['nullable', 'string'],
            'provider_expires_at' => ['nullable', 'integer'],
            'provider_scopes' => ['nullable'],
        ]);

        $supabaseUser = $this->verifySupabaseUser($validated['access_token']);
        $supabaseEmail = strtolower((string) ($supabaseUser['email'] ?? ''));
        $requestEmail = strtolower($validated['email']);

        if (($supabaseUser['id'] ?? null) !== $validated['supabase_user_id'] || $supabaseEmail !== $requestEmail) {
            throw ValidationException::withMessages([
                'supabase_user_id' => 'Supabase session details did not match the authenticated user.',
            ]);
        }

        $redirectTo = $this->resolveRedirectTo($request, $validated['redirect_to'] ?? null);
        $currentUser = Auth::user();
        $isNewUser = false;

        try {
        $user = DB::transaction(function () use ($validated, $requestEmail, $currentUser, $supabaseUser, &$isNewUser) {
            $user = User::where('supabase_user_id', $validated['supabase_user_id'])->first();

            if ($currentUser) {
                if ($user && $user->isNot($currentUser)) {
                    throw ValidationException::withMessages([
                        'supabase_user_id' => 'This LatchID account is already linked to another Livelatch user.',
                    ]);
                }

                $emailOwner = User::where('email', $requestEmail)
                    ->where('id', '!=', $currentUser->id)
                    ->first();

                if ($emailOwner) {
                    throw ValidationException::withMessages([
                        'email' => 'This LatchID email is already used by another Livelatch account.',
                    ]);
                }

                if (!empty($currentUser->supabase_user_id) && $currentUser->supabase_user_id !== $validated['supabase_user_id']) {
                    throw ValidationException::withMessages([
                        'supabase_user_id' => 'This Livelatch account is already linked to a different LatchID account.',
                    ]);
                }

                if (empty($currentUser->supabase_user_id)) {
                    $currentUser->supabase_user_id = $validated['supabase_user_id'];
                    $currentUser->save();
                }

                $this->syncSocialAccounts($currentUser, $supabaseUser, $validated);

                return $currentUser;
            }

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
                $isNewUser = true;
            }

            $this->syncSocialAccounts($user, $supabaseUser, $validated);

            return $user;
        });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // A concurrent double-submit can try to create the same LatchID user
            // twice (unique supabase_user_id). Recover the existing record rather
            // than returning a 500; otherwise fail cleanly with a retry message.
            $user = User::where('supabase_user_id', $validated['supabase_user_id'])->first();

            if (!$user) {
                Log::error('LatchID session persistence failed.', ['error' => $e->getMessage()]);

                return response()->json([
                    'success' => false,
                    'message' => 'We could not finish signing you in. Please try again.',
                ], 503);
            }
        }

        // Stripe provisioning is best-effort and runs outside the database
        // transaction so a transient Stripe/network failure can no longer roll
        // back signup or 500 the request. Missing billing is reconciled by the
        // billing:backfill-stripe command.
        if ($isNewUser) {
            $this->provisionBilling($user);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => $redirectTo,
        ]);
    }

    private function provisionBilling(User $user): void
    {
        if (UserBilling::where('user_id', $user->id)->exists()) {
            return;
        }

        try {
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
        } catch (\Throwable $e) {
            Log::warning('LatchID signup: Stripe billing provisioning failed; user can be backfilled later.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveRedirectTo(Request $request, ?string $redirectTo): string
    {
        $fallback = url('/dashboard');
        $redirectTo = trim((string) $redirectTo);

        if ($redirectTo === '') {
            return $fallback;
        }

        $decoded = urldecode($redirectTo);

        if (
            !Str::startsWith($decoded, '/') ||
            Str::startsWith($decoded, '//') ||
            Str::contains($decoded, ["\r", "\n", '\\'])
        ) {
            return $fallback;
        }

        return url($decoded);
    }

    private function syncSocialAccounts(User $user, array $supabaseUser, array $validated): void
    {
        foreach (($supabaseUser['identities'] ?? []) as $identity) {
            $provider = (string) ($identity['provider'] ?? '');
            $identityData = $identity['identity_data'] ?? [];
            $providerId = (string) (
                $identity['provider_id']
                ?? $identityData['provider_id']
                ?? $identityData['sub']
                ?? $identityData['id']
                ?? $identity['identity_id']
                ?? ''
            );

            if ($provider === '' || $providerId === '') {
                continue;
            }

            $account = SocialAccount::where([
                'provider_name' => $provider,
                'provider_id' => $providerId,
            ])->first();

            if ($account && (int) $account->user_id !== (int) $user->id) {
                throw ValidationException::withMessages([
                    'supabase_user_id' => 'This provider identity is already linked to another Livelatch account.',
                ]);
            }

            SocialAccount::firstOrCreate([
                'provider_name' => $provider,
                'provider_id' => $providerId,
            ], [
                'user_id' => $user->id,
            ]);
        }

        $this->syncRequestedProviderConnection($user, $supabaseUser, $validated);
    }

    private function syncRequestedProviderConnection(User $user, array $supabaseUser, array $validated): void
    {
        $connectionProvider = $this->normalizeConnectionProvider((string) ($validated['oauth_provider'] ?? ''));

        if ($connectionProvider === '') {
            return;
        }

        $identity = $this->primaryIdentity($supabaseUser);
        $providerId = $this->providerIdFromIdentity($identity);

        if ($providerId === '') {
            return;
        }

        $accountProviderId = $connectionProvider === 'youtube'
            ? 'youtube:' . $providerId
            : $providerId;

        $account = SocialAccount::where([
            'provider_name' => $connectionProvider,
            'provider_id' => $accountProviderId,
        ])->first();

        if ($account && (int) $account->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'supabase_user_id' => 'This provider connection is already linked to another Livelatch account.',
            ]);
        }

        $values = [
            'user_id' => $user->id,
            'metadata' => [
                'identity_provider' => (string) ($identity['provider'] ?? ''),
                'supabase_user_id' => (string) ($supabaseUser['id'] ?? ''),
            ],
            'connected_at' => now(),
        ];

        if (!empty($validated['provider_token'])) {
            $values['access_token'] = $validated['provider_token'];
        }

        if (!empty($validated['provider_refresh_token'])) {
            $values['refresh_token'] = $validated['provider_refresh_token'];
        }

        if (!empty($validated['provider_expires_at'])) {
            $values['token_expires_at'] = now()->setTimestamp((int) $validated['provider_expires_at']);
        }

        $scopes = $this->normalizeScopes($validated['provider_scopes'] ?? null);
        if (!empty($scopes)) {
            $values['scopes'] = $scopes;
        }

        SocialAccount::updateOrCreate([
            'provider_name' => $connectionProvider,
            'provider_id' => $accountProviderId,
        ], $values);
    }

    private function normalizeConnectionProvider(string $provider): string
    {
        return $provider;
    }

    private function primaryIdentity(array $supabaseUser): array
    {
        $identities = $supabaseUser['identities'] ?? [];

        if (is_array($identities) && isset($identities[0]) && is_array($identities[0])) {
            return $identities[0];
        }

        return [];
    }

    private function providerIdFromIdentity(array $identity): string
    {
        $identityData = $identity['identity_data'] ?? [];

        return (string) (
            $identity['provider_id']
            ?? $identityData['provider_id']
            ?? $identityData['sub']
            ?? $identityData['id']
            ?? $identity['identity_id']
            ?? ''
        );
    }

    private function normalizeScopes(mixed $scopes): array
    {
        if (is_array($scopes)) {
            return array_values(array_filter(array_map('strval', $scopes)));
        }

        $scopes = trim((string) $scopes);

        if ($scopes === '') {
            return [];
        }

        return array_values(array_filter(preg_split('/\s+/', $scopes) ?: []));
    }

    private function verifySupabaseUser(string $accessToken): array
    {
        $supabaseUrl = rtrim((string) config('services.supabase.url'), '/');
        $anonKey = (string) config('services.supabase.anon_key');

        if ($supabaseUrl === '' || $anonKey === '') {
            Log::error('LatchID Supabase configuration is missing.');
            abort(500, 'LatchID authentication is not configured.');
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $anonKey,
                'Authorization' => 'Bearer ' . $accessToken,
            ])->acceptJson()->timeout(8)->retry(2, 250)->get($supabaseUrl . '/auth/v1/user');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('LatchID Supabase verification could not connect.', ['error' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'access_token' => 'We could not reach LatchID to verify your session. Please try again.',
            ]);
        }

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
