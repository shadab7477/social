@extends('layouts.public')

@section('content')
<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-4">Terms of Service</h1>
            <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>

            <hr class="my-4">

            <div class="content lh-lg text-secondary">
                <h5 class="fw-bold text-dark mt-4">1. Acceptance of Terms</h5>
                <p>By accessing and using SocialPilotX, you agree to comply with and be bound by these Terms of Service and all applicable laws and regulations.</p>

                <h5 class="fw-bold text-dark mt-4">2. Social Media Platform Rules</h5>
                <p>When connecting social media profiles through our service, you agree to adhere to the respective platform community standards and developer policies, including Meta's Platform Terms, LinkedIn's Developer Agreement, and Google's Terms of Service.</p>

                <h5 class="fw-bold text-dark mt-4">3. Prohibited Content</h5>
                <p>You may not use our service to transmit spam, unlawful, abusive, or intellectual-property infringing material. We reserve the right to suspend any workspace that violates these standards.</p>

                <h5 class="fw-bold text-dark mt-4">4. Limitation of Liability</h5>
                <p>SocialPilotX provides scheduled publishing and inbox management on an "as is" and "as available" basis. We are not liable for any downtime caused by third-party social network API outages or policy enforcement actions.</p>

                <h5 class="fw-bold text-dark mt-4">5. Contact Information</h5>
                <p>For inquiries regarding these Terms, contact us at <code>legal@socialpilotx.com</code>.</p>
            </div>
        </div>
    </div>
</div>
@endsection

