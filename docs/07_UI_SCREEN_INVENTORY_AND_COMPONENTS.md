# SOCIALPILOTX — UI SCREEN INVENTORY & COMPONENT SPECIFICATION
**Document ID:** `SPX-DOC-007`  
**Phase:** `Phase 0 — UI/UX Design System, Livewire Components & Screen Inventory`  
**Stack:** Laravel Blade, Livewire 3, Alpine.js 3, Bootstrap 5.3, Chart.js, FullCalendar 6.

---

## 1. UI Component Architecture & Design Principles

```
+---------------------------------------------------------------------------------------+
|                                    BLADE LAYOUT SYSTEM                                |
+-------------------------------+-------------------------------+-----------------------+
| layouts/public.blade.php      | layouts/auth.blade.php        | layouts/app.blade.php |
| (Landing, Features, Pricing)  | (Login, Register, Reset)      | (Full SaaS App Shell) |
+-------------------------------+-------------------------------+-----------------------+
                                                                |
                                 +------------------------------+------------------------------+
                                 | Sidebar Navigation (14 items)| Top Navigation & User Menu   |
                                 +------------------------------+------------------------------+
                                 | Dynamic Livewire Page Content Area                          |
                                 | (Multi-State: Loading, Empty, Error, Success, Denied)       |
                                 +-------------------------------------------------------------+
```

---

## 2. Complete Screen Inventory (21 Screens)

| # | Screen Name | Route | Livewire Component | Primary Functionality |
|---|---|---|---|---|
| **01** | **Landing & Pricing** | `/`, `/pricing` | Static Blade / `PublicView` | SaaS marketing, pricing tier cards (Starter, Pro, Agency, Enterprise), FAQs. |
| **02** | **Authentication** | `/login`, `/register` | `App\Livewire\Auth\*` | Multi-step registration, workspace naming, login with remember-me. |
| **03** | **Workspace Setup** | `/onboarding` | `WorkspaceWizard` | Initial workspace configuration (agency name, timezone, client setup). |
| **04** | **Dashboard** | `/dashboard` | `WorkspaceOverview` | High-level metrics, upcoming posts, account connection health, quick actions. |
| **05** | **Social Accounts** | `/social-accounts` | `AccountManager` | 10 Platform connection cards, connection health badges, reauth triggers. |
| **06** | **Profile Picker Modal**| `/social-accounts/select`| `ProfileSelectionModal` | Post-OAuth modal to select discovered Facebook Pages, IG accounts, etc. |
| **07** | **Client Manager** | `/clients` | `ClientManager` | Agency client directory (Sharma Restaurant, etc.), profile mappings. |
| **08** | **Team & Permissions**| `/team` | `MemberManager` | Role assignments (`admin`, `manager`, `editor`, `client_viewer`), invite links. |
| **09** | **Content Composer** | `/composer` | `PostComposer` | 3-column interactive post composer with platform variants & live previews. |
| **10** | **Content Calendar** | `/calendar` | `ContentCalendar` | FullCalendar integration: Month, Week, Day, List views; drag-and-drop reschedule.|
| **11** | **Posts Directory** | `/posts` | `PostList` | Filterable data table (Scheduled, Drafts, Awaiting Approval, Failed, Published).|
| **12** | **Media Library** | `/media` | `MediaManager` | Folder tree, drag-and-drop R2 uploads, preview modal, tag filter, usage inspector.|
| **13** | **Approval Center** | `/approvals` | `ApprovalCenter` | Multi-tier review queue, change request notes, internal discussion threads.|
| **14** | **Social Inbox** | `/inbox` | `UnifiedInbox` | Unified comment threads, review reply box, member assignment, mark resolved.|
| **15** | **Analytics Dashboard**| `/analytics` | `AnalyticsDashboard` | Chart.js visual metrics (Reach, Growth, Engagement), profile level breakdown.|
| **16** | **Reports Generator** | `/reports` | `ReportBuilder` | Custom date range PDF/CSV report generation with agency branding. |
| **17** | **AI Content Assistant**| (Modal in Composer) | `AiAssistantModal` | OpenAI caption generation, hashtag suggester, tone adjuster (Professional, Friendly).|
| **18** | **Automations** | `/automations` | `AutomationRulesManager`| RSS feed ingestion rules, failed posting alerts, schedule reminders.|
| **19** | **Billing & Plans** | `/billing` | `PlanSelector`, `BillingDetails`| Razorpay / Stripe checkout, plan upgrade/downgrade, invoices PDF download.|
| **20** | **Settings & Audit** | `/settings` | `WorkspaceSettings`, `AuditLogsViewer`| Workspace timezone, watermark, webhook secrets, complete audit trail.|
| **21** | **Super Admin Portal**| `/admin/overview` | `SuperAdminOverview` | Platform-wide tenant count, active connections, global health, feature flags.|

