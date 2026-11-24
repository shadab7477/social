@extends('layouts.public')

@section('content')
<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-4">User Data Deletion Instructions</h1>
            <p class="text-muted">According to Meta (Facebook & Instagram) Platform rules, you have the right to request the deletion of your personal data and authorized account information.</p>

            <hr class="my-4">

            <div class="content lh-lg text-secondary">
                <h5 class="fw-bold text-dark mt-4">Option 1: Disconnect and Delete Within SocialPilotX</h5>
                <p>You can instantly delete all stored access tokens, profile information, and cached social metrics directly from your SocialPilotX dashboard:</p>
                <ol>
                    <li>Log in to your <strong>SocialPilotX</strong> account.</li>
                    <li>Navigate to the <strong>Social Accounts</strong> section from the left navigation sidebar.</li>
                    <li>Find the connected Facebook or Instagram account you wish to delete.</li>
                    <li>Click the <strong>Delete / Disconnect</strong> (trash icon) button. All access tokens, credentials, and synced profile records will be permanently erased from our database immediately.</li>
                </ol>

                <h5 class="fw-bold text-dark mt-4">Option 2: Revoke Access Directly via Facebook / Meta</h5>
                <p>If you wish to remove SocialPilotX's access and delete data directly from Meta:</p>
                <ol>
                    <li>Go to your Facebook profile's <strong>Settings & Privacy > Settings</strong>.</li>
                    <li>Click on <strong>Business Integrations</strong> (or <strong>Apps and Websites</strong>).</li>
                    <li>Find <strong>socialconnect</strong> (or <strong>SocialPilotX</strong>) in the list of authorized apps.</li>
                    <li>Click the <strong>Remove</strong> button.</li>
                    <li>Check the box <em>"Delete posts, videos or events that socialconnect posted on your timeline"</em> if you wish, and confirm removal.</li>
                </ol>

                <h5 class="fw-bold text-dark mt-4">Option 3: Manual Full Data Deletion Request</h5>
                <p>If you would like us to purge all associated workspace logs, analytics snapshots, and user records associated with your Facebook User ID, please send an email to:</p>
                <div class="p-3 bg-light rounded-3 border my-3">
                    <strong>Email:</strong> <code>support@socialpilotx.com</code><br>
                    <strong>Subject:</strong> Facebook User Data Deletion Request<br>
                    <strong>Details to include:</strong> Your registered email address and the Facebook Page or User ID.
                </div>
                <p>Our team will process your deletion request and confirm the permanent purge of all associated data within 48 hours.</p>
            </div>
        </div>
    </div>
</div>
@endsection

