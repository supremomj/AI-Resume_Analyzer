@extends('layouts.app')

@section('title', 'About Us - HanapBuh.AI')

@section('content')
<div class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 font-display">
    <main class="flex-grow">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 md:py-16">
            <div class="max-w-4xl mx-auto">
                <!-- Hero Section -->
                <div class="text-center mb-12 sm:mb-16">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">About HanapBuh.AI</h1>
                    <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                        We are a revolutionary platform designed to transform the job search experience in the Philippines.
                    </p>
                </div>

                <!-- Mission & Vision -->
                <div class="space-y-12 sm:space-y-16">
                    <!-- Mission -->
                    <div class="grid md:grid-cols-2 gap-8 sm:gap-12 items-center">
                        <div class="order-2 md:order-1">
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">Our Mission</h2>
                            <p class="text-gray-600 leading-relaxed text-sm sm:text-base">
                                Our mission is to empower Filipino professionals to find their ideal career paths. We aim to bridge the gap between talent and opportunity, ensuring that every individual has access to fulfilling and rewarding employment.
                            </p>
                        </div>
                        <div class="order-1 md:order-2 p-4 rounded-xl bg-[#1193d4]/10">
                            <img
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMMDJX2-B35CQ8HpgbAsmdzDnE9G1rI_Gbs5YALRR1083thUF7TJLAID7mQvqkNGBtX1wN9gpYK8e6rZjfp8SEuolq0sDkFiBZz7aSPxYKqXkR3h59cF15agGzqn9nQwRv9pvMrF9tRoq2JTVtIkwVpzaRtqNxnSWQff5H-9HYSOlxx8pdkgJuBf5MxoREEdtQg8BPKtOutJDeRz4eXoi0rG7hePhXj7Gq3IRW0X_KI6g1lKl_l0npdWh1mm4YJPdZNnSx8IMsXWj4"
                                alt="Diverse team collaborating"
                                class="rounded-lg shadow-lg w-full h-auto"
                            />
                        </div>
                    </div>

                    <!-- Vision -->
                    <div class="grid md:grid-cols-2 gap-8 sm:gap-12 items-center">
                        <div class="order-2 md:order-2">
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">Our Vision</h2>
                            <p class="text-gray-600 leading-relaxed text-sm sm:text-base">
                                To be the leading AI-powered career platform in the Philippines, recognized for its accuracy, user-centric design, and positive impact on the job market.
                            </p>
                        </div>
                        <div class="order-1 md:order-1 p-4 rounded-xl bg-[#1193d4]/10">
                            <img
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAuT4DwnIHsKGR8stMF1qDkRK6QZpvBmOgAuh5JDAOTR_43c2w0_K0eVivpBdEBFRy2DCpZsWH3lUc5a7OvmnC0SrEfNFUBLl4ioISE37m8SrlsaxWiuHUs7qiEDcnXPRPZBNrzohQ3z_59maMXYSoKsE-F8YiChcQYQw4hs4rPtXJMG4jsJ3_JpVFN5sS-Kl_4wCZYM4gUEQ9ZjQ-4_wS9xmnjexxH9M13LVLXRpp6OmVoPeg8wSFMjyboN_cv4Zu89Quxz27uRUUz"
                                alt="Man looking at a futuristic interface"
                                class="rounded-lg shadow-lg w-full h-auto"
                            />
                        </div>
                    </div>
                </div>

                <!-- Values -->
                <div class="mt-12 sm:mt-16 md:mt-20">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-8 sm:mb-10">Our Values</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
                        <div class="p-6 rounded-xl bg-white border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-[#1193d4]/10 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Innovation</h3>
                            <p class="text-gray-600 text-sm">Continuously improving our AI algorithms and platform features.</p>
                        </div>
                        <div class="p-6 rounded-xl bg-white border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-[#1193d4]/10 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Accuracy</h3>
                            <p class="text-gray-600 text-sm">Providing precise and relevant job recommendations.</p>
                        </div>
                        <div class="p-6 rounded-xl bg-white border border-gray-200 shadow-sm hover:shadow-md transition-shadow sm:col-span-2 md:col-span-1">
                            <div class="w-12 h-12 bg-[#1193d4]/10 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">User Empowerment</h3>
                            <p class="text-gray-600 text-sm">Putting the user at the center of our design and services.</p>
                        </div>
                    </div>
                </div>

                <!-- Team Section -->
                <div class="mt-12 sm:mt-16 md:mt-20">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-4">Meet the Team</h2>
                    <p class="text-center text-base sm:text-lg text-gray-600 max-w-2xl mx-auto mb-8 sm:mb-12">
                        Our team comprises experienced professionals in AI, recruitment, and technology, all dedicated to making a difference in the lives of Filipino job seekers.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 max-w-2xl mx-auto">
                        <div class="text-center p-6 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full mx-auto mb-4 bg-[#1193d4]/10 flex items-center justify-center ring-4 ring-[#1193d4]/20">
                                <span class="text-3xl sm:text-4xl font-bold text-[#1193d4]">K</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Kenneth Aricheta</h3>
                            <p class="text-[#1193d4] font-medium text-sm sm:text-base">Co-Founder & Developer</p>
                        </div>

                        <div class="text-center p-6 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full mx-auto mb-4 bg-[#1193d4]/10 flex items-center justify-center ring-4 ring-[#1193d4]/20">
                                <span class="text-3xl sm:text-4xl font-bold text-[#1193d4]">M</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Mario John Santiano</h3>
                            <p class="text-[#1193d4] font-medium text-sm sm:text-base">Co-Founder & Developer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
