@extends('layouts.public', ['title' => 'Privacy Policy — PostSync SaaS'])

@section('content')
<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-2">Privacy Policy for PostSync SaaS</h1>
            <p class="text-muted">Application: <strong>PostSync SaaS</strong> | Domain: <code>postsync.cloud</code> | Last updated: {{ date('F d, Y') }}</p>

            <hr class="my-4">

            <div class="content lh-lg text-secondary">
                <h5 class="fw-bold text-dark mt-4">1. Introduction & Overview</h5>
                <p>Welcome to <strong>PostSync SaaS</strong> (accessible at <a href="https://postsync.cloud">https://postsync.cloud</a>). PostSync SaaS is an enterprise social media management, publishing, and scheduling software-as-a-service (SaaS) platform built for content creators, digital marketing agencies, and businesses. We respect your privacy and are committed to protecting your personal data, connected social channels, and authorized account tokens.</p>

                <h5 class="fw-bold text-dark mt-4">2. Application Purpose & How PostSync SaaS Operates</h5>
                <p>PostSync SaaS provides unified scheduling, drafting, multi-tier approvals, media asset management, and analytics across official third-party social networks including YouTube, Meta (Facebook & Instagram), LinkedIn, Google Business, TikTok, X (Twitter), Pinterest, and Bluesky. Users connect their accounts via industry-standard OAuth 2.0 handshakes to grant explicit, user-authorized permissions.</p>

                <h5 class="fw-bold text-dark mt-4">3. Information We Collect</h5>
                <p>When you register on PostSync SaaS and connect your accounts, we collect:</p>
                <ul>
                    <li><strong>Account Credentials & Identifiers:</strong> Name, business email address, public profile name, unique platform ID, username, and public avatar URL.</li>
                    <li><strong>OAuth Authorization Tokens:</strong> Ephemeral and long-lived OAuth access tokens and refresh tokens required to interact with authorized platform APIs on your behalf. All access tokens and secrets are strictly encrypted at rest using AES-256-GCM encryption.</li>
                    <li><strong>User-Generated Media & Content:</strong> Captions, titles, video descriptions, tags, category selections, custom thumbnails, photos, videos, and scheduled publication timestamps submitted through our Content Composer and Media Library.</li>
                    <li><strong>Analytics & Performance Metrics:</strong> Aggregate impressions, views, reach, likes, follower counts, and engagement statistics retrieved from official APIs to populate your workspace analytics dashboards.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">4. Google API Services & YouTube User Data Policy Disclosure</h5>
                <div class="p-4 bg-light rounded-3 border my-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-google text-primary me-2"></i>Google API Services User Data Policy Compliance</h6>
                    <p class="small mb-2">
                        PostSync SaaS's use and transfer of information received from Google APIs to any other app will adhere to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" class="text-decoration-underline text-primary">Google API Services User Data Policy</a>, including the Limited Use requirements.
                    </p>
                    <ul class="small mb-2">
                        <li><strong>Google Scopes Requested:</strong> PostSync SaaS requests <code>https://www.googleapis.com/auth/youtube.upload</code>, <code>https://www.googleapis.com/auth/youtube.readonly</code>, <code>https://www.googleapis.com/auth/userinfo.profile</code>, and <code>https://www.googleapis.com/auth/userinfo.email</code>.</li>
                        <li><strong>Purpose of Access:</strong> We access these scopes exclusively to allow you to upload and schedule videos to your own YouTube channel, set custom video thumbnails, edit video metadata (titles, descriptions, tags, and privacy settings), and display channel analytics in your PostSync SaaS dashboard.</li>
                        <li><strong>No Transfer or Sale of Google User Data:</strong> We <strong>never sell, lease, or transfer</strong> Google user data to data brokers, advertising networks, or third-party service providers. Google user data is not shared with any third parties except as strictly necessary to fulfill the publishing actions directly initiated by you.</li>
                        <li><strong>No Generalized AI Model Training:</strong> PostSync SaaS <strong>does not use Google user data or YouTube data to train, retrain, or fine-tune generalized artificial intelligence (AI) or machine learning (ML) models</strong>.</li>
                        <li><strong>Strict AI NCII Prohibition:</strong> PostSync SaaS complies fully with the <a href="https://developers.google.com/terms" target="_blank" class="text-decoration-underline text-secondary">Google APIs Terms of Service</a>. PostSync SaaS <strong>strictly prohibits and does not use Google APIs for creating, generating, hosting, processing, or distributing Non-Consensual Intimate Imagery (AI NCII)</strong> or any illicit synthetic media.</li>
                        <li><strong>YouTube Terms of Service:</strong> By connecting your YouTube account with PostSync SaaS, you also acknowledge and agree to be bound by the <a href="https://www.youtube.com/t/terms" target="_blank" class="text-decoration-underline text-primary">YouTube Terms of Service</a> and the <a href="https://policies.google.com/privacy" target="_blank" class="text-decoration-underline text-primary">Google Privacy Policy</a>.</li>
                    </ul>
                </div>

                <h5 class="fw-bold text-dark mt-4">5. How You Can Revoke Access to Google & Social Accounts</h5>
                <p>You have continuous and complete control over your authorized connections:</p>
                <ul>
                    <li><strong>Inside PostSync SaaS:</strong> You can disconnect any connected social account or YouTube channel at any time from your <a href="https://postsync.cloud/social-accounts">Social Accounts Settings</a> page, which immediately deletes all stored OAuth tokens from our database.</li>
                    <li><strong>Directly via Google Security:</strong> You can revoke PostSync SaaS's access to your Google account at any time by visiting the official <a href="https://myaccount.google.com/permissions" target="_blank" class="text-decoration-underline text-primary">Google Third-Party Apps & Services Permissions Page</a>.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">6. Data Retention, Security & Deletion</h5>
                <p>We retain your account data and scheduled posts only as long as your workspace account is active:</p>
                <ul>
                    <li>All data transmitted between your browser, our servers, and third-party APIs is protected using modern Transport Layer Security (TLS 1.3 / HTTPS).</li>
                    <li>All OAuth tokens and credentials are encrypted at rest using AES-256-GCM cryptographic encryption.</li>
                    <li>To request complete, permanent deletion of your account and all associated data, follow our <a href="{{ route('legal.deletion') }}">User Data Deletion Instructions</a> or submit a request directly through Meta's automated data deletion callback.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">7. Contact Information</h5>
                <p>For any privacy, compliance, or security inquiries regarding PostSync SaaS, please contact our Data Protection and Compliance Team:</p>
                <div class="p-3 bg-light rounded-3 border my-3">
                    <strong>PostSync SaaS Compliance Team</strong><br>
                    <strong>Email:</strong> <code>techshad04@gmail.com</code> (or <code>support@postsync.cloud</code>)<br>
                    <strong>Website:</strong> <a href="https://postsync.cloud">https://postsync.cloud</a><br>
                    <strong>Address:</strong> India
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
