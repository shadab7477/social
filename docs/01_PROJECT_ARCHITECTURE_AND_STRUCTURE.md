# SOCIALPILOTX — PROJECT ARCHITECTURE & STRUCTURE SPECIFICATION
**Document ID:** `SPX-DOC-001`  
**Phase:** `Phase 0 — System Architecture & Foundational Blueprint`  
**Target Stack:** Laravel 11 / PHP 8.2+, Livewire 3, Alpine.js, Bootstrap 5, MySQL 8, Redis, Supervisor.

---

## 1. Executive Architecture Overview

**SocialPilotX** is a commercial, multi-tenant B2B & B2C Social Media Management Platform architected as a **Modular Monolith** in PHP / Laravel. It provides digital agencies, enterprises, and local businesses with the ability to manage multiple clients, connect heterogeneous social network APIs, compose customized platform variants, schedule and publish content through high-throughput queues, orchestrate multi-tiered approval workflows, gather unified analytics, and conduct social inbox interactions.

```
+-----------------------------------------------------------------------------------+
|                                  CLIENT LAYER                                     |
|    Browser: Bootstrap 5 + Livewire 3 (Morph DOM) + Alpine.js + FullCalendar/Chart.js|
+-----------------------------------------+-----------------------------------------+
                                          | HTTPS / WebSockets / Livewire AJAX
+-----------------------------------------v-----------------------------------------+
|                                 APPLICATION LAYER                                 |
|  +-----------------------------------------------------------------------------+  |
|  | Multi-Tenancy Middleware (Workspace Identification & Tenant Scoping)       |  |
|  +-----------------------------------------------------------------------------+  |
|  | Web Controllers (Auth, Webhooks, OAuth Callbacks, File Streaming)           |  |
|  | Livewire Components (Composer, Calendar, Inbox, Analytics, Connections)     |  |
|  +-----------------------------------------------------------------------------+  |
|  | Domain Services Layer:                                                      |  |
|  |  - Social Platform Adapters (Meta Graph API, LinkedIn, X, TikTok, etc.)     |  |
|  |  - Media Processing Engine (Cloudflare R2 / S3, Validation, Transcoding)   |  |
|  |  - Content Publishing & Scheduling Pipeline                                 |  |
|  |  - Approval Engine & Multi-Tier Workflow                                    |  |
|  |  - Analytics & Metric Normalization Engine                                  |  |
|  |  - Subscription & Plan Enforcer (Razorpay / Stripe)                         |  |
|  +-----------------------------------------------------------------------------+  |
|  | Data Access Layer: Eloquent ORM + Global Scopes + Model Observers           |  |
+-----------------------------------------+-----------------------------------------+
                                          |
                +-------------------------+-------------------------+
                |                                                   |
+---------------v-------------------+               +---------------v---------------+
|          PERSISTENCE LAYER        |               |      ASYNC & QUEUE LAYER      |
|  MySQL 8 (InnoDb, JSON fields,    |               |  Redis 7 (Pub/Sub, Queue,     |
|  Tenant Foreign Keys, Indexes)    |               |  Atomic Locks, Rate Limiters) |
+-----------------------------------+               +---------------+---------------+
                                                                    |
                                                    +---------------v---------------+
                                                    |     WORKER / SUPERVISOR       |
                                                    |  - queue:work --queue=        |
                                                    |    publishing,analytics,media |
                                                    |  - schedule:run (Cron)        |
                                                    +-------------------------------+
```

---

## 2. Laravel Concepts Guide for React / Node.js / Express Developers
*(Hinglish + Clear English Conceptual Bridge)*

Agar aap Node.js, Express aur React me kaam kar chuke hain, toh Laravel ke concepts ko samajhna bohot aasan hai. Laravel me sabhi required building blocks built-in aate hain (batteries included), jiske liye Node ecosystem me 10 alag-alag libraries (Express, Prisma/Mongoose, BullMQ, Passport, Helmet, Dotenv, Joi/Zod, Winston) lagani padti hain.

