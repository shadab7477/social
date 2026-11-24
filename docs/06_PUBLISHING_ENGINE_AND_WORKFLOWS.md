# SOCIALPILOTX — PUBLISHING ENGINE & WORKFLOWS
**Document ID:** `SPX-DOC-006`  
**Phase:** `Phase 0 — Asynchronous Execution & Fault-Tolerant Publishing Pipeline`  

---

## 1. Publishing Engine Lifecycle & State Machine

Every post exists at two distinct levels:
1. **Parent Post (`posts`)**: The umbrella campaign container authored by the user.
2. **Post Variant (`post_variants`)**: The platform-tailored message bound to a specific social profile.

```mermaid
stateDiagram-v2
    [*] --> draft : User creates post
    draft --> pending_approval : Submitted for approval
    draft --> scheduled : Direct schedule (if permitted)
    
    pending_approval --> approved : Reviewer accepts
    pending_approval --> draft : Changes requested / Rejected
    
    approved --> scheduled : User assigns publish date/time
    
    scheduled --> queued : Dispatcher claims record at due time
    queued --> publishing : Redis worker picks up job
    
    publishing --> published : All variants succeed
    publishing --> partially_published : Some variants succeed, others fail
    publishing --> failed : All variants fail
    
    failed --> scheduled : User edits & reschedules
    failed --> queued : Manual retry triggered
    
    published --> [*]
    partially_published --> [*]
```

---

## 2. Asynchronous Queue Topology (Redis + Supervisor)

The queue pipeline is partitioned into distinct Redis queues to prevent resource starvation (e.g., heavy video uploads will never block high-priority publishing jobs):

```
+---------------------------------------------------------------------------------------+
|                                    REDIS QUEUE CLUSTER                                |
+------------------+------------------+------------------+------------------+-----------+
|    publishing    |      media       |    analytics     |     reports      |  default  |
| (Priority: HIGH) | (Priority: MED)  | (Priority: LOW)  | (Priority: LOW)  | (General) |
+------------------+------------------+------------------+------------------+-----------+
         |                  |                  |                  |               |
+--------v------------------v------------------v------------------v---------------v-----+
|                          SUPERVISOR MANAGED PHP WORKERS                               |
|  - Worker Group 1 (Publishing): php artisan queue:work --queue=publishing,default    |
|  - Worker Group 2 (Media/Transcode): php artisan queue:work --queue=media             |
|  - Worker Group 3 (Analytics/Reports): php artisan queue:work --queue=analytics,reports|
+---------------------------------------------------------------------------------------+
```

---

## 3. End-to-End Publishing Execution Sequence

