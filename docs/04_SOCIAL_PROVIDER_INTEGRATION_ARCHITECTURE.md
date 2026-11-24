# SOCIALPILOTX — SOCIAL PROVIDER INTEGRATION ARCHITECTURE
**Document ID:** `SPX-DOC-004`  
**Phase:** `Phase 0 — Social API Architecture & Protocol Standards`  

---

## 1. Unified Provider Contracts & Interface Hierarchy

Every social platform is represented by an isolated provider module under `App\Services\Social\{Platform}\`. We do not force a monolithic single interface on wildly disparate platforms; instead, we implement a **Clean Interface Segregation** pattern:

```
                      +-----------------------------+
                      |   SocialProviderInterface   |
                      |  - getPlatform(): string    |
                      |  - getCapabilities(): array |
                      +--------------+--------------+
                                     |
         +---------------------------+---------------------------+
         |                           |                           |
+--------v----------------+ +--------v----------------+ +--------v----------------+
|  OAuthProviderInterface | |PublishingProviderIntf. | | AnalyticsProviderIntf. |
| - getAuthorizationUrl() | | - publishText()        | | - getProfileMetrics()  |
| - handleCallback()      | | - publishImage()       | | - getPostMetrics()     |
| - refreshToken()        | | - publishVideo()       | +------------------------+
+-------------------------+ | - publishCarousel()    |
                            | - checkPublishStatus() |
                            +------------------------+
```

---

## 2. Platform Capability Matrix

The `PlatformCapabilityRegistry` informs the Content Composer, Scheduler, and Validator of supported features in real time:

| Platform | Text Limit | Single Image | Multi Image / Carousel | Video | Reels / Shorts | Stories | Native Scheduling | Comment Moderation | Direct Messages |
|---|---|---|---|---|---|---|---|---|---|
| **Facebook Pages** | 63,206 chars | Yes (JPG, PNG) | Yes (up to 10) | Yes (MP4, MOV) | Yes | Yes (Page story) | Yes | Yes (Graph API) | Yes (Page inbox) |
| **Instagram** | 2,200 chars | Yes (1:1, 4:5) | Yes (up to 10) | Yes (Reels) | Yes (9:16) | Yes (eligible) | Yes | Yes (Media comments) | Yes (Professional) |
| **LinkedIn** | 3,000 chars | Yes | Yes (up to 9) | Yes (MP4) | No | No | No | Yes (Org comments) | No |
| **YouTube** | 5,000 chars | No (Thumbnail) | No | Yes (Landscape) | Yes (Shorts 9:16) | No | Yes (PublishAt) | Yes (Video comments)| No |
| **TikTok** | 2,200 chars | Yes (Photos) | Yes | Yes (MP4) | Yes (Native) | No | No | Yes (Webhooks) | No |
| **Pinterest** | 500 chars (title: 100) | Yes | Yes (Carousel) | Yes | No | No | No | No | No |
| **X (Twitter)** | 280 (Free) / 25k (Prem) | Yes (up to 4) | Yes (up to 4) | Yes | No | No | No | No (Tier dependent) | No |
| **Threads** | 500 chars | Yes | Yes (up to 10) | Yes (5 mins max)| No | No | No | Yes (Replies) | No |
| **Google Business**| 1,500 chars | Yes | No | No | No | No | No | Yes (Reviews) | No |
| **Bluesky** | 300 graphemes | Yes (up to 4) | Yes (up to 4) | Yes (60s max) | No | No | No | Yes (Post replies) | No |

---

## 3. Platform-by-Platform Architectural Specifications

### 1. Facebook (Meta Graph API v20.0+)
- **OAuth Scopes**: `pages_show_list`, `pages_read_engagement`, `pages_manage_posts`, `pages_read_user_content`, `read_insights`.
- **Flow**:
  1. User authenticates via Facebook Login dialog.
  2. Exchange code for short-lived user token (`oauth/access_token`).
  3. Exchange short-lived token for 60-day long-lived user token (`fb_exchange_token`).
  4. Query `/me/accounts` to retrieve all manageable Facebook Pages along with Page permanent access tokens.
  5. Store Page token encrypted; Page tokens derived from long-lived user tokens do not expire unless password changed or permissions revoked.
- **Publishing Endpoints**:
  - Text/Link: `POST /{page-id}/feed`
  - Photos: `POST /{page-id}/photos` (multipart or `url` parameter)
  - Videos: `POST /{page-id}/videos` (Chunked resumable upload for files > 25MB)
  - Reels: Meta Reels Publishing API (`POST /{page-id}/video_reels` with container status check)

### 2. Instagram (Graph API for Business/Creator Accounts)
- **Account Requirement**: Only Instagram Professional accounts (Business or Creator) connected to a Facebook Page or authorized via Instagram Login for Business.
- **Discovery**: Query `GET /{page-id}?fields=instagram_business_account` to obtain `instagram_business_account.id`.
- **Container-Based Publishing Workflow**:
  - **Step 1 (Create Container)**:
    - Single Image: `POST /{ig-user-id}/media?image_url={PUBLIC_HTTPS_URL}&caption={CAPTION}`
    - Video/Reel: `POST /{ig-user-id}/media?video_url={PUBLIC_HTTPS_URL}&media_type=REELS&caption={CAPTION}`
    - Carousel: Create child item containers with `is_carousel_item=true`, then create parent container `POST /{ig-user-id}/media?media_type=CAROUSEL&children={child_id_1},{child_id_2}&caption={CAPTION}`.
  - **Step 2 (Poll Status)**:
    - Query `GET /{creation-id}?fields=status_code`. Wait for `FINISHED` (handle `IN_PROGRESS`, `ERROR`).
  - **Step 3 (Publish Container)**:
    - Execute `POST /{ig-user-id}/media_publish?creation_id={creation-id}`.
    - Provider returns permanent `id`.

### 3. LinkedIn (Community Management & Posts API)
- **OAuth Scopes**: `openid`, `profile`, `email`, `w_member_social`, `w_organization_social`, `r_organization_social`.
- **Discovery**:
  - User details: `GET /v2/userinfo`
  - Organization roles: `GET /v2/organizationalEntityAcls?q=roleAssignee`
- **Publishing via Posts API (`/rest/posts`)**:
  - Image/Video upload requires 2-step register flow:
    1. Initialize upload: `POST /rest/images?action=initializeUpload` (returns `uploadUrl` and `imageURN`).
    2. PUT binary data to `uploadUrl`.
    3. Construct post payload targeting author URN (`urn:li:person:...` or `urn:li:organization:...`) referencing the asset URN.

### 4. YouTube (Google Data API v3)
- **OAuth Scopes**: `https://www.googleapis.com/auth/youtube.upload`, `https://www.googleapis.com/auth/youtube.readonly`.
- **Resumable Upload Architecture**:
  - Step 1: POST to `https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status`.
  - Receive `Location` HTTP header pointing to unique upload URI.
  - Step 2: Upload binary chunks (multiple of 256KB) via HTTP PUT.
  - Step 3: Track processing status via `GET /v3/videos?part=processingDetails&id={video_id}`.
