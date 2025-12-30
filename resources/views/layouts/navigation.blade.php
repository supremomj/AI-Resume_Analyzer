<nav class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 relative">
        <!-- Left: Logo -->
        <div class="flex items-center gap-2">
            <span class="text-gray-400 text-xl sm:text-2xl font-medium">PH</span>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">HanapBuh.AI</h1>
        </div>

        <!-- Center: Navigation Links (Desktop) -->
        <nav class="hidden md:flex absolute left-1/2 transform -translate-x-1/2">
            <div class="flex items-center gap-6 lg:gap-8">
                <a href="{{ route('home') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Home</a>
                <a href="{{ route('jobs') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Jobs</a>
                @auth
                <a href="{{ route('bookmarks') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200 flex items-center gap-1">
                    Bookmarks
                    @if(auth()->user()->bookmarks()->count() > 0)
                        <span class="px-1.5 py-0.5 bg-[#1193d4] text-white text-xs rounded-full">{{ auth()->user()->bookmarks()->count() }}</span>
                    @endif
                </a>
                @endauth
                <a href="{{ url('/about') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">About</a>
                <a href="#contact" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Contact</a>
            </div>
        </nav>

        <!-- Right: Profile Dropdown or Auth Links -->
        <div class="flex items-center gap-2">
            @auth
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @keydown.escape="open = false"
                        class="flex items-center focus:outline-none rounded-full border-2 border-transparent hover:border-[#1193d4] transition-colors duration-200">
                    <img src="{{ auth()->user()->profile_photo ? route('profile.image', ['filename' => auth()->user()->profile_photo]) . '?v=' . auth()->user()->updated_at->timestamp : 'https://ui-avatars.com/api/?name=' . urlencode((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) . '&size=32&background=1193d4&color=fff&bold=true' }}"
                         alt="profile" 
                         class="w-8 h-8 rounded-full object-cover border-2 border-gray-200 bg-gray-100"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) }}&size=32&background=1193d4&color=fff&bold=true';">
                </button>
                <div x-cloak x-show="open"
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('profile.edit') }}">Profile</a>
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('resume.upload') }}">Resume</a>
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('bookmarks') }}">
                        Bookmarks
                        @if(auth()->user()->bookmarks()->count() > 0)
                            <span class="ml-2 px-1.5 py-0.5 bg-[#1193d4] text-white text-xs rounded-full">{{ auth()->user()->bookmarks()->count() }}</span>
                        @endif
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors border-t border-gray-200 mt-1 pt-1" href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    @endif
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('settings.index') }}">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors">Logout</button>
                    </form>
                </div>
            </div>
            @else
            <div class="hidden sm:flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Login</a>
                <a href="{{ route('register') }}" class="text-sm font-medium text-white bg-[#1193d4] hover:bg-[#0f83bd] px-3 py-1.5 rounded-lg transition-colors duration-200">Sign Up</a>
            </div>
            @endauth
            
            <!-- Mobile Menu Button -->
            <div x-data="{ mobileMenuOpen: false }" class="md:hidden ml-2">
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#1193d4]">
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <!-- Mobile Menu -->
                <div x-cloak x-show="mobileMenuOpen" 
                     @click.away="mobileMenuOpen = false"
                     class="absolute top-full left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-50">
                    <div class="px-4 py-4 space-y-3">
                        <a href="{{ route('home') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">Home</a>
                        <a href="{{ route('jobs') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">Jobs</a>
                        @auth
                        <a href="{{ route('bookmarks') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors flex items-center justify-between">
                            Bookmarks
                            @if(auth()->user()->bookmarks()->count() > 0)
                                <span class="px-1.5 py-0.5 bg-[#1193d4] text-white text-xs rounded-full">{{ auth()->user()->bookmarks()->count() }}</span>
                            @endif
                        </a>
                        @endauth
                        <a href="{{ url('/about') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">About</a>
                        <a href="#contact" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">Contact</a>
                        @guest
                        <div class="pt-3 border-t border-gray-200 space-y-2">
                            <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">Login</a>
                            <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium text-white bg-[#1193d4] hover:bg-[#0f83bd] rounded-lg transition-colors text-center">Sign Up</a>
                        </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
