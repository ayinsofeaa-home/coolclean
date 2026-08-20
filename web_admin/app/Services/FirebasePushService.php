<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FirebasePushService
{
    public function sendToUser(?User $user, string $title, string $body): void
    {
        if (! $user?->FCM_TOKEN) {
            return;
        }

        $credentials = $this->credentials();
        if (! $credentials) {
            return;
        }

        try {
            $accessToken = $this->accessToken($credentials);
            Http::withToken($accessToken)
                ->post('https://fcm.googleapis.com/v1/projects/'.$credentials['project_id'].'/messages:send', [
                    'message' => [
                        'token' => $user->FCM_TOKEN,
                        'notification' => ['title' => $title, 'body' => $body],
                    ],
                ])->throw();
        } catch (Throwable $exception) {
            // Push failure must not roll back a booking or payment transaction.
            Log::warning('Firebase push failed', ['message' => $exception->getMessage()]);
        }
    }

    public function sendToActiveDrivers(string $title, string $body): void
    {
        User::where('ROLE', 'DRIVER')->where('STATUS', 'ACTIVE')->whereNotNull('FCM_TOKEN')
            ->each(fn (User $user) => $this->sendToUser($user, $title, $body));
    }

    private function credentials(): ?array
    {
        $path = config('services.firebase.service_account_path');
        if (! $path || ! is_readable($path)) {
            return null;
        }
        $credentials = json_decode(file_get_contents($path), true);
        return isset($credentials['client_email'], $credentials['private_key'], $credentials['project_id']) ? $credentials : null;
    }

    private function accessToken(array $credentials): string
    {
        $now = time();
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64Url(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));
        openssl_sign($header.'.'.$claims, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
        $assertion = $header.'.'.$claims.'.'.$this->base64Url($signature);

        return Http::asForm()->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ])->throw()->json('access_token');
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
