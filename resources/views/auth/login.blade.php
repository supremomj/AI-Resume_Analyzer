<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>HanapBuh.AI - Login</title>
  <meta name="description" content="Sign in to your HanapBuh.AI account and continue your job search journey with AI-powered job matching.">
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

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
    
    /* Form field hover effects */
    input:hover:not(:disabled), textarea:hover:not(:disabled) {
      border-color: #1193d4;
    }
  </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display h-screen">
  <!-- Full screen split -->
  <div class="flex h-full">

    <!-- LEFT: Image Carousel -->
    <section class="relative w-1/2 hidden md:flex overflow-hidden">
      <!-- Slides -->
      <div id="carouselSlides" class="absolute inset-0"></div>

      <!-- Overlay for readability -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-black/25"></div>

      <!-- Caption / Branding -->
      <div class="relative z-10 flex flex-col justify-end w-full p-10 pb-20">
        <div class="max-w-lg">
          <h1 class="text-4xl lg:text-5xl font-extrabold text-white leading-tight drop-shadow">
            Welcome to <span class="text-primary">HanapBuh.AI</span>
          </h1>
          <p class="mt-4 text-white/90 text-lg">
            Smart matching for smarter careers. Sign in to continue your journey.
          </p>
        </div>

        <!-- Dots -->
        <div class="flex items-center gap-2 mt-6" id="carouselDots" aria-label="Slide controls"></div>
      </div>
    </section>

    <!-- RIGHT: Login Form -->
    <main class="w-full md:w-1/2 flex items-center justify-center p-8">
      <div class="w-full max-w-md bg-white dark:bg-[#0f161a] rounded-2xl shadow-xl p-8">
        <div class="mb-8 text-center">
          <div class="flex items-center justify-center gap-2 mb-4">
            @include('partials.logo', ['class' => 'h-10 sm:h-12 w-auto'])
            <span class="text-2xl font-bold text-gray-900 dark:text-white">HanapBuh.AI</span>
          </div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Sign in to your account</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">Welcome back! Please enter your credentials</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
          <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 fade-in">
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
          <div class="mb-4 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 fade-in">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <p class="text-sm text-green-800 dark:text-green-300">{{ session('status') }}</p>
            </div>
          </div>
        @endif

        <form id="loginForm" action="{{ route('login') }}" method="POST" class="space-y-5">
          @csrf
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
            <div class="flex items-center justify-between mb-2">
              <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
              <a href="{{ route('password.request') }}" class="text-sm text-[#1193d4] hover:text-[#0f83bd] hover:underline font-medium">Forgot password?</a>
            </div>
            <div class="relative">
              <input id="password" name="password" type="password" autocomplete="current-password" required
                     class="block w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1193d4] focus:border-[#1193d4] p-3 pr-10 transition-all duration-200 hover:border-[#1193d4]/50 @error('password') input-error border-red-500 @enderror"
                     placeholder="Enter your password"
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

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
              <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                     class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-[#1193d4] focus:ring-2 focus:ring-[#1193d4] cursor-pointer">
              Remember me
            </label>
          </div>

          <button type="submit" id="submitBtn"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1193d4] text-white font-semibold py-3.5 px-6 hover:bg-[#0f83bd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1193d4] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            <span id="submitText">Sign In</span>
            <svg id="submitSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </button>
        </form>

        <!-- Sign up CTA -->
        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
          Don't have an account?
          <a href="{{ route('register') }}" class="text-[#1193d4] hover:text-[#0f83bd] font-semibold hover:underline transition-colors">Sign up</a>
        </p>
      </div>
    </main>
  </div>

  <!-- Carousel logic -->
  <script>
    const images = [
      { url: "https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1600&auto=format&fit=crop", alt: "Team collaboration" },
      { url: "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1600&auto=format&fit=crop", alt: "Workspace" },
      { url: "https://images.unsplash.com/photo-1516251193007-45ef944ab0c6?q=80&w=1600&auto=format&fit=crop", alt: "Laptop" },
    ];

    const slidesRoot = document.getElementById('carouselSlides');
    const dotsRoot = document.getElementById('carouselDots');
    let current = 0;

    function renderSlides() {
      slidesRoot.innerHTML = images.map((img, i) => `
        <div class="absolute inset-0 transition-opacity duration-700 ease-in-out ${i === current ? 'opacity-100' : 'opacity-0'}">
          <img src="${img.url}" alt="${img.alt}" class="w-full h-full object-cover" draggable="false"/>
        </div>
      `).join('');

      dotsRoot.innerHTML = images.map((_, i) => `
        <span class="h-2.5 w-2.5 rounded-full transition-all duration-300 ${i === current ? 'bg-white' : 'bg-white/50'}"></span>
      `).join('');
    }

    function next() {
      current = (current + 1) % images.length;
      renderSlides();
    }

    renderSlides();
    let carouselInterval = setInterval(next, 4000);

    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');

    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.classList.toggle('hidden');
        eyeOffIcon.classList.toggle('hidden');
      });
    }

    // Form submission loading state
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    if (loginForm && submitBtn) {
      loginForm.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitText.textContent = 'Signing in...';
        submitSpinner.classList.remove('hidden');
      });
    }

    // Pause carousel on hover
    const carouselSection = document.querySelector('section');
    if (carouselSection) {
      carouselSection.addEventListener('mouseenter', () => clearInterval(carouselInterval));
      carouselSection.addEventListener('mouseleave', () => {
        carouselInterval = setInterval(next, 4000);
      });
    }
  </script>
</body>
</html>
