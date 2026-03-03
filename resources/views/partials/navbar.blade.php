<header class="sticky top-0 z-20 border-b border-[#23303a] bg-[#101c22]">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative">
    <div class="flex items-center justify-between h-16">
      <!-- Left: Logo -->
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        @include('partials.logo', ['class' => 'h-10 sm:h-11 w-auto'])
        <h1 class="text-xl sm:text-2xl font-bold text-white">HanapBuh.AI</h1>
      </a>

      <!-- Middle: Centered navbar links -->
      <nav class="absolute left-1/2 transform -translate-x-1/2">
        <div class="hidden md:flex items-center gap-8">
          <a href="{{ route('home') }}" class="text-sm font-medium text-white hover:text-[#1193d4] transition-colors duration-200">Home</a>
          <a href="{{ route('jobs') }}" class="text-sm font-medium text-white hover:text-[#1193d4] transition-colors duration-200">Jobs</a>
          <a href="{{ url('/about') }}" class="text-sm font-medium text-white hover:text-[#1193d4] transition-colors duration-200">About</a>
          <a href="#contact" class="text-sm font-medium text-white hover:text-[#1193d4] transition-colors duration-200">Contact</a>
        </div>
      </nav>

      <!-- Right: Auth/User actions -->
      <div class="flex items-center gap-2">
        {{-- Dynamic Navbar Links --}}
        @auth
        <!-- User is logged in -->
        <div class="flex items-center gap-2">
          <img src="{{ auth()->user()->profile_photo ? route('profile.image', ['filename' => auth()->user()->profile_photo]) . '?v=' . auth()->user()->updated_at->timestamp : 'https://ui-avatars.com/api/?name=' . urlencode((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) . '&size=32&background=1193d4&color=fff&bold=true' }}"
               alt="profile" 
               class="w-8 h-8 rounded-full object-cover border-2 border-[#1193d4] bg-gray-100"
               onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) }}&size=32&background=1193d4&color=fff&bold=true';">
        </div>
        @else
        <!-- User is logged out -->
        <div class="flex items-center gap-3">
          <a href="{{ route('login') }}" class="text-sm text-white hover:text-[#1193d4] transition-colors duration-200">Login</a>
          <a href="{{ route('register') }}" class="text-sm font-medium text-white bg-[#1193d4] hover:bg-[#0f83bd] px-3 py-1.5 rounded-lg transition-colors duration-200">Sign Up</a>
        </div>
        @endauth
      </div>
    </div>
  </div>
</header>
