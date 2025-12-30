<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>HanapBuh.AI - Register</title>
  <meta name="description" content="Create your account on HanapBuh.AI and start your journey to finding the perfect job match in the Philippines.">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#1193d4",
            "background-light": "#f6f7f8",
            "background-dark": "#101c22",
          },
          fontFamily: { display: ["Poppins", "sans-serif"] },
          borderRadius: { xl: "0.75rem" },
        },
      },
    }
  </script>
  <style>
    body { font-family: 'Poppins', sans-serif; }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-in { animation: fadeIn 0.5s ease-out; }
    .input-error { border-color: #ef4444 !important; }
    .input-error:focus { ring-color: #ef4444 !important; }
    
    /* Smooth focus transitions */
    input:focus, textarea:focus, select:focus {
      transition: all 0.2s ease-in-out;
    }
    
    /* Checkbox styling */
    input[type="checkbox"] {
      accent-color: #1193d4;
    }
    
    /* Form field hover effects */
    input:hover:not(:disabled), textarea:hover:not(:disabled) {
      border-color: #1193d4;
    }
    
    /* Modal backdrop blur */
    .modal-backdrop {
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }
    
    /* Modal animation */
    @keyframes modalFadeIn {
      from {
        opacity: 0;
        transform: scale(0.95);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }
    
    .modal-content {
      animation: modalFadeIn 0.3s ease-out;
    }
    
    /* Scroll indicator */
    .scroll-indicator {
      position: sticky;
      bottom: 0;
      background: linear-gradient(to top, rgba(255,255,255,1) 0%, rgba(255,255,255,0.8) 50%, transparent 100%);
      padding: 20px 0 10px;
      text-align: center;
      pointer-events: none;
      z-index: 10;
    }
    
    .dark .scroll-indicator {
      background: linear-gradient(to top, rgba(15,22,26,1) 0%, rgba(15,22,26,0.8) 50%, transparent 100%);
    }
  </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display h-screen overflow-y-auto">
  <div class="flex min-h-full">

    <!-- LEFT: Image Carousel -->
    <section class="relative w-1/2 hidden md:flex overflow-hidden">
      <div id="carouselSlides" class="absolute inset-0"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-black/25"></div>

      <div class="relative z-10 flex flex-col justify-end w-full p-10 pb-20">
        <div class="max-w-lg fade-in">
          <h1 class="text-4xl lg:text-5xl font-extrabold text-white leading-tight drop-shadow-lg">
            Join <span class="text-primary">HanapBuh.AI</span> today
          </h1>
          <p class="mt-4 text-white/90 text-lg leading-relaxed">
            One profile. Smarter matches. Faster applications. Start your career journey with AI-powered job matching.
          </p>
        </div>
        <div class="flex items-center gap-2 mt-6" id="carouselDots" aria-label="Slide controls" role="tablist"></div>
      </div>
    </section>

    <!-- RIGHT: Register Form -->
    <main class="w-full md:w-1/2 flex items-center justify-center p-4 md:p-8 overflow-y-auto">
      <div class="w-full max-w-md bg-white dark:bg-[#0f161a] rounded-2xl shadow-xl p-6 md:p-8 fade-in">
        <div class="mb-8 text-center">
          <div class="flex items-center justify-center gap-2 mb-4">
            <div class="w-12 h-12 bg-[#1193d4] rounded-full flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M12 2a10 10 0 100 20 10 10 0 000-20zm-1 13h2v2h-2v-2zm0-8h2v6h-2V7z"/>
              </svg>
            </div>
            <span class="text-2xl font-bold text-gray-900 dark:text-white">HanapBuh.AI</span>
          </div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create your account</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">Join thousands of job seekers finding their dream career</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
          <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-1">Please fix the following errors:</p>
                <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        @endif

        @if (session('status'))
          <div class="mb-4 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <p class="text-sm text-green-800 dark:text-green-300">{{ session('status') }}</p>
            </div>
          </div>
        @endif

        <form id="registerForm" action="{{ route('register') }}" method="POST" class="space-y-5">
          @csrf

          <!-- Name Fields -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name <span class="text-red-500">*</span></label>
              <input id="first_name" name="first_name" type="text" required
                     value="{{ old('first_name') }}"
                     class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 transition-all duration-200 hover:border-[#1193d4]/50 @error('first_name') input-error border-red-500 @enderror"
                     placeholder="Juan"
                     autocomplete="given-name"
                     aria-invalid="@error('first_name') true @else false @enderror"
                     aria-describedby="@error('first_name') first_name-error @enderror">
              @error('first_name')
                <p id="first_name-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name <span class="text-red-500">*</span></label>
              <input id="last_name" name="last_name" type="text" required
                     value="{{ old('last_name') }}"
                     class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 transition-all duration-200 hover:border-[#1193d4]/50 @error('last_name') input-error border-red-500 @enderror"
                     placeholder="Dela Cruz"
                     autocomplete="family-name"
                     aria-invalid="@error('last_name') true @else false @enderror"
                     aria-describedby="@error('last_name') last_name-error @enderror">
              @error('last_name')
                <p id="last_name-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email address <span class="text-red-500">*</span></label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 transition-all duration-200 hover:border-[#1193d4]/50 @error('email') input-error border-red-500 @enderror"
                   placeholder="you@example.com"
                   aria-invalid="@error('email') true @else false @enderror"
                   aria-describedby="@error('email') email-error @enderror">
            @error('email')
              <p id="email-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="contact_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Number</label>
            <input id="contact_number" name="contact_number" type="tel" 
                   value="{{ old('contact_number') }}"
                   class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 transition-all duration-200 hover:border-[#1193d4]/50 @error('contact_number') input-error border-red-500 @enderror"
                   placeholder="+63 912 345 6789"
                   autocomplete="tel"
                   aria-invalid="@error('contact_number') true @else false @enderror"
                   aria-describedby="@error('contact_number') contact_number-error @enderror">
            @error('contact_number')
              <p id="contact_number-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
            <input id="address" name="address" type="text" 
                   value="{{ old('address') }}"
                   class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 transition-all duration-200 hover:border-[#1193d4]/50 @error('address') input-error border-red-500 @enderror"
                   placeholder="City, Province"
                   autocomplete="street-address"
                   aria-invalid="@error('address') true @else false @enderror"
                   aria-describedby="@error('address') address-error @enderror">
            @error('address')
              <p id="address-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input id="password" name="password" type="password" autocomplete="new-password" required
                     class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 pr-10 transition-all duration-200 hover:border-[#1193d4]/50 @error('password') input-error border-red-500 @enderror"
                     placeholder="Create a strong password"
                     aria-invalid="@error('password') true @else false @enderror"
                     aria-describedby="@error('password') password-error @enderror">
              <button type="button" id="togglePassword" 
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#1193d4] rounded p-1"
                      aria-label="Toggle password visibility">
                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg id="eyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
            @error('password')
              <p id="password-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                     class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 pr-10 transition-all duration-200 hover:border-[#1193d4]/50"
                     placeholder="Re-enter your password">
              <button type="button" id="togglePasswordConfirmation" 
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary rounded p-1"
                      aria-label="Toggle password visibility">
                <svg id="eyeIconConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg id="eyeOffIconConfirmation" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Terms and Conditions Checkbox -->
          <div class="flex items-start gap-3">
            <input type="checkbox" id="terms" name="terms" value="1" 
                   class="mt-0.5 w-5 h-5 rounded border-2 border-gray-400 text-[#1193d4] focus:ring-2 focus:ring-[#1193d4] cursor-pointer flex-shrink-0 @error('terms') border-red-500 @enderror"
                   required
                   aria-invalid="@error('terms') true @else false @enderror"
                   aria-describedby="@error('terms') terms-error @enderror">
            <div class="flex-1">
              <label for="terms" class="text-sm text-gray-800 dark:text-gray-200 cursor-pointer leading-relaxed inline-block">
                <span class="font-medium">I agree to the</span> 
                <span class="text-red-500 font-bold">*</span>
              </label>
              <button type="button" id="openTermsModal" class="text-[#1193d4] hover:text-[#0f83bd] hover:underline font-semibold mx-1 transition-colors cursor-pointer bg-transparent border-none p-0" style="pointer-events: auto;">Terms and Conditions</button>
            </div>
          </div>
          @error('terms')
            <p id="terms-error" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1" role="alert">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              </svg>
              {{ $message }}
            </p>
          @enderror

          <button type="submit" id="submitBtn"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1193d4] text-white font-semibold py-3.5 px-6 hover:bg-[#0f83bd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1193d4] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            <span id="submitText">Create Account</span>
            <svg id="submitSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
          Already have an account?
          <a href="{{ route('login') }}" class="text-primary hover:underline">Sign in</a>
        </p>
      </div>
    </main>
  </div>

  <!-- Terms and Conditions Modal -->
  <div id="termsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop with blur -->
    <div class="fixed inset-0 bg-black/50 modal-backdrop transition-opacity" id="modalBackdrop"></div>
    
    <!-- Modal container -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative bg-white dark:bg-[#0f161a] rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col modal-content">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="modal-title">Terms and Conditions</h3>
          <button type="button" id="closeTermsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <!-- Modal body with scrollable content -->
        <div class="flex-1 overflow-y-auto p-6" id="termsContent">
          <p class="text-gray-600 dark:text-gray-400 mb-6">Last updated: {{ date('F d, Y') }}</p>
          
          <div class="space-y-8">
            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">1. Acceptance of Terms</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                By accessing and using HanapBuh.AI ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2. Description of Service</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                HanapBuh.AI is an AI-powered job matching platform that helps job seekers in the Philippines find employment opportunities. Our service includes:
              </p>
              <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2 ml-4">
                <li>Resume analysis using artificial intelligence</li>
                <li>Personalized job recommendations based on your skills and experience</li>
                <li>Job listings aggregated from various Philippine job boards</li>
                <li>Profile management and career insights</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">3. User Accounts</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                To use certain features of our Service, you must register for an account. You agree to:
              </p>
              <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2 ml-4">
                <li>Provide accurate, current, and complete information during registration</li>
                <li>Maintain and promptly update your account information</li>
                <li>Maintain the security of your password and identification</li>
                <li>Accept all responsibility for activities that occur under your account</li>
                <li>Notify us immediately of any unauthorized use of your account</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">4. User Responsibilities</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                You are responsible for:
              </p>
              <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2 ml-4">
                <li>Ensuring the accuracy of information you provide, including your resume</li>
                <li>Using the Service in compliance with all applicable laws and regulations</li>
                <li>Not using the Service for any illegal or unauthorized purpose</li>
                <li>Not attempting to gain unauthorized access to the Service or its systems</li>
                <li>Not interfering with or disrupting the Service or servers connected to the Service</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">5. Resume and Data Privacy</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                When you upload your resume to our platform:
              </p>
              <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2 ml-4">
                <li>You grant us permission to analyze your resume using AI technology</li>
                <li>We will process and store your resume data securely</li>
                <li>Your personal information will be used solely for job matching purposes</li>
                <li>We will not share your resume or personal data with third parties without your consent, except as required by law</li>
                <li>You can request deletion of your data at any time</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">6. Job Listings</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                Our Service aggregates job listings from various sources. We:
              </p>
              <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2 ml-4">
                <li>Do not guarantee the accuracy, completeness, or availability of job listings</li>
                <li>Are not responsible for the content of external job postings</li>
                <li>Do not endorse any specific employer or job opportunity</li>
                <li>Are not a party to any employment agreement between you and employers</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">7. AI Analysis and Recommendations</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                Our AI-powered analysis and job recommendations are provided for informational purposes only. We:
              </p>
              <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-2 ml-4">
                <li>Do not guarantee job placement or employment</li>
                <li>Do not warrant the accuracy of AI-generated insights</li>
                <li>Recommend that you verify all information independently</li>
                <li>Are not liable for decisions made based on our recommendations</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">8. Intellectual Property</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                The Service and its original content, features, and functionality are owned by HanapBuh.AI and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">9. Limitation of Liability</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                To the maximum extent permitted by law, HanapBuh.AI shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your use of the Service.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">10. Termination</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                We may terminate or suspend your account and access to the Service immediately, without prior notice or liability, for any reason, including if you breach the Terms. Upon termination, your right to use the Service will cease immediately.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">11. Changes to Terms</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">12. Governing Law</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                These Terms shall be governed and construed in accordance with the laws of the Philippines, without regard to its conflict of law provisions. Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">13. Contact Information</h2>
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                If you have any questions about these Terms and Conditions, please contact us at:
              </p>
              <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <p class="text-gray-700 dark:text-gray-300"><strong>Email:</strong> support@hanapbuhai.com</p>
                <p class="text-gray-700 dark:text-gray-300"><strong>Website:</strong> <a href="{{ url('/') }}" class="text-[#1193d4] hover:underline">{{ url('/') }}</a></p>
              </div>
            </section>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                By using HanapBuh.AI, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.
              </p>
            </div>
          </div>
          
          <!-- Scroll indicator -->
          <div class="scroll-indicator" id="scrollIndicator">
            <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Please scroll to the bottom to continue</p>
          </div>
        </div>
        
        <!-- Modal footer with accept button -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">You must read and scroll to the bottom to accept</p>
            <button type="button" id="acceptTerms" class="px-6 py-2.5 bg-[#1193d4] text-white font-semibold rounded-lg hover:bg-[#0f83bd] transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
              I Accept
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Carousel logic -->
  <script>
    const images = [
      { url: "https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1600&auto=format&fit=crop", alt: "Team collaboration" },
      { url: "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1600&auto=format&fit=crop", alt: "Modern workspace" },
      { url: "https://images.unsplash.com/photo-1516251193007-45ef944ab0c6?q=80&w=1600&auto=format&fit=crop", alt: "Laptop and workspace" },
    ];

    const slidesRoot = document.getElementById('carouselSlides');
    const dotsRoot = document.getElementById('carouselDots');
    let current = 0;
    let carouselInterval;

    function renderSlides() {
      slidesRoot.innerHTML = images.map((img, i) => `
        <div class="absolute inset-0 transition-opacity duration-700 ease-in-out ${i === current ? 'opacity-100 z-10' : 'opacity-0 z-0'}" role="tabpanel" aria-hidden="${i !== current}">
          <img src="${img.url}" alt="${img.alt}" class="w-full h-full object-cover" draggable="false" loading="lazy"/>
        </div>
      `).join('');

      dotsRoot.innerHTML = images.map((_, i) => `
        <button type="button" 
                class="h-2.5 w-2.5 rounded-full transition-all duration-300 ${i === current ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75'}" 
                aria-label="Go to slide ${i + 1}"
                role="tab"
                aria-selected="${i === current}"
                onclick="goToSlide(${i})"></button>
      `).join('');
    }

    function next() {
      current = (current + 1) % images.length;
      renderSlides();
    }

    function goToSlide(index) {
      current = index;
      renderSlides();
      clearInterval(carouselInterval);
      carouselInterval = setInterval(next, 4000);
    }

    // Password visibility toggles
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');

    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const eyeIconConfirmation = document.getElementById('eyeIconConfirmation');
    const eyeOffIconConfirmation = document.getElementById('eyeOffIconConfirmation');

    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.classList.toggle('hidden');
        eyeOffIcon.classList.toggle('hidden');
      });
    }

    if (togglePasswordConfirmation && passwordConfirmationInput) {
      togglePasswordConfirmation.addEventListener('click', () => {
        const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmationInput.setAttribute('type', type);
        eyeIconConfirmation.classList.toggle('hidden');
        eyeOffIconConfirmation.classList.toggle('hidden');
      });
    }

    // Form submission loading state
    const registerForm = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    if (registerForm && submitBtn) {
      registerForm.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitText.textContent = 'Creating Account...';
        submitSpinner.classList.remove('hidden');
      });
    }

    // Initialize carousel
    renderSlides();
    carouselInterval = setInterval(next, 4000);

    // Pause carousel on hover
    const carouselSection = document.querySelector('section');
    if (carouselSection) {
      carouselSection.addEventListener('mouseenter', () => clearInterval(carouselInterval));
      carouselSection.addEventListener('mouseleave', () => {
        carouselInterval = setInterval(next, 4000);
      });
    }

    // Terms and Conditions Modal
    const termsModal = document.getElementById('termsModal');
    const openTermsModalBtn = document.getElementById('openTermsModal');
    const closeTermsModalBtn = document.getElementById('closeTermsModal');
    const acceptTermsBtn = document.getElementById('acceptTerms');
    const termsContent = document.getElementById('termsContent');
    const scrollIndicator = document.getElementById('scrollIndicator');
    const termsCheckbox = document.getElementById('terms');
    const modalBackdrop = document.getElementById('modalBackdrop');

    function checkScrollPosition() {
      if (!termsContent) return;
      const scrollTop = termsContent.scrollTop;
      const scrollHeight = termsContent.scrollHeight;
      const clientHeight = termsContent.clientHeight;
      
      // Check if scrolled to bottom (with 50px tolerance)
      const isAtBottom = scrollTop + clientHeight >= scrollHeight - 50;
      
      if (isAtBottom) {
        // Enable accept button and close button
        if (acceptTermsBtn) acceptTermsBtn.disabled = false;
        if (closeTermsModalBtn) closeTermsModalBtn.disabled = false;
        if (scrollIndicator) scrollIndicator.style.display = 'none';
      } else {
        // Disable accept button and close button
        if (acceptTermsBtn) acceptTermsBtn.disabled = true;
        if (closeTermsModalBtn) closeTermsModalBtn.disabled = true;
        if (scrollIndicator) scrollIndicator.style.display = 'block';
      }
    }

    // Open modal
    if (openTermsModalBtn && termsModal) {
      openTermsModalBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        termsModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (termsContent) {
          termsContent.scrollTop = 0;
          checkScrollPosition();
        }
      });
    }

    // Close modal
    function closeModal() {
      if (termsModal) {
        termsModal.classList.add('hidden');
        document.body.style.overflow = '';
        if (termsContent) {
          termsContent.scrollTop = 0;
          checkScrollPosition();
        }
      }
    }

    if (closeTermsModalBtn) {
      closeTermsModalBtn.addEventListener('click', closeModal);
    }

    // Close on backdrop click (only if scrolled to bottom)
    if (modalBackdrop) {
      modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop && acceptTermsBtn && !acceptTermsBtn.disabled) {
          closeModal();
        }
      });
    }

    // Close on Escape key (only if scrolled to bottom)
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && termsModal && !termsModal.classList.contains('hidden') && acceptTermsBtn && !acceptTermsBtn.disabled) {
        closeModal();
      }
    });

    // Check scroll position on scroll
    if (termsContent) {
      termsContent.addEventListener('scroll', checkScrollPosition);
    }

    // Accept terms
    if (acceptTermsBtn) {
      acceptTermsBtn.addEventListener('click', () => {
        if (termsCheckbox) {
          termsCheckbox.checked = true;
        }
        closeModal();
      });
    }
  </script>
</body>
</html>
