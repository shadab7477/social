@extends('layouts.public')

@section('content')
<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-4">Privacy Policy</h1>
            <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>

            <hr class="my-4">

            <div class="content lh-lg text-secondary">
                <h5 class="fw-bold text-dark mt-4">1. Introduction</h5>
                <p>SocialPilotX ("we", "our", or "us") provides a social media management platform designed to help agencies and businesses manage, schedule, and publish content across multiple social platforms. We are committed to protecting your privacy and ensuring transparency regarding how your data is handled.</p>

                <h5 class="fw-bold text-dark mt-4">2. Information We Collect</h5>
                <p>When you connect your social media accounts (such as Facebook, Instagram, LinkedIn, YouTube, and Google Business Profile), we collect:</p>
                <ul>
                    <li><strong>Account Identifiers:</strong> Public page/profile names, profile IDs, usernames, and profile picture URLs.</li>
                    <li><strong>Authentication Tokens:</strong> OAuth access tokens and refresh tokens required to publish content on your behalf. These tokens are stored securely with AES-256-GCM encryption.</li>
                    <li><strong>Content Data:</strong> Posts, captions, images, and videos created or scheduled through our platform.</li>
                    <li><strong>Performance Analytics:</strong> Aggregate metrics (reach, impressions, engagements) to generate workspace performance dashboards.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">3. How We Use Your Information</h5>
                <p>We use the collected information solely to:</p>
                <ul>
                    <li>Authenticate and maintain connection with your authorized social media accounts.</li>
                    <li>Publish and schedule content to your selected social channels upon your instruction.</li>
                    <li>Display analytics and metrics in your workspace dashboard.</li>
                    <li>Deliver unified inbox messages and comments for moderation.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">4. Third-Party Platform Compliance</h5>
                <p>Our platform uses official APIs provided by Meta (Facebook & Instagram Graph APIs), LinkedIn, Google, and others. We do not sell, rent, or transfer your personal data or social media tokens to any third-party advertisers or data brokers.</p>

                <h5 class="fw-bold text-dark mt-4">5. Data Retention & Deletion</h5>
                <p>You can disconnect your social accounts at any time from the Social Accounts page. Upon disconnection, stored access tokens are permanently deleted. To request full deletion of all personal data, visit our <a href="{{ route('legal.deletion') }}">User Data Deletion Instructions</a>.</p>

                <h5 class="fw-bold text-dark mt-4">6. Contact Us</h5>
                <p>If you have any questions about this Privacy Policy, please contact us at <code>support@socialpilotx.com</code>.</p>
            </div>
        </div>
    </div>
</div>
@endsection