---

## 3. The 3-Column Content Composer Architecture

The **Content Composer** is the centerpiece of SocialPilotX. Built in Livewire 3 with Alpine.js reactivity, it is organized into three synchronized panels:

```
+--------------------------------------------------------------------------------------------------+
|                                  SOCIALPILOTX CONTENT COMPOSER                                   |
+------------------------------+------------------------------------+------------------------------+
| LEFT PANEL: TARGETS & BASE   | CENTER PANEL: VARIANT CUSTOMIZER   | RIGHT PANEL: LIVE PREVIEW    |
+------------------------------+------------------------------------+------------------------------+
| 1. Client Selector           | 1. Platform Tabs:                  | 1. Real-time Mock Previews:  |
|    [Sharma Restaurant v]     |    [All] [FB Page] [IG] [LinkedIn] |    - Facebook Desktop/Mobile |
|                              |                                    |    - Instagram Feed / Reel   |
| 2. Profile Target Checkboxes:| 2. Independent Caption Editor:     |    - LinkedIn Feed Post      |
|    [x] FB: Sharma Main Page  |    - Character Counter (Dynamic)   |    - X (Twitter) Card        |
|    [x] IG: @sharmarestaurant |    - Hashtag Insertion Pills       |                              |
|    [x] LI: Sharma Hospitality|    - Emoji Picker                  | 2. Validation Inspector:     |
|                              |                                    |    - Character limit badge   |
| 3. Master Base Caption:      | 3. Platform Settings Accordion:    |    - Aspect ratio warnings   |
|    "Weekend buffet is here!" |    - FB: Feed vs Reel              |    - Required field alerts   |
|                              |    - IG: Share to Feed checkbox    |                              |
| 4. Media Asset Picker:       |    - Pinterest: Board & Link       | 3. Scheduling Footer:        |
|    [Select from Media Library|                                    |    [Save Draft]              |
|    or Upload New Asset]      | 4. Variant Media Ordering:         |    [Submit for Approval]     |
|                              |    - Drag-to-reorder thumbnails    |    [Publish Now]             |
|                              |    - Alt-text input per image      |    [Schedule for Later v]    |
+------------------------------+------------------------------------+------------------------------+
```

---

## 4. Reusable Blade Components

Standardized UI components created under `resources/views/components/`:

- `<x-button variant="primary|secondary|danger|success" wire:loading.attr="disabled">`: Standardized buttons with built-in spinner states.
- `<x-modal id="modalId" title="Title">`: Accessible Bootstrap modal container bound to Alpine.js `x-model`.
- `<x-card title="Card Title" :actions="$actions">`: Standardized card panel with header, body, and footer slots.
- `<x-badge variant="success|warning|danger|info">`: Color-coded status indicator.
- `<x-alert type="success|warning|danger">`: Alert notifications with dismissible option.
- `<x-empty-state icon="..." title="..." action="...">`: Visual illustration when zero records exist.
- `<x-loading-indicator>`: Elegant SVG skeleton or spinner displayed during Livewire wire:target processing.