```mermaid
sequenceDiagram
    autonumber
    participant Cron as Laravel Scheduler (Every Minute)
    participant Dispatcher as DispatchDuePostsJob
    participant DB as MySQL Database
    participant Redis as Redis Cache / Lock
    participant Queue as Redis Queue [publishing]
    participant Worker as PublishPostVariantJob
    participant Adapter as Social Platform Adapter (e.g. Meta / X)
    participant API as External Provider API

    Cron->>Dispatcher: Trigger scheduled run
    Dispatcher->>DB: SELECT * FROM post_variants WHERE status = 'scheduled' AND scheduled_at <= NOW()
    loop For each due variant
        Dispatcher->>Redis: Acquire Atomic Lock: Cache::lock('lock:variant:' . $id, 60)
        alt Lock Acquired
            Dispatcher->>DB: CREATE publishing_records (status: 'queued', idempotency_key: UUID)
            Dispatcher->>DB: UPDATE post_variants SET status = 'queued'
            Dispatcher->>Queue: Dispatch PublishPostVariantJob(variantId, idempotencyKey)
        else Lock Exists
            Dispatcher->>Dispatcher: Skip (Already in progress)
        end
    end

    Queue->>Worker: Worker pops PublishPostVariantJob
    Worker->>DB: Fetch PostVariant with Post, MediaAssets, SocialProfile, SocialConnection
    Worker->>Worker: 1. Validate Token not expired
    Worker->>Worker: 2. Validate Profile active & publishing_enabled
    Worker->>Worker: 3. Verify media accessible via public HTTPS URL
    Worker->>DB: UPDATE publishing_records SET status = 'processing', started_at = NOW()
    Worker->>DB: UPDATE post_variants SET status = 'publishing'
    Worker->>Adapter: publish(PublishingPayloadDTO)
    Adapter->>API: HTTP POST to Provider (e.g. Meta Graph API)
    
    alt HTTP 200/201 Success
        API->>Adapter: Returns provider_post_id & post_url
        Adapter->>Worker: Returns PublishingResultDTO(success: true)
        Worker->>DB: INSERT INTO publish_attempts (attempt_number, status_code: 200)
        Worker->>DB: UPDATE post_variants SET status = 'published', external_post_id = ..., published_at = NOW()
        Worker->>DB: UPDATE publishing_records SET status = 'completed', completed_at = NOW()
        Worker->>Worker: Check sibling variants on parent post
        Worker->>DB: UPDATE posts SET status = 'published' (or 'partially_published')
    else Transient HTTP Error (500/503/429 Rate Limit)
        API->>Adapter: Returns error response
        Adapter->>Worker: Returns PublishingResultDTO(success: false, retriable: true)
        Worker->>DB: INSERT INTO publish_attempts (attempt_number, error_details)
        alt retry_count < max_retries (3)
            Worker->>Worker: Calculate Exponential Backoff (60s * 2^retry_count)
            Worker->>Queue: Release job back to queue with delay
            Worker->>DB: UPDATE post_variants SET retry_count = retry_count + 1
        else Max Retries Exceeded
            Worker->>DB: UPDATE post_variants SET status = 'failed', error_message = ...
            Worker->>DB: UPDATE publishing_records SET status = 'failed'
            Worker->>Worker: Trigger Notification to Workspace
        end
    else Non-Retriable Error (401 Bad Token / 400 Bad Format / Permission Denied)
        Worker->>DB: INSERT INTO publish_attempts (attempt_number, error_details)
        Worker->>DB: UPDATE post_variants SET status = 'failed', error_message = ...
        Worker->>DB: UPDATE publishing_records SET status = 'failed'
        Worker->>Worker: Trigger Notification to Workspace
    end
```

---

## 4. Fault Tolerance, Idempotency & Partial Publishing

### Idempotency Keys
Before a job communicates with a remote API, an `idempotency_key` (UUID v4) is stamped on the `publishing_records` row. Platforms supporting native client request tokens (such as LinkedIn, Stripe, and X) receive this key in headers to guarantee that network timeouts do not cause duplicate publication.

### Uncertain Network Timeout Reconciliation
If a worker encounters a `ConnectionException` or cURL timeout (HTTP 0) after sending payload bytes:
1. **DO NOT blindly re-post**.
2. Mark variant as `status = 'publishing'` with `reconciliation_required = true`.
3. Dispatch `ReconcileUncertainPublishJob` with a 2-minute delay.
4. The reconciler calls the platform's query endpoint (e.g., `GET /{page-id}/feed` or `GET /2/users/{id}/tweets`) searching for the specific caption text or client timestamp.
5. If found, mark as `published` with the discovered provider post ID.
6. If confirmed absent, safely retry.

### Partial Publishing Handling
When a user targets 4 profiles (e.g., Facebook Page, Instagram, LinkedIn, X):
- If Facebook, Instagram, and LinkedIn publish successfully, but X fails due to rate limits:
  - Facebook Variant: `status = 'published'`
  - Instagram Variant: `status = 'published'`
  - LinkedIn Variant: `status = 'published'`
  - X Variant: `status = 'failed'`
  - Parent Post: `status = 'partially_published'`
- In the UI, the user sees clear badges. A **"Retry Failed Platform"** button enables the user to re-attempt publishing specifically for the failed X variant without re-publishing to Facebook, Instagram, or LinkedIn!

