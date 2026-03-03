<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HanapBuh.AI - Career Matching Platform</title>
  <meta name="description" content="AI-powered job matching platform for the Philippines. Upload your resume and find the perfect career opportunities.">
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  {{-- Vite entry points --}}
  @vite(['resources/css/app.css','resources/js/app.js'])

  <style>
    html { scroll-behavior: smooth; }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    image.png
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .fade-in-up {
      animation: fadeInUp 0.6s ease-out forwards;
    }
    .fade-in {
      animation: fadeIn 0.8s ease-out forwards;
    }
    .delay-100 { animation-delay: 0.1s; opacity: 0; }
    .delay-200 { animation-delay: 0.2s; opacity: 0; }
    .delay-300 { animation-delay: 0.3s; opacity: 0; }
    .input-error {
      border-color: #ef4444 !important;
    }
    .input-error:focus {
      ring-color: #ef4444 !important;
    }
  </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
<div class="flex flex-col min-h-screen">

  {{-- Navbar --}}
  @include('partials.navbar')

  {{-- Main Content --}}
  <main class="flex-grow">
    
    {{-- Hero Section --}}
    <section id="home" class="relative h-[80vh] min-h-[500px] flex items-center justify-center text-center text-white bg-cover bg-center transition-all duration-500 ease-in-out" style='background-image: url("https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1920&auto=format&fit=crop");'>
      <div class="absolute inset-0 bg-gradient-to-b from-[#0d2b2b]/90 via-[#0d2b2b]/80 to-[#234950]/90"></div>
      <div class="relative z-10 px-6 py-12 max-w-3xl mx-auto">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight mb-4 fade-in-up">Unlock Your Career Potential with AI</h1>
        <p class="text-lg md:text-xl text-gray-200 mb-8 fade-in-up delay-100">Upload your resume and let our intelligent system find the perfect job opportunities tailored to your skills and experience in the Philippines.</p>
        <a href="{{ route('login') }}" class="inline-block px-8 py-3 text-lg font-bold rounded-lg bg-primary text-white hover:bg-[#0f83bd] transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 fade-in-up delay-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-transparent">Get Started Now</a>
      </div>
      <a href="#about" class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white/80 hover:text-white transition-colors animate-bounce" aria-label="Scroll to about section">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
        </svg>
      </a>
    </section>

    {{-- About Section --}}
    <section id="about" class="py-16 sm:py-24 bg-white dark:bg-background-dark transition-all duration-500 ease-in-out">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6 fade-in-up">About HanapBuh.AI</h2>
        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-8 fade-in-up delay-100">
          HanapBuh.AI leverages cutting-edge artificial intelligence to simplify your job search by matching your profile to roles that fit your goals and experience. We're revolutionizing how Filipinos find their dream careers.
        </p>
        <div class="mt-8 fade-in-up delay-200">
          <a href="#features" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-primary/80 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded">
            Learn More
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </a>
        </div>
      </div>
    </section>

    {{-- How It Works Section --}}
    <section id="features" class="py-16 sm:py-24 bg-gradient-to-b from-[#0d2b2b] to-[#234950]">
      <div class="container mx-auto px-6 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-4 fade-in-up">How It Works</h2>
        <p class="text-lg mb-12 fade-in-up delay-100">Our AI-driven platform simplifies your job search in three easy steps:</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white dark:bg-background-dark p-8 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 fade-in-up delay-100">
            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary/10 mb-6 mx-auto">
              <svg class="h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Upload Your Resume</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Securely upload your resume in PDF or DOCX format. Our system will instantly parse your information and extract key details.</p>
          </div>

          <div class="bg-white dark:bg-background-dark p-8 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 fade-in-up delay-200">
            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary/10 mb-6 mx-auto">
              <svg class="h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">AI Analysis &amp; Matching</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Our advanced AI analyzes your skills, experience, and preferences to find the best job matches from our extensive database.</p>
          </div>

          <div class="bg-white dark:bg-background-dark p-8 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 fade-in-up delay-300">
            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary/10 mb-6 mx-auto">
              <svg class="h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Get Job Recommendations</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Receive a curated list of job openings that perfectly align with your profile and career aspirations.</p>
          </div>
        </div>
      </div>
    </section>

    {{-- Contact Section --}}
    <section id="contact" class="py-16 sm:py-24 bg-background-light dark:bg-background-dark">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4 fade-in-up">Get in Touch</h2>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-12 fade-in-up delay-100">We'd love to hear from you. Here's how you can reach our team.</p>

        <!-- Team Members -->
        <div class="flex flex-col md:flex-row gap-6 justify-center mb-16">
          <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-lg w-full md:w-1/3 hover:transform hover:scale-105 transition-all duration-300 ease-in-out fade-in-up delay-100">
            <img src="https://i.pravatar.cc/100?img=1" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover ring-2 ring-primary/20" alt="Sophia Reyes - Co-Founder & Lead Developer">
            <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-1">Sophia Reyes</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Co-Founder &amp; Lead Developer</p>
            <div class="space-y-1">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <a href="mailto:sophia.reyes@email.com" class="hover:text-primary transition-colors">sophia.reyes@email.com</a>
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <a href="tel:+639171234567" class="hover:text-primary transition-colors">+63 917 123 4567</a>
              </p>
              <p class="text-sm">
                <a href="https://linkedin.com/in/sophiareyes" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-primary rounded">LinkedIn Profile</a>
              </p>
            </div>
          </div>

          <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-800 p-6 rounded-xl shadow-lg w-full md:w-1/3 hover:transform hover:scale-105 transition-all duration-300 ease-in-out fade-in-up delay-200">
            <img src="https://i.pravatar.cc/100?img=2" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover ring-2 ring-primary/20" alt="Miguel Santos - Co-Founder & AI Specialist">
            <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-1">Miguel Santos</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Co-Founder &amp; AI Specialist</p>
            <div class="space-y-1">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <a href="mailto:miguel.santos@email.com" class="hover:text-primary transition-colors">miguel.santos@email.com</a>
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <a href="tel:+639189876543" class="hover:text-primary transition-colors">+63 918 987 6543</a>
              </p>
              <p class="text-sm">
                <a href="https://linkedin.com/in/miguelsantos" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-primary rounded">LinkedIn Profile</a>
              </p>
            </div>
          </div>
        </div>

        <div class="max-w-xl mx-auto rounded-xl overflow-hidden shadow-xl fade-in-up delay-300">
          <div class="bg-gradient-to-b from-[#0d2b2b] to-[#234950] p-6 text-white">
            <p class="text-lg font-medium mb-2">Want to contact us? Send us a message!</p>
            <p class="text-sm text-white/80">We'll get back to you as soon as possible.</p>
          </div>

          @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 m-6 rounded">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                  <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-1">Please fix the following errors:</p>
                  <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          @endif

          @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 m-6 rounded">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
              </div>
            </div>
          @endif

          <form id="contactForm" action="{{ Route::has('contact.store') ? route('contact.store') : (Route::has('contact') ? route('contact') : '#') }}" method="POST" class="bg-gradient-to-b from-[#0d2b2b] to-[#234950] text-white border-t border-white/10 px-6 md:px-8 pb-8 pt-6">
            @csrf
            <div class="mb-4 text-left">
              <label for="contact_name" class="block mb-2 text-sm font-medium text-white/90">Your Name <span class="text-red-400">*</span></label>
              <input 
                id="contact_name"
                name="name" 
                type="text" 
                required
                value="{{ old('name') }}"
                class="w-full border border-white/20 bg-white/10 text-white placeholder-white/60 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#1193d4]/70 focus:border-transparent transition-all @error('name') input-error border-red-400 @enderror" 
                placeholder="Enter your full name"
                aria-invalid="@error('name') true @else false @enderror"
                aria-describedby="@error('name') name-error @enderror"
              />
              @error('name')
                <p id="name-error" class="mt-1 text-sm text-red-300" role="alert">{{ $message }}</p>
              @enderror
            </div>
            <div class="mb-4 text-left">
              <label for="contact_email" class="block mb-2 text-sm font-medium text-white/90">Your Email <span class="text-red-400">*</span></label>
              <input 
                id="contact_email"
                name="email" 
                type="email" 
                required
                value="{{ old('email') }}"
                class="w-full border border-white/20 bg-white/10 text-white placeholder-white/60 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#1193d4]/70 focus:border-transparent transition-all @error('email') input-error border-red-400 @enderror" 
                placeholder="Enter your email address"
                aria-invalid="@error('email') true @else false @enderror"
                aria-describedby="@error('email') email-error @enderror"
              />
              @error('email')
                <p id="email-error" class="mt-1 text-sm text-red-300" role="alert">{{ $message }}</p>
              @enderror
            </div>
            <div class="mb-6 text-left">
              <label for="contact_message" class="block mb-2 text-sm font-medium text-white/90">Message <span class="text-red-400">*</span></label>
              <textarea 
                id="contact_message"
                name="message" 
                rows="4" 
                required
                class="w-full border border-white/20 bg-white/10 text-white placeholder-white/60 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#1193d4]/70 focus:border-transparent transition-all resize-none @error('message') input-error border-red-400 @enderror" 
                placeholder="Enter your message here..."
                aria-invalid="@error('message') true @else false @enderror"
                aria-describedby="@error('message') message-error @enderror"
              >{{ old('message') }}</textarea>
              @error('message')
                <p id="message-error" class="mt-1 text-sm text-red-300" role="alert">{{ $message }}</p>
              @enderror
            </div>
            <button 
              type="submit" 
              id="submitBtn"
              class="w-full inline-flex items-center justify-center gap-2 bg-[#1193d4] text-white px-6 py-3 rounded-lg hover:bg-[#0f83bd] transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-[#1193d4] focus:ring-offset-2 focus:ring-offset-[#234950] disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]">
              <span id="submitText">Send Message</span>
              <svg id="submitSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </form>
        </div>
      </div>
    </section>
  </main>

  {{-- Include shared footer partial --}}
  @include('partials.footer')
  @stack('scripts')

  {{-- Contact Form Script --}}
  <script>
    // Contact form submission handling
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    if (contactForm && submitBtn) {
      contactForm.addEventListener('submit', function(e) {
        // Only show loading state if form action is valid
        if (contactForm.action && contactForm.action !== '#' && contactForm.action !== window.location.href) {
          submitBtn.disabled = true;
          submitText.textContent = 'Sending...';
          submitSpinner.classList.remove('hidden');
        }
      });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
          e.preventDefault();
          const target = document.querySelector(href);
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        }
      });
    });

    // Intersection Observer for fade-in animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // Observe all fade-in elements
    document.querySelectorAll('.fade-in-up, .fade-in').forEach(el => {
      observer.observe(el);
    });
  </script>
</div>
</body>
</html>
