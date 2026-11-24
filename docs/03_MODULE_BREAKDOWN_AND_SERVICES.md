# SOCIALPILOTX — MODULE BREAKDOWN & SERVICE INVENTORY
**Document ID:** `SPX-DOC-003`  
**Phase:** `Phase 0 — Core Architecture & Domain Boundaries`  

---

## 1. Module Architectural Breakdown

The system is decomposed into 16 coherent, loosely coupled domains. Each domain is governed by dedicated **Services**, **Repositories/Query Builders**, **Policies**, and **Livewire / Controller Entrypoints**.

```
+----------------------------------------------------------------------------------------------------+
|                                      SOCIALPILOTX CORE DOMAINS                                     |
+--------------------+---------------------+--------------------+------------------------------------+
| 01. TENANCY & AUTH | 02. CLIENT MGMT     | 03. TEAM & RBAC    | 04. SOCIAL CONNECTIONS (OAUTH)     |
+--------------------+---------------------+--------------------+------------------------------------+
| 05. MEDIA LIBRARY  | 06. COMPOSER        | 07. CALENDAR       | 08. PUBLISHING ENGINE (REDIS)      |
+--------------------+---------------------+--------------------+------------------------------------+
| 09. APPROVALS      | 10. SOCIAL INBOX    | 11. ANALYTICS      | 12. CLIENT REPORTS (PDF/CSV)       |
+--------------------+---------------------+--------------------+------------------------------------+
| 13. AI ASSISTANT   | 14. AUTOMATIONS     | 15. BILLING/PLANS  | 16. SUPER ADMIN & AUDIT            |
+--------------------+---------------------+--------------------+------------------------------------+
```

---

## 2. In-Depth Module Specifications & Laravel Concept Walkthrough

### Module 01: Multi-Tenancy & Workspace Isolation
- **Why Needed**: Agencies must operate separate workspaces or manage distinct customer pools without cross-tenant data leaks.
- **Internal Mechanics**:
  - Request hits `IdentifyWorkspace` middleware.
  - Workspace is resolved via session (`active_workspace_id`) or user membership.
  - Global query scope `TenantScope` automatically injects `where('workspace_id', $currentId)` on all multi-tenant Eloquent models (`Post`, `SocialConnection`, `MediaAsset`, etc.).
  - Policies ensure that unauthorized users cannot read or mutate models belonging to another tenant.
- **Key Classes**:
  - Middleware: `App\Http\Middleware\IdentifyWorkspace`
  - Scope: `App\Models\Scopes\TenantScope`
  - Service: `App\Services\Tenancy\WorkspaceManagerService`

### Module 02: Client Management (Agency Sub-Accounts)
- **Why Needed**: Marketing agencies manage multiple independent external businesses (e.g., Sharma Restaurant, Fashion Store). Social profiles and posts must be bound to specific clients.
- **Internal Mechanics**:
  - Clients are created inside a workspace.
  - Social profiles are assigned to clients.
  - Client users (`client_viewer` role) are mapped via `client_members` table and restricted solely to that client's content.
- **Key Classes**:
  - Livewire: `App\Livewire\Clients\ClientManager`
  - Service: `App\Services\Clients\ClientService`
  - Policy: `App\Policies\ClientPolicy`

### Module 03: Team & RBAC (Role-Based Access Control)
- **Roles**:
  1. `owner`: Full control, billing, workspace deletion.
  2. `admin`: Member invitations, connection management, client creation.
  3. `manager`: Post approval, scheduling, publishing overrides.
  4. `editor`: Drafting, media uploads, requesting approval.
  5. `client_viewer`: Restricted portal access to review and approve assigned client posts.
- **Internal Mechanics**:
  - Handled via `WorkspacePolicy` and `PostPolicy`.
  - Livewire directives `@can('publish', $post)` dynamically disable forbidden actions in UI.

### Module 04: Social Accounts & OAuth Management
- **Why Needed**: The foundational module. Handles OAuth handshake, PKCE validation, encrypted token persistence, profile discovery, and token health monitoring.
- **Internal Mechanics**:
  - High-security one-time `oauth_transactions` table stores random state + workspace ID + user ID.
  - Callback verifies state, decrypts tokens, calls discovery API (e.g. Meta `/me/accounts`), presents Page selection modal, and stores selected pages into `social_profiles`.
- **Key Classes**:
  - Controller: `App\Http\Controllers\SocialOAuthController`
  - Service: `App\Services\Social\Registry\SocialProviderManager`
  - Livewire: `App\Livewire\SocialAccounts\AccountManager`, `ProfileSelectionModal`

