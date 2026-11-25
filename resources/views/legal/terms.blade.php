@extends('layouts.public', ['title' => 'Terms of Service — PostSync SaaS'])

@section('content')
<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="fw-bold mb-2">Terms of Service for PostSync SaaS</h1>
            <p class="text-muted">Application: <strong>PostSync SaaS</strong> | Domain: <code>postsync.cloud</code> | Last updated: {{ date('F d, Y') }}</p>

            <hr class="my-4">

            <div class="content lh-lg text-secondary">
                <h5 class="fw-bold text-dark mt-4">1. Agreement to Terms</h5>
                <p>These Terms of Service constitute a legally binding agreement between you and <strong>PostSync SaaS</strong> (operated via <a href="https://postsync.cloud">https://postsync.cloud</a>). By creating an account, connecting social accounts, or using our services, you agree to be bound by these Terms.</p>

                <h5 class="fw-bold text-dark mt-4">2. Account Responsibility & Multi-Tenancy</h5>
                <p>You are responsible for maintaining the confidentiality of your workspace login credentials and for all activities that occur under your account. You must notify us immediately of any unauthorized use or security breach.</p>

                <h5 class="fw-bold text-dark mt-4">3. Compliance with Social Network Policies & Google APIs</h5>
                <p>When connecting social media accounts to PostSync SaaS, you agree to comply with all applicable third-party platform terms and community guidelines, including:</p>
                <ul>
                    <li><a href="https://developers.facebook.com/terms/" target="_blank">Meta Platform Terms & Developer Policies</a></li>
                    <li><a href="https://help.instagram.com/477434105621119" target="_blank">Instagram Community Guidelines</a></li>
                    <li><a href="https://www.linkedin.com/legal/user-agreement" target="_blank">LinkedIn User Agreement</a></li>
                    <li><a href="https://www.youtube.com/t/terms" target="_blank">YouTube Terms of Service</a></li>
                    <li><a href="https://developers.google.com/terms" target="_blank">Google APIs Terms of Service</a> and <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank">Google API Services User Data Policy</a></li>
                </ul>
                <p>PostSync SaaS is not responsible for any account restrictions, suspensions, or penalties imposed by external social networks resulting from violations of their respective terms.</p>

                <h5 class="fw-bold text-dark mt-4">4. Acceptable Use Policy & AI NCII Prohibition</h5>
                <p>You agree not to use PostSync SaaS to:</p>
                <ul>
                    <li>Transmit spam, unauthorized advertising, or unsolicited promotional material.</li>
                    <li>Publish hate speech, violent content, copyrighted material without authorization, or illegal content.</li>
                    <li><strong>Generate, create, upload, host, process, or distribute Non-Consensual Intimate Imagery (AI NCII)</strong> or any illicit, abusive synthetic media in strict compliance with the Google APIs Terms of Service.</li>
                    <li>Attempt to bypass rate limits, probe system vulnerabilities, or reverse-engineer our proprietary features.</li>
                </ul>

                <h5 class="fw-bold text-dark mt-4">5. Intellectual Property</h5>
                <p>You retain full ownership of all content, images, videos, and texts you upload or schedule through PostSync SaaS. We claim no intellectual property rights over your social media materials.</p>

                <h5 class="fw-bold text-dark mt-4">6. Limitation of Liability</h5>
                <p>PostSync SaaS is provided on an "as is" and "as available" basis. To the maximum extent permitted by law, PostSync SaaS shall not be liable for any indirect, incidental, or consequential damages resulting from platform API downtime, rate limit throttling, or network outages.</p>

                <h5 class="fw-bold text-dark mt-4">7. Termination</h5>
                <p>You may cancel your workspace subscription or delete your account at any time. We reserve the right to suspend or terminate accounts that violate our Acceptable Use Policy.</p>

                <h5 class="fw-bold text-dark mt-4">8. Contact Us</h5>
                <p>For questions or notices regarding these Terms, please contact:</p>
                <div class="p-3 bg-light rounded-3 border my-3">
                    <strong>PostSync SaaS Legal & Compliance Team</strong><br>
                    <strong>Email:</strong> <code>techshad04@gmail.com</code> (or <code>support@postsync.cloud</code>)<br>
                    <strong>Website:</strong> <a href="https://postsync.cloud">https://postsync.cloud</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
