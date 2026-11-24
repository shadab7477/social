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
}
