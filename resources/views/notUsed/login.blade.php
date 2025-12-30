<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>JobMatch AI - Login</title>

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
      <div class="absolute inset-0 bg-black/25"></div>

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
        <div class="mb-6 text-center">
          <div class="flex items-center justify-center gap-2 mb-3">
            <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M12 2a10 10 0 100 20 10 10 0 000-20zm-1 13h2v2h-2v-2zm0-8h2v6h-2V7z"/>
            </svg>
            <span class="text-xl font-bold text-gray-900 dark:text-white">HanapBuh.AI</span>
          </div>
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Sign in to your account</h2>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
          @csrf
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   class="mt-2 block w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-primary focus:border-primary p-3"
                   placeholder="you@email.com">
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
              <a href="#" class="text-sm text-primary hover:underline">Forgot?</a>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="mt-2 block w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0f161a] text-gray-900 dark:text-white focus:ring-primary focus:border-primary p-3"
                   placeholder="••••••••">
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <input type="checkbox" name="remember" class="rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary">
              Remember me
            </label>
          </div>

          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-white font-semibold py-3 hover:bg-[#0f83bd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            Sign In
          </button>
        </form>

        <div class="my-6 relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
          </div>
          <div class="relative flex justify-center text-xs">
            <span class="px-2 bg-white dark:bg-[#0f161a] text-gray-500 dark:text-gray-400">Or continue with</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <button type="button"
                  class="w-full inline-flex justify-center items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 py-2 hover:bg-gray-50 dark:hover:bg-white/5">
            <svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor"
              d="M21.6 12.23c0-.66-.06-1.29-.17-1.9H12v3.6h5.4a4.62 4.62 0 0 1-2 3.03v2.5h3.2c1.88-1.73 3-4.27 3-7.23Z"/></svg>
            Google
          </button>
          <button type="button"
                  class="w-full inline-flex justify-center items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 py-2 hover:bg-gray-50 dark:hover:bg:white/5 dark:hover:bg-white/5">
            <svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor"
              d="M22 12a10 10 0 1 0-11.5 9.87v-6.98H8v-2.9h2.5V9.8c0-2.47 1.48-3.84 3.74-3.84 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.86H16.9l-.45 2.9h-2.1v6.98A10 10 0 0 0 22 12Z"/></svg>
            Facebook
          </button>
        </div>

        <!-- Sign up CTA -->
        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
          Don't have an account?
          <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
             class="text-primary font-medium hover:underline">Sign up</a>
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
    setInterval(next, 4000);
  </script>
</body>
</html>
