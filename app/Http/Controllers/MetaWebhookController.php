<?php

namespace App\Http\Controllers;

use App\Models\WebhookEvent;
use Illuminate\Http\Request;

class MetaWebhookController extends Controller
{
    /**
     * Handle Meta Webhook Hub Challenge Verification (GET).
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        $expectedToken = config('services.facebook.verify_token') 
            ?: (env('META_WEBHOOK_VERIFY_TOKEN') ?: 'socialpilotx_meta_secret_token');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden: Invalid verification token', 403);
    }

    /**
     * Ingest incoming Meta Webhook notifications (POST).
     */
    public function handle(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');
        $appSecret = config('services.facebook.client_secret') ?: env('META_APP_SECRET');

        // Cryptographic HMAC SHA-256 Verification
        if (!empty($appSecret) && !empty($signature)) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);
            if (!hash_equals($expectedSignature, (string)$signature)) {
                return response('Invalid signature', 401);
            }
        }

        $payload = $request->json()->all();
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $index => $entry) {
            $entryId = (string)($entry['id'] ?? 'meta_entry_' . time() . '_' . $index);
            $eventTime = (string)($entry['time'] ?? time());
            $uniqueEventId = "meta_{$entryId}_{$eventTime}";

            WebhookEvent::firstOrCreate(
                [
                    'provider' => 'meta',
                    'event_id' => $uniqueEventId,
                ],
                [
                    'event_name' => $payload['object'] ?? 'page',
                    'payload' => $entry,
                    'headers' => [
                        'x-hub-signature' => $signature,
                        'content-type' => $request->header('Content-Type'),
                    ],
                    'status' => 'pending',
                ]
            );
        }

        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Handle Meta User Data Deletion Callback (POST).
     * Decodes Meta's signed_request and returns confirmation_code + status URL.
     */
    public function dataDeletion(Request $request)
    {
        $signedRequest = $request->input('signed_request');
        $confirmationCode = 'DEL_' . strtoupper(\Illuminate\Support\Str::random(12));

        if (!empty($signedRequest)) {
            $data = $this->parseSignedRequest($signedRequest);
            $userId = $data['user_id'] ?? null;

            if ($userId) {
                // Delete or revoke any connected social connections/profiles for this Meta User ID
                \App\Models\SocialConnection::where('account_id', (string)$userId)
                    ->orWhere('account_id', 'like', "%{$userId}%")
                    ->delete();
            }
        }

        // Cache confirmation code for status page lookup (for 90 days)
        \Illuminate\Support\Facades\Cache::put(
            "meta_deletion_{$confirmationCode}",
            [
                'status' => 'completed',
                'requested_at' => now()->toIso8601String(),
                'code' => $confirmationCode,
            ],
            now()->addDays(90)
        );

        $statusUrl = url("/deletion-status?code={$confirmationCode}");

        return response()->json([
            'url' => $statusUrl,
            'confirmation_code' => $confirmationCode,
        ]);
    }

    /**
     * Decode and verify Meta signed_request.
     */
    protected function parseSignedRequest(string $signedRequest): ?array
    {
        $secret = config('services.facebook.client_secret') ?: env('META_APP_SECRET');
        $parts = explode('.', $signedRequest, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$encodedSig, $encodedPayload] = $parts;

        $sig = $this->base64UrlDecode($encodedSig);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (!$payload || !isset($payload['algorithm']) || $payload['algorithm'] !== 'HMAC-SHA256') {
            return null;
        }

        if (!empty($secret)) {
            $expectedSig = hash_hmac('sha256', $encodedPayload, $secret, true);
            if (!hash_equals($expectedSig, $sig)) {
                return null;
            }
        }

        return $payload;
    }

    protected function base64UrlDecode(string $input): string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