### Module 05: Centralized Media Library
- **Why Needed**: Stores assets (images, videos, PDFs) on Cloudflare R2 / S3. Supports folders, tags, dimensions/duration inspection, and post reference tracking.
- **Internal Mechanics**:
  - Uses Laravel Storage disk configured for S3-compatible R2.
  - Prevents deleting media assets currently bound to upcoming scheduled posts.
- **Key Classes**:
  - Livewire: `App\Livewire\MediaLibrary\MediaManager`
  - Service: `App\Services\Media\MediaStorageService`, `MediaValidationService`

### Module 06: Multi-Platform Content Composer & Variants
- **Why Needed**: Social platforms have drastically different constraints (e.g., X: 280 chars; Instagram: mandatory media; Pinterest: destination URL + board).
- **Internal Mechanics**:
  - Master Livewire component `PostComposer` maintains parent post state and child variant state.
  - Real-time Alpine.js and Livewire validation against `PlatformCapabilityRegistry`.
  - Split tabs for each selected social profile allowing individual captions, media ordering, and settings.

### Module 07: FullCalendar Content Calendar
- **Why Needed**: Interactive visual scheduling calendar (Month, Week, Day, List).
- **Internal Mechanics**:
  - FullCalendar JavaScript library initialized via Alpine.js.
  - Events loaded asynchronously via Livewire method `getEvents($start, $end)`.
  - Drag-and-drop event moving triggers Livewire `reschedulePost($postId, $newUtcDate)` which automatically cancels old queued dispatch and recalculates schedule.

### Module 08: High-Throughput Publishing Engine
- **Why Needed**: Robust, fail-safe publishing without browser dependency.
- **Internal Mechanics**:
  - Laravel Scheduler runs `DispatchDuePostsJob` every minute.
  - Queries `post_variants` where `status = 'scheduled' AND scheduled_at <= NOW()`.
  - Acquires atomic Redis lock on `publishing_records` row to prevent duplicate dispatch.
  - Dispatches `PublishPostVariantJob` to the `publishing` Redis queue.
  - Worker executes platform adapter, records attempt history in `publish_attempts`, updates variant status, and resolves parent post status.

### Module 09: Multi-Tier Approval Workflow
- **Why Needed**: Agency editors create posts, internal managers review, and client representatives approve before posts can be scheduled to publish.
- **Internal Mechanics**:
  - State machine: `draft -> pending_approval -> approved -> scheduled`.
  - Edits made after approval automatically bump the post `revision` number and reset approval state if substantive text/media changed.

### Module 10: Social Inbox & Unified Conversations
- **Why Needed**: Centralized management of post comments, replies, and supported messages.
- **Internal Mechanics**:
  - Ingestion via platform webhooks into `webhook_events`, dispatched to `ProcessWebhookEventJob`.
  - Normalizes conversations and messages.
  - Outbound replies execute via platform adapter and store author information.

### Module 11: Analytics & Time-Series Engine
- **Why Needed**: Aggregates reach, impressions, engagement, likes, comments, and follower metrics.
- **Internal Mechanics**:
  - Scheduled background worker `SyncSocialProfileAnalyticsJob` polls provider metrics (respecting rate limits).
  - Normalizes metrics into `analytics_snapshots`.
  - Visualized in frontend via Chart.js components.

### Module 12: Automated & Branded Client Reports
- **Why Needed**: Agencies deliver professional weekly/monthly PDF/CSV reports to clients with agency branding.
- **Internal Mechanics**:
  - Asynchronously compiled via `GenerateClientReportJob` using headless HTML-to-PDF (`dompdf` or `browsershot`).
  - Saved to storage and emailed to designated recipients.

### Module 13: AI Content Assistant
- **Why Needed**: AI-assisted caption creation, rewriting, hashtag generation, tone adjustments.
- **Internal Mechanics**:
  - Connects to OpenAI API through `OpenAIAssistantService`.
  - Usage tracked per workspace in `usage_records` to enforce plan limits.
  - Output is strictly populated in composer fields for user review and approval.

### Module 14: Automation Engine
- **Why Needed**: RSS auto-posting, reminder alerts, failed post notifications.
- **Internal Mechanics**:
  - Worker evaluates `automation_rules` and records run results in `automation_runs`.

### Module 15: Billing & Subscription Management
- **Why Needed**: Commercial SaaS monetized via tiers (Starter, Professional, Agency, Enterprise).
- **Internal Mechanics**:
  - Native dual gateway support: **Razorpay** (India UPI/Cards/Netbanking) and **Stripe** (International).
  - Enforces hard limits on active profiles, members, and monthly post volume via `SubscriptionLimitEnforcer`.

### Module 16: Super Admin & Security Audit
- **Why Needed**: Platform governance, tenant monitoring, feature flag toggling, and security audit log inspection.

