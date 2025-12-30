@extends('layouts.app')

@section('title', 'Terms and Conditions - HanapBuh.AI')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8 md:p-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Terms and Conditions</h1>
            <p class="text-gray-600 mb-8">Last updated: {{ date('F d, Y') }}</p>

            <div class="prose prose-lg max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Acceptance of Terms</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        By accessing and using HanapBuh.AI ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Description of Service</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        HanapBuh.AI is an AI-powered job matching platform that helps job seekers in the Philippines find employment opportunities. Our service includes:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Resume analysis using artificial intelligence</li>
                        <li>Personalized job recommendations based on your skills and experience</li>
                        <li>Job listings aggregated from various Philippine job boards</li>
                        <li>Profile management and career insights</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. User Accounts</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        To use certain features of our Service, you must register for an account. You agree to:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Provide accurate, current, and complete information during registration</li>
                        <li>Maintain and promptly update your account information</li>
                        <li>Maintain the security of your password and identification</li>
                        <li>Accept all responsibility for activities that occur under your account</li>
                        <li>Notify us immediately of any unauthorized use of your account</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. User Responsibilities</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        You are responsible for:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Ensuring the accuracy of information you provide, including your resume</li>
                        <li>Using the Service in compliance with all applicable laws and regulations</li>
                        <li>Not using the Service for any illegal or unauthorized purpose</li>
                        <li>Not attempting to gain unauthorized access to the Service or its systems</li>
                        <li>Not interfering with or disrupting the Service or servers connected to the Service</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Resume and Data Privacy</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        When you upload your resume to our platform:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>You grant us permission to analyze your resume using AI technology</li>
                        <li>We will process and store your resume data securely</li>
                        <li>Your personal information will be used solely for job matching purposes</li>
                        <li>We will not share your resume or personal data with third parties without your consent, except as required by law</li>
                        <li>You can request deletion of your data at any time</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Job Listings</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Our Service aggregates job listings from various sources. We:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Do not guarantee the accuracy, completeness, or availability of job listings</li>
                        <li>Are not responsible for the content of external job postings</li>
                        <li>Do not endorse any specific employer or job opportunity</li>
                        <li>Are not a party to any employment agreement between you and employers</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. AI Analysis and Recommendations</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Our AI-powered analysis and job recommendations are provided for informational purposes only. We:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Do not guarantee job placement or employment</li>
                        <li>Do not warrant the accuracy of AI-generated insights</li>
                        <li>Recommend that you verify all information independently</li>
                        <li>Are not liable for decisions made based on our recommendations</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Intellectual Property</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        The Service and its original content, features, and functionality are owned by HanapBuh.AI and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Limitation of Liability</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        To the maximum extent permitted by law, HanapBuh.AI shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your use of the Service.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Termination</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We may terminate or suspend your account and access to the Service immediately, without prior notice or liability, for any reason, including if you breach the Terms. Upon termination, your right to use the Service will cease immediately.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Changes to Terms</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Governing Law</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        These Terms shall be governed and construed in accordance with the laws of the Philippines, without regard to its conflict of law provisions. Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Contact Information</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        If you have any questions about these Terms and Conditions, please contact us at:
                    </p>
                    <div class="bg-gray-50 rounded-lg p-4 mt-4">
                        <p class="text-gray-700"><strong>Email:</strong> support@hanapbuhai.com</p>
                        <p class="text-gray-700"><strong>Website:</strong> <a href="{{ url('/') }}" class="text-[#1193d4] hover:underline">{{ url('/') }}</a></p>
                    </div>
                </section>

                <div class="mt-8 pt-8 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        By using HanapBuh.AI, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-[#1193d4] text-white font-semibold rounded-lg hover:bg-[#0f83bd] transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Registration
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