| Node.js / Express / React Concept | Laravel Equivalent | Deep Explanation (Kaise kaam karta hai) |
|---|---|---|
| **Express Router (`app.get(...)`)** | `routes/web.php` & `Route::get(...)` | Laravel me routing central file me hoti hai. Routes middleware pipeline ke through controllers ya Livewire components se link hote hain. |
| **Express Middleware (`(req, res, next) => ...`)** | `app/Http/Middleware/*` | Har request middleware stack se pass hoti hai. Humara `IdentifyWorkspace` middleware session/subdomain se active tenant check karta hai aur global context set karta hai. |
| **Express Controller / Route Handler** | `app/Http/Controllers/*` | Traditional HTTP endpoints (jaise OAuth callbacks, Stripe/Razorpay webhooks, file downloads) ke liye Controllers use hote hain. |
| **React Components (`useState`, `useEffect`)** | **Livewire 3 Components** (`app/Livewire/*`) | Livewire client-side JavaScript likhe bina server-driven dynamic UI deta hai. Component server par render hota hai, frontend par DOM update (morphing) hoti hai via lightweight AJAX/fetch packets. Zero React/Node needed! |
| **Client-Side State (`zustand`, `redux`)** | **Alpine.js** (`x-data`, `x-show`, `x-on`) | Dropdowns, modals, tabs, preview toggles ke liye browser me hi chalne wali ultra-lightweight library (~15kb). Koi bundle step ya React compile step ki jarurat nahi hoti. |
| **Prisma / Mongoose / TypeORM** | **Eloquent ORM** (`app/Models/*`) | Active Record pattern. `Post::where('workspace_id', $id)->with('variants')->get()` directly database relations ko load karta hai. Multi-tenancy ke liye hum **Global Scopes** use karte hain jo automatically `WHERE workspace_id = ?` inject karta hai. |
| **Zod / Joi Validation** | **Form Requests & Rules** (`app/Http/Requests/*`) | Request validate karne ke liye standard classes. Agar validation fail ho, toh automatically errors return hote hain aur frontend par display ho jate hain. |
| **BullMQ / Celery (Redis Queues)** | **Laravel Queues & Jobs** (`app/Jobs/*`) | Social media posting, token refresh aur analytics sync background workers run karte hain via `php artisan queue:work --queue=publishing`. Redis acts as the queue broker. |
| **node-cron** | **Laravel Task Scheduler** (`app/Console/Kernel.php` or `routes/console.php`) | Server par sirf 1 cron job chalti hai (`* * * * * php artisan schedule:run`). Laravel internally decide karta hai ki kab konsa job run hona hai (e.g. `everyMinute()`, `daily()`). |
| **Node EventEmitter / Socket.io** | **Laravel Events & Listeners** (`app/Events/*`, `app/Listeners/*`) | Decoupled architecture. Jaise hi `PostPublished` event trigger hota hai, alag-alag listeners (e.g. `SendClientNotification`, `UpdateWorkspaceQuota`) execute hote hain. |
| **JWT / Express Session / CASL** | **Laravel Auth, Gates & Policies** (`app/Policies/*`) | RBAC (Role Based Access Control). `PostPolicy` check karta hai ki kya current user kisi specific post ko edit ya publish karne ke liye authorized hai ya nahi. |

---

## 3. Complete Directory Structure

