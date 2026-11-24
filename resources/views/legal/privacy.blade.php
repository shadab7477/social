@extends('layouts.public')

@section('content')
<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-2">Privacy Policy</h1>
            <p class="text-muted">Domain: <code>postsync.cloud</code> | Last updated: {{ date('F d, Y') }}</p>

            <hr class="my-4">

            <div class="content lh-lg text-secondary">
                <h5 class="fw-bold text-dark mt-4">1. Introduction</h5>
                <p>Welcome to <strong>PostSync</strong> (accessible at <a href="https://postsync.cloud">https://postsync.cloud</a>). PostSync provides an all-in-one social media publishing, scheduling, and community engagement SaaS platform. We respect your privacy and are committed to protecting the personal data and authorized account information you share with us.</p>

                <h5 class="fw-bold text-dark mt-4">2. Information We Collect</h5>
                <p>When you register on PostSync and connect your social media profiles (including Meta Facebook Pages, Instagram Professional Accounts, LinkedIn, YouTube, and Google Business Profile), we collect:</p>
                <ul>
                    <li><strong>Account Credentials & Identifiers:</strong> Name, business email address, public profile name, unique profile ID, username, and public avatar URL.</li>
                    <li><strong>OAuth Authorization Tokens:</strong> Ephemeral and long-lived OAuth access tokens and refresh tokens required to publish content on your behalf. All access tokens are strictly encrypted at rest using AES-256-GCM encryption.</li>
                    <li><strong>User-Generated Content:</strong> Post captions, media files (photos, videos), scheduled dates, and comments submitted through our Content Composer and Unified Inbox.</li>
                    <li><strong>Analytics & Metric Aggregates:</strong> Aggregated audience reach, impressions, follower counts, and post engagement metrics retrieved from official platform APIs to generate your workspace analytics dashboards and client reports.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">3. How We Use Your Information</h5>
                <p>We process your data exclusively for legitimate platform operations:</p>
                <ul>
                    <li>Facilitating scheduled and instant content publishing to your connected social channels upon your explicit command.</li>
                    <li>Retrieving comments and messages to display in your Unified Social Inbox for team moderation and responses.</li>
                    <li>Compiling performance reports and trend analytics for your authorized workspaces.</li>
                    <li>Securing account access and verifying team member permissions.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">4. Compliance with Meta Platform Terms & Google API Services</h5>
                <p>PostSync operates in full compliance with the <strong>Meta Platform Terms</strong>, <strong>Developer Policies</strong>, and <strong>Google API Services User Data Policy</strong>. Specifically:</p>
                <ul>
                    <li>We <strong>do not sell, rent, or lease</strong> your personal data, tokens, or social media content to any data brokers, advertising networks, or third parties.</li>
                    <li>We do not use your private social content or inbox messages for training generalized AI models without explicit consent.</li>
                    <li>Access tokens are used strictly to perform actions directly requested by the user within the PostSync dashboard.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">5. Data Retention & Deletion</h5>
                <p>We retain your connected account information only as long as your workspace remains active. You can disconnect any social profile or delete all associated access tokens at any time via your <a href="https://postsync.cloud/social-accounts">Social Accounts</a> dashboard.</p>
                <p>To request permanent deletion of your account and all associated data, follow our <a href="{{ route('legal.deletion') }}">User Data Deletion Instructions</a> or submit a request directly through Meta's automated data deletion callback.</p>

                <h5 class="fw-bold text-dark mt-4">6. Security Measures</h5>
                <p>All communications between your browser, our application servers, and third-party social APIs are encrypted in transit via SSL/TLS (HTTPS). Access tokens and secrets are encrypted at rest using modern cryptographic primitives.</p>

                <h5 class="fw-bold text-dark mt-4">7. Contact Information</h5>
                <p>For any questions or compliance inquiries regarding this Privacy Policy, please contact our Data Protection Officer at:</p>
                <div class="p-3 bg-light rounded-3 border my-3">
                    <strong>PostSync Privacy & Legal Team</strong><br>
                    <strong>Email:</strong> <code>support@postsync.cloud</code><br>
                    <strong>Website:</strong> <a href="https://postsync.cloud">https://postsync.cloud</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
