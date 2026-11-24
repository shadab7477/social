# SOCIALPILOTX — INFRASTRUCTURE, DEVOPS & LIMITATIONS SPECIFICATION
**Document ID:** `SPX-DOC-008`  
**Phase:** `Phase 0 — DevOps, Production Deployment & Platform Compliance`  

---

## 1. Production Infrastructure Architecture

```
                                  [ Internet / Users ]
                                           |
                                  [ Cloudflare WAF/CDN ]
                                  (SSL, DDoS, Edge Cache)
                                           |
                              +------------v------------+
                              |   Ubuntu 22.04/24.04    |
                              |         NGINX           |
                              +------------+------------+
                                           |
                      +--------------------+--------------------+
                      |                                         |
            +---------v---------+                     +---------v---------+
            |  PHP 8.2+ FPM     |                     |  Supervisor Pool  |
            |  (Web Requests &  |                     |  (Background      |
            |   Livewire AJAX)  |                     |   Queue Workers)  |
            +---------+---------+                     +---------+---------+
                      |                                         |
                      +--------------------+--------------------+
                                           |
                     +---------------------+---------------------+
                     |                                           |
           +---------v---------+                       +---------v---------+
           |      MySQL 8      |                       |      Redis 7      |
           |  (Tenant Storage, |                       |  (Queues, Locks,  |
           |   InnoDB Buffer)  |                       |   Rate Limiters)  |
           +-------------------+                       +-------------------+
                     |
           +---------v---------+
           |   Cloudflare R2   |
           | (Media S3 Storage)|
           +-------------------+
```

---

## 2. Server Configuration Blueprints

### A. Nginx Virtual Host (`/etc/nginx/sites-available/socialpilotx.conf`)
```nginx
server {
    listen 80;
    server_name app.socialpilotx.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name app.socialpilotx.com;
    root /var/www/socialpilotx/public;

    ssl_certificate /etc/letsencrypt/live/app.socialpilotx.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.socialpilotx.com/privkey.pem;

    client_max_body_size 512M; # Required for high-resolution video uploads

    index index.php index.html;
    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### B. Supervisor Queue Worker Configuration (`/etc/supervisor/conf.d/socialpilotx-workers.conf`)
```ini
[program:socialpilotx-publishing]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/socialpilotx/artisan queue:work redis --queue=publishing --sleep=3 --tries=3 --timeout=120 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/socialpilotx/storage/logs/worker-publishing.log

[program:socialpilotx-media]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/socialpilotx/artisan queue:work redis --queue=media,reports --sleep=3 --tries=2 --timeout=600 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/socialpilotx/storage/logs/worker-media.log

[program:socialpilotx-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/socialpilotx/artisan queue:work redis --queue=analytics,notifications,default --sleep=3 --tries=3 --timeout=60 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/socialpilotx/storage/logs/worker-default.log
```

### C. System Cron (`crontab -e -u www-data`)
```bash
* * * * * cd /var/www/socialpilotx && php artisan schedule:run >> /dev/null 2>&1
```

---

## 3. Third-Party Platform Approval Checklists

Before launching to external users, commercial review and verification must be cleared with each platform:

### 1. Meta (Facebook & Instagram)
- [ ] **Business Verification**: Submit official company registration documents to Meta Business Manager.
- [ ] **App Review Submission**:
  - Request permissions: `pages_show_list`, `pages_read_engagement`, `pages_manage_posts`, `instagram_basic`, `instagram_content_publish`.
  - Provide a screencast demonstrating:
    1. User logging into SocialPilotX.
    2. Clicking "Connect Facebook" / "Connect Instagram".
    3. The Meta OAuth dialog with requested permissions.
    4. Profile selection interface.
    5. Creating a post in Composer and publishing it to a test Page and Instagram Business account.
- [ ] **Privacy Policy & Data Deletion Callback**: Publicly accessible HTTPS URL with working automated deletion callback.

### 2. LinkedIn Developer Portal
- [ ] Create LinkedIn Page for SocialPilotX.
- [ ] Request Access to the **Community Management API** product.
- [ ] Verify test publishing to personal profile and organization page.

### 3. Google Cloud Console (YouTube & Google Business Profile)
- [ ] Configure OAuth Consent Screen (Internal vs External).
- [ ] Complete YouTube API Services verification for scope `https://www.googleapis.com/auth/youtube.upload`.
- [ ] Request Quota Extension from default 10,000 units/day to 100,000+ units/day once reaching active customers.

### 4. TikTok Developer
- [ ] Apply for Developer Account.
- [ ] Register App with **Login Kit** and **Content Posting API**.
- [ ] Submit App for Commercial Audit (unaudited apps can only post private videos).

### 5. X (Twitter Developer Portal)
- [ ] Enroll in Developer Portal with Basic or Pro tier.
- [ ] Configure User Authentication Settings: OAuth 2.0, Web App, Redirect URL, Scopes (`tweet.read`, `tweet.write`, `users.read`, `offline.access`).

---

## 4. Known Technical Limitations & Operational Guardrails

1. **Instagram Personal Accounts**:
   - Meta Graph API **strictly prohibits** automated publishing to consumer/personal Instagram accounts. Users must convert to a Free Instagram Professional (Business or Creator) account.
2. **Container Asynchronicity**:
   - Instagram and Threads media containers require server-side transcoding before publication. Workers must poll container status (`GET /{creation_id}`) and only execute `media_publish` once status transitions to `FINISHED`.
3. **YouTube Daily Quota**:
   - Google allocates 10,000 quota units per day by default. A video upload costs 1,600 units (~6 uploads/day total across the entire developer project). We enforce a strict Redis tracker and require self-hosted Google OAuth credentials for Enterprise agencies.
4. **X (Twitter) Tier Paywalls**:
   - The Free tier allows only 1,500 tweets/month platform-wide. Production deployment requires Basic ($100/mo) or Pro ($5,000/mo) depending on customer scale.
5. **Direct Media Upload vs HTTPS URLs**:
   - Meta Instagram, Threads, and TikTok APIs fetch video/photo assets by pulling them from a public HTTPS URL. Therefore, media must be uploaded to Cloudflare R2 / S3 with public read permissions before dispatching the publish job.