```
e:/social/
|-- app/
|   |-- Console/
|   |   `-- Commands/
|   |       |-- DispatchScheduledPostsCommand.php
|   |       |-- SyncSocialAccountsHealthCommand.php
|   |       |-- PruneExpiredOAuthTransactionsCommand.php
|   |       `-- AggregateDailyAnalyticsCommand.php
|   |-- Enums/
|   |   |-- SocialPlatform.php             // FACEBOOK, INSTAGRAM, LINKEDIN, X, etc.
|   |   |-- PostStatus.php                 // DRAFT, PENDING_APPROVAL, APPROVED, SCHEDULED, PUBLISHING, PUBLISHED, PARTIALLY_PUBLISHED, FAILED
|   |   |-- PublishingStatus.php           // PENDING, PROCESSING, SUCCESS, FAILED, RETRYING
|   |   |-- ConnectionStatus.php           // CONNECTED, EXPIRED, REAUTHORIZATION_REQUIRED, PERMISSION_MISSING, ERROR
|   |   |-- WorkspaceRole.php              // OWNER, ADMIN, MANAGER, EDITOR, CLIENT_VIEWER
|   |   |-- MediaType.php                  // IMAGE, VIDEO, GIF, CAROUSEL, DOCUMENT
|   |   `-- ApprovalStatus.php             // PENDING, APPROVED, REJECTED, CHANGES_REQUESTED
|   |-- Events/
|   |   |-- PostCreated.php
|   |   |-- PostApproved.php
|   |   |-- PostPublished.php
|   |   |-- PostPublishingFailed.php
|   |   |-- SocialTokenExpiring.php
|   |   `-- WebhookEventReceived.php
|   |-- Exceptions/
|   |   |-- SocialProviderException.php
|   |   |-- PlanLimitExceededException.php
|   |   |-- TenantAccessDeniedException.php
|   |   `-- TokenRefreshException.php
|   |-- Http/
|   |   |-- Controllers/
|   |   |   |-- Auth/
|   |   |   |   |-- LoginController.php
|   |   |   |   |-- RegisterController.php
|   |   |   |   `-- PasswordResetController.php
|   |   |   |-- SocialOAuthController.php   // Central OAuth redirect & callback handler
|   |   |   |-- WebhookController.php       // Incoming platform webhooks (Meta, Stripe, Razorpay)
|   |   |   |-- MediaController.php         // Direct R2/S3 upload signature & serving
|   |   |   |-- ReportExportController.php  // PDF & CSV downloads
|   |   |   `-- SuperAdminController.php
|   |   |-- Middleware/
|   |   |   |-- IdentifyWorkspace.php       // Resolves tenant, sets active workspace in container
|   |   |   |-- EnsureWorkspaceSubscription.php // Verifies plan active & limits
|   |   |   |-- CheckWorkspaceRole.php      // Role & permission enforcement
|   |   |   `-- VerifyWebhookSignature.php  // Cryptographic signature check for webhooks
|   |   `-- Requests/
|   |       |-- StoreClientRequest.php
|   |       |-- StoreMediaRequest.php
|   |       `-- UpdateWorkspaceSettingsRequest.php
|   |-- Jobs/
|   |   |-- Publishing/
|   |   |   |-- DispatchDuePostsJob.php     // Runs every minute, claims due variants
|   |   |   |-- PublishPostVariantJob.php   // Worker that invokes provider adapter
|   |   |   `-- ReconcileUncertainPublishJob.php // Resolves network timeouts
|   |   |-- Analytics/
|   |   |   |-- SyncSocialProfileAnalyticsJob.php
|   |   |   `-- AggregateWorkspaceMetricsJob.php
|   |   |-- Media/
|   |   |   |-- ProcessMediaAssetJob.php    // Extracts metadata, generates thumbnails
|   |   |   `-- DeleteOrphanedMediaJob.php
|   |   |-- Reports/
|   |   |   `-- GenerateClientReportJob.php // Compiles PDF/CSV asynchronously
|   |   |-- Social/
|   |   |   |-- RefreshSocialTokenJob.php
|   |   |   `-- SyncSocialProfileMetadataJob.php
|   |   `-- Webhooks/
|   |       `-- ProcessWebhookEventJob.php
|   |-- Livewire/
|   |   |-- Auth/
|   |   |-- Dashboard/
|   |   |   `-- WorkspaceOverview.php
|   |   |-- Composer/
|   |   |   |-- PostComposer.php            // Master 3-column interactive composer
|   |   |   |-- PlatformVariantTab.php
|   |   |   |-- MediaSelectorModal.php
|   |   |   `-- LivePostPreview.php
|   |   |-- Calendar/
|   |   |   `-- ContentCalendar.php         // FullCalendar Livewire wrapper
|   |   |-- Posts/
|   |   |   |-- PostList.php
|   |   |   `-- PostDetailsModal.php
|   |   |-- SocialAccounts/
|   |   |   |-- AccountManager.php          // Connected accounts & platform grid
|   |   |   `-- ProfileSelectionModal.php   // Post-OAuth profile selection
|   |   |-- MediaLibrary/
|   |   |   |-- MediaManager.php
|   |   |   |-- FolderTree.php
|   |   |   `-- AssetDetailModal.php
|   |   |-- Inbox/
|   |   |   |-- UnifiedInbox.php
|   |   |   `-- ConversationThread.php
|   |   |-- Analytics/
|   |   |   |-- AnalyticsDashboard.php
|   |   |   `-- ProfileAnalyticsView.php
|   |   |-- Reports/
|   |   |   |-- ReportBuilder.php
|   |   |   `-- ScheduledReports.php
|   |   |-- Approvals/
|   |   |   `-- ApprovalCenter.php
|   |   |-- Clients/
|   |   |   `-- ClientManager.php
|   |   |-- Team/
|   |   |   `-- MemberManager.php
|   |   |-- Automations/
|   |   |   `-- AutomationRulesManager.php
|   |   |-- Billing/
|   |   |   |-- PlanSelector.php
|   |   |   |-- SubscriptionDetails.php
|   |   |   `-- PaymentHistory.php
|   |   `-- Settings/
|   |       |-- WorkspaceSettings.php
|   |       `-- AuditLogsViewer.php
|   |-- Models/
|   |   |-- User.php
|   |   |-- Workspace.php
|   |   |-- WorkspaceMember.php
|   |   |-- WorkspaceInvitation.php
|   |   |-- Client.php
|   |   |-- ClientMember.php
|   |   |-- SocialConnection.php
|   |   |-- SocialProfile.php
|   |   |-- OAuthTransaction.php
|   |   |-- SocialTokenEvent.php
|   |   |-- Post.php
|   |   |-- PostVariant.php
|   |   |-- PostMedia.php
|   |   |-- PublishingRecord.php
|   |   |-- PublishAttempt.php
|   |   |-- MediaAsset.php
|   |   |-- MediaFolder.php
|   |   |-- MediaTag.php
|   |   |-- Approval.php
|   |   |-- ApprovalAction.php
|   |   |-- ApprovalComment.php
|   |   |-- Conversation.php
|   |   |-- Message.php
|   |   |-- AnalyticsSnapshot.php
|   |   |-- AnalyticsSyncLog.php
|   |   |-- Report.php
|   |   |-- ReportConfig.php
|   |   |-- AutomationRule.php
|   |   |-- AutomationRun.php
|   |   |-- Plan.php
|   |   |-- Subscription.php
|   |   |-- Payment.php
|   |   |-- Invoice.php
|   |   |-- UsageRecord.php
|   |   |-- WebhookEvent.php
|   |   |-- AuditLog.php
|   |   `-- FeatureFlag.php
|   |-- Policies/
|   |   |-- WorkspacePolicy.php
|   |   |-- ClientPolicy.php
|   |   |-- PostPolicy.php
|   |   |-- SocialConnectionPolicy.php
|   |   `-- MediaAssetPolicy.php
|   |-- Providers/
|   |   |-- AppServiceProvider.php
|   |   |-- SocialServiceProvider.php       // Registers platform registry & adapters
|   |   `-- EventServiceProvider.php
|   `-- Services/
|       |-- AI/
|       |   |-- Contracts/AIAssistantInterface.php
|       |   `-- OpenAIAssistantService.php
|       |-- Billing/
|       |   |-- Contracts/PaymentGatewayInterface.php
|       |   |-- RazorpayService.php
|       |   |-- StripeService.php
|       |   `-- SubscriptionLimitEnforcer.php
|       |-- Media/
|       |   |-- MediaStorageService.php     // R2/S3 integration
|       |   `-- MediaValidationService.php
|       |-- Publishing/
|       |   |-- PublishingEngine.php        // Core scheduler & state orchestrator
|       |   |-- IdempotencyManager.php
|       |   `-- RetryStrategyManager.php
|       `-- Social/
|           |-- Contracts/
|           |   |-- SocialProviderInterface.php
|           |   |-- OAuthProviderInterface.php
|           |   |-- ProfileDiscoveryInterface.php
|           |   |-- PublishingProviderInterface.php
|           |   |-- AnalyticsProviderInterface.php
|           |   `-- ConversationProviderInterface.php
|           |-- DTOs/
|           |   |-- OAuthTokenDTO.php
|           |   |-- DiscoveredProfileDTO.php
|           |   |-- PublishingPayloadDTO.php
|           |   |-- PublishingResultDTO.php
|           |   `-- AnalyticsMetricDTO.php
|           |-- Registry/
|           |   |-- PlatformCapabilityRegistry.php
|           |   `-- SocialProviderManager.php
|           |-- Facebook/
|           |   |-- FacebookOAuthService.php
|           |   |-- FacebookProfileService.php
|           |   |-- FacebookPublishingService.php
|           |   `-- FacebookAnalyticsService.php
|           |-- Instagram/
|           |   |-- InstagramOAuthService.php
|           |   |-- InstagramProfileService.php
|           |   |-- InstagramMediaService.php
|           |   |-- InstagramPublishingService.php
|           |   `-- InstagramAnalyticsService.php
|           |-- LinkedIn/
|           |   |-- LinkedInOAuthService.php
|           |   |-- LinkedInProfileService.php
|           |   `-- LinkedInPublishingService.php
|           |-- YouTube/
|           |   |-- YouTubeOAuthService.php
|           |   |-- YouTubeResumableUploadService.php
|           |   |-- YouTubePublishingService.php
|           |   `-- YouTubeQuotaTrackerService.php
|           |-- TikTok/
|           |   |-- TikTokOAuthService.php
|           |   |-- TikTokCreatorService.php
|           |   `-- TikTokPublishingService.php
|           |-- Pinterest/
|           |   |-- PinterestOAuthService.php
|           |   |-- PinterestBoardService.php
|           |   `-- PinterestPublishingService.php
|           |-- X/
|           |   |-- XOAuthService.php
|           |   |-- XPublishingService.php
|           |   `-- XRateLimitTrackerService.php
|           |-- Threads/
|           |   |-- ThreadsOAuthService.php
|           |   `-- ThreadsPublishingService.php
|           |-- GoogleBusiness/
|           |   |-- GoogleBusinessOAuthService.php
|           |   |-- GoogleBusinessLocationService.php
|           |   `-- GoogleBusinessPublishingService.php
|           `-- Bluesky/
|               |-- BlueskyAuthService.php
|               `-- BlueskyPublishingService.php
|-- config/
|   |-- social.php                         // App IDs, secrets, redirect URIs, scopes
|   |-- capabilities.php                   // Feature matrix per platform
|   `-- plans.php                          // Tier limits (profiles, members, storage)
|-- database/
|   |-- migrations/                        // Strict multi-tenant schema migrations
|   |-- seeders/
|   `-- factories/
|-- resources/
|   |-- css/
|   |   `-- app.css                        // Custom Bootstrap overrides & theme styling
|   |-- js/
|   |   `-- app.js                         // Bootstrap 5 bundle, Chart.js, FullCalendar
|   `-- views/
|       |-- components/                    // Reusable Blade components
|       |   |-- button.blade.php
|       |   |-- modal.blade.php
|       |   |-- card.blade.php
|       |   |-- badge.blade.php
|       |   |-- alert.blade.php
|       |   |-- empty-state.blade.php
|       |   `-- loading-indicator.blade.php
|       |-- layouts/
|       |   |-- app.blade.php              // Main SaaS Shell (Sidebar + Topbar + Content)
|       |   |-- auth.blade.php             // Clean login/registration layout
|       |   `-- public.blade.php           // Landing & Pricing layout
|       `-- livewire/                      // Corresponding Livewire views
`-- routes/
    |-- web.php                            // SaaS web routes + Livewire pages
    |-- api.php                            // Webhooks & secure callbacks only
    `-- console.php                        // Scheduler definitions
```

