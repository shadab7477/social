@extends('layouts.public')

@section('content')
<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-2">User Data Deletion Instructions</h1>
            <p class="text-muted">PostSync (<a href="https://postsync.cloud">https://postsync.cloud</a>) — Meta Platform Compliance</p>

            <hr class="my-4">

            <div class="content lh-lg text-secondary">
                <p>According to the <strong>Meta (Facebook & Instagram) Platform Rules</strong>, users have the right to request the deletion of their personal data and authorized account information collected through the PostSync platform.</p>

                <h5 class="fw-bold text-dark mt-4">Option 1: Automated Deletion via PostSync Dashboard (Instant)</h5>
                <p>You can instantly delete all stored access tokens, connected account identities, and synchronized data directly inside PostSync:</p>
                <ol>
                    <li>Log in to your PostSync account at <a href="https://postsync.cloud/login">https://postsync.cloud/login</a>.</li>
                    <li>Click on <strong>Social Accounts</strong> in the left sidebar menu (<a href="https://postsync.cloud/social-accounts">https://postsync.cloud/social-accounts</a>).</li>
                    <li>Locate the Facebook Page or Instagram Account you wish to remove.</li>
                    <li>Click the <strong>Delete (Trash)</strong> button next to the account.</li>
                    <li>All encrypted OAuth tokens, cached profile data, and associated sync records will be immediately and permanently purged from our database.</li>
                </ol>

                <h5 class="fw-bold text-dark mt-4">Option 2: Revoke Access via Facebook Account Settings (Automated Callback)</h5>
                <p>If you prefer to remove PostSync's permissions directly through Facebook:</p>
                <ol>
                    <li>Log in to your Facebook profile and navigate to <strong>Settings & Privacy > Settings</strong>.</li>
                    <li>Scroll down to the <strong>Permissions</strong> section and click on <strong>Apps and Websites</strong> (or <strong>Business Integrations</strong>).</li>
                    <li>Find <strong>socialconnect</strong> (or <strong>PostSync</strong>) in your active apps list.</li>
                    <li>Click <strong>Remove</strong>.</li>
                    <li>Optionally check <em>"Delete posts, videos or events that PostSync posted on your timeline"</em> and confirm the removal.</li>
                </ol>
                <p>Facebook will automatically trigger our secure data deletion endpoint (<code>https://postsync.cloud/webhooks/meta/data-deletion</code>), which automatically purges all your data and generates a confirmation tracking URL.</p>

                <h5 class="fw-bold text-dark mt-4">Option 3: Manual Data Deletion Request</h5>
                <p>If you have deleted your account or cannot access the dashboard, you may submit a manual data deletion request:</p>
                <div class="p-3 bg-light rounded-3 border my-3">
                    <strong>Email:</strong> <code>support@postsync.cloud</code><br>
                    <strong>Subject:</strong> Meta User Data Deletion Request<br>
                    <strong>Required details:</strong> Your registered email address and your Facebook Page Name or Page ID.<br>
                    <strong>Processing time:</strong> Your request will be fulfilled within 24 to 48 hours, and a confirmation code will be sent to your email.
                </div>

                <h5 class="fw-bold text-dark mt-4">Check Status of a Previous Deletion Request</h5>
                <p>If you have a confirmation code from a previous deletion request, you can check its status anytime at our <a href="{{ route('legal.deletion.status') }}">Data Deletion Status Page</a>.</p>
            </div>
        </div>
    </div>
</div>
@endsection
