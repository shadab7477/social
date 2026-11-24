# SOCIALPILOTX — OAUTH & SECURITY ARCHITECTURE
**Document ID:** `SPX-DOC-005`  
**Phase:** `Phase 0 — Authentication, OAuth 2.0 PKCE & Encryption Blueprint`  

---

## 1. High-Security OAuth Handshake Sequence Diagram

This diagram visualizes how SocialPilotX executes an authorization flow while neutralizing **CSRF**, **State Replay**, and **Workspace-Switching / Account Hijacking Attacks**.

```mermaid
sequenceDiagram
    autonumber
    actor User as Agency User
    participant Browser as User Browser
    participant App as Laravel SocialOAuthController
    participant DB as MySQL (oauth_transactions)
    participant Provider as Social Provider (e.g. Meta / Google / X)

    User->>Browser: Clicks "Connect Facebook"
    Browser->>App: GET /oauth/connect/facebook?workspace_id=5
    App->>App: 1. Validate User Authenticated & Active in Workspace 5
    App->>App: 2. Verify Workspace Subscription Limit (profiles < max_profiles)
    App->>App: 3. Generate Cryptographic Random State (128-bit)
    App->>App: 4. Generate PKCE code_verifier & code_challenge (if supported)
    App->>DB: 5. Store Transaction: [state, workspace_id: 5, user_id: 12, code_verifier, expires_at: NOW() + 15m]
    App->>Browser: 6. 302 Redirect to Provider Auth URL + state + client_id + scopes + challenge
    Browser->>Provider: Navigates to Provider Authorization Screen
    User->>Provider: Grants Permissions & Approves Account Access
    Provider->>Browser: 302 Redirect to App Callback: /oauth/callback/facebook?code=XYZ&state=ABC
    Browser->>App: GET /oauth/callback/facebook?code=XYZ&state=ABC
    App->>DB: 7. Lookup oauth_transactions WHERE state = 'ABC' AND is_consumed = 0
    alt State Not Found or Expired
        App->>Browser: 403 Forbidden: Invalid or Expired OAuth State
    else State Valid
        App->>DB: 8. Atomic Update: SET is_consumed = 1 (Prevents Replay)
        App->>App: 9. Verify Current Authenticated User == Transaction.user_id
        App->>App: 10. Verify User still belongs to Transaction.workspace_id
        App->>Provider: 11. POST Token Exchange: code + client_secret (+ code_verifier)
        Provider->>App: 200 OK: [access_token, refresh_token, expires_in]
        App->>App: 12. Encrypt Tokens with AES-256-GCM via Crypt::encryptString()
        App->>Provider: 13. Query Profiles: GET /me/accounts (or /userinfo)
        Provider->>App: Returns List of Discoverable Pages / Accounts
        App->>DB: 14. Save Temporary Discovered Profiles in Session Cache
        App->>Browser: 15. Redirect to Livewire Profile Selection Modal
        User->>Browser: Selects Target Pages for Client (e.g. "Sharma Restaurant")
        Browser->>App: POST Save Selected Profiles
        App->>DB: 16. Insert/Update social_connections & social_profiles
        App->>DB: 17. Insert audit_logs entry
        App->>Browser: Shows Success Notification
    end
```

---

## 2. Token Lifecycle & Automated Refresh Engine

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Scheduler (Every Hour)
    participant Job as RefreshSocialTokenJob
    participant DB as MySQL Database
    participant Provider as Social Provider API
    participant Notif as Notification Service

    Scheduler->>Job: Dispatch Refresh Check
    Job->>DB: Query social_connections WHERE status = 'connected' AND token_expires_at <= NOW() + 7 DAYS
    loop For Each Expiring Connection
        Job->>Job: Decrypt refresh_token
        alt Refresh Supported (Google, X, LinkedIn, Pinterest)
            Job->>Provider: POST /oauth/token (grant_type=refresh_token)
            alt Refresh Success
                Provider->>Job: 200 OK [new_access_token, new_refresh_token, expires_in]
                Job->>DB: Encrypt & UPDATE social_connections SET access_token = ..., token_expires_at = ...
                Job->>DB: INSERT INTO social_token_events (event_type: 'refreshed')
            else Refresh Rejected (User revoked access / Password changed)
                Provider->>Job: 400 Bad Request: invalid_grant
                Job->>DB: UPDATE social_connections SET status = 'reauthorization_required'
                Job->>DB: UPDATE social_profiles SET publishing_enabled = 0
                Job->>DB: INSERT INTO social_token_events (event_type: 'refresh_failed')
                Job->>Notif: Send Alert to Workspace Owner & Admins ("Facebook Connection Expired")
            end
        else Refresh Not Supported (Long-lived Meta Page Tokens)
            Job->>Provider: GET /debug_token to test validity
            alt Token Invalid
                Job->>DB: UPDATE social_connections SET status = 'expired'
                Job->>Notif: Send Reauthorization Alert
            end
        end
    end
```

---

## 3. Cryptographic Token Protection & Storage

1. **At-Rest Encryption**:
   - Access tokens and refresh tokens are **NEVER** stored in plain text.
   - Encrypted using Laravel's native encryption service: `Illuminate\Support\Facades\Crypt::encryptString($token)`.
   - Algorithm: **AES-256-GCM** with authenticated payload (prevents bit-flipping attacks).
   - Encryption key is derived from `APP_KEY` stored in server environment variable (outside web root).

2. **In-Memory & In-Transit Isolation**:
   - Tokens are **NEVER** passed into Livewire public properties or Blade template HTML.
   - Tokens are stripped from all JSON log outputs via Monolog processors.
   - API error responses sent to client contain sanitized error codes (`ERR_PROVIDER_UNAUTHORIZED`) rather than raw payload containing authorization headers.

---

## 4. Webhook Security & HMAC Verification

Incoming webhooks from Meta, X, Stripe, and Razorpay are processed through dedicated middleware:

| Provider | Signature Header | Verification Method |
|---|---|---|
| **Meta (Facebook/Instagram)** | `X-Hub-Signature-256` | `hash_equals('sha256=' . hash_hmac('sha256', $rawBody, $appSecret), $header)` |
| **Stripe** | `Stripe-Signature` | `\Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret)` |
| **Razorpay** | `X-Razorpay-Signature` | `hash_equals(hash_hmac('sha256', $rawBody, $webhookSecret), $header)` |
| **X (Twitter)** | `X-Twitter-Webhooks-Signature` | `hash_equals('sha256=' . base64_encode(hash_hmac('sha256', $rawBody, $consumerSecret, true)), $header)` |

- Requests failing signature verification are instantly aborted with `401 Unauthorized`.
- Verified payloads are saved into `webhook_events` with unique constraint `(provider, event_id)` to ensure **Idempotent Ingestion**.