- **Quota Management**: Standard project quota is 10,000 units/day. A video upload costs 1,600 units (~6 uploads/day on default quota). `YouTubeQuotaTrackerService` maintains real-time Redis tally to prevent hard quota lockout.

### 5. TikTok (Login Kit & Content Posting API)
- **OAuth Scopes**: `user.info.basic`, `video.publish`, `video.upload`.
- **Audit Restriction Notice**: Unaudited TikTok applications are limited to private videos or creator self-testing. Commercial production requires TikTok Developer App Review.
- **Workflow**:
  1. Fetch creator status: `POST /v2/post/publish/creator_info/query/` to check allowed video duration, privacy levels (PUBLIC, MUTUAL_FOLLOW_FRIENDS, SELF_ONLY), comment/duet/stitch disabled flags.
  2. Initiate Direct Post: `POST /v2/post/publish/video/init/` with `source_info.video_url`.
  3. Poll publish status: `POST /v2/post/publish/status/fetch/` until `PUBLISH_COMPLETE`.

### 6. Pinterest (API v5)
- **OAuth Scopes**: `boards:read`, `boards:write`, `pins:read`, `pins:write`.
- **Workflow**:
  1. Fetch user boards: `GET /v5/boards`.
  2. Media upload: `POST /v5/media` to obtain upload parameters, then upload asset.
  3. Create Pin: `POST /v5/pins` referencing `board_id`, `title`, `description`, `link`, `media_source`.

### 7. X (Twitter API v2)
- **OAuth**: OAuth 2.0 with PKCE (`code_challenge` / `code_verifier`).
- **Scopes**: `tweet.read`, `tweet.write`, `users.read`, `offline.access`.
- **Media Upload**: Media upload still resides on `upload.twitter.com/1.1/media/upload.json` (INIT, APPEND, FINALIZE).
- **Post Creation**: `POST /2/tweets` with `text` and `media.media_ids`.
- **Rate-Limit Enforcement**: X endpoints strictly cap write limits per 15-minute window and monthly tweet caps based on subscription tier (Free, Basic, Pro).

### 8. Threads (Official Threads Graph API)
- **OAuth Scopes**: `threads_basic`, `threads_content_publish`, `threads_read_replies`.
- **Container Workflow**:
  - Step 1: `POST /v1.0/{threads-user-id}/threads?media_type=TEXT&text={text}` or `media_type=IMAGE&image_url={url}`.
  - Step 2: `POST /v1.0/{threads-user-id}/threads_publish?creation_id={creation-id}`.

### 9. Google Business Profile (Location & Local Post API)
- **OAuth Scopes**: `https://www.googleapis.com/auth/business.manage`.
- **Discovery**:
  - `GET https://mybusinessaccountmanagement.googleapis.com/v1/accounts`
  - `GET https://mybusinessbusinessinformation.googleapis.com/v1/{account}/locations`
- **Publishing**: `POST https://mybusiness.googleapis.com/v4/{name}/localPosts` (STANDARD post, EVENT, or OFFER with Call-to-Action URL).

### 10. Bluesky (AT Protocol)
- **Protocol**: ATP (Authenticated Transfer Protocol).
- **Authentication**: `com.atproto.server.createSession` using user handle and app password.
- **Workflow**:
  - Upload image blobs: `POST /xrpc/com.atproto.repo.uploadBlob`.
  - Parse link facets & mentions: Calculate byte offsets in UTF-8.
  - Create post record: `POST /xrpc/com.atproto.repo.createRecord` (`collection: app.bsky.feed.post`). Returns `uri` and `cid`.

