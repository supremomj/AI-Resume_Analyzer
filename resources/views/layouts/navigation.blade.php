<nav class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 relative">
        <!-- Left: Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            @include('partials.logo', ['class' => 'h-10 sm:h-11 w-auto'])
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">HanapBuh.AI</h1>
        </a>

        <!-- Center: Navigation Links (Desktop) -->
        <nav class="hidden md:flex absolute left-1/2 transform -translate-x-1/2">
            <div class="flex items-center gap-6 lg:gap-8">
                <a href="{{ route('home') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Home</a>
                <a href="{{ route('feed') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Feed</a>
                <a href="{{ route('messaging.index') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Messages</a>
                


                <a href="{{ route('connections.index') }}" class="text-sm font-medium text-gray-900 hover:text-[#1193d4] transition-colors duration-200">Network</a>
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
        <div class="flex items-center gap-4">
            @auth
            <!-- Notification Bell & Dropdown -->
            <div x-data="{ open: false }" class="relative flex items-center">
                <button @click="open = !open" 
                        class="relative text-gray-500 hover:text-[#1193d4] transition-colors focus:outline-none">
                    <span class="material-symbols-outlined text-2xl">notifications</span>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-1 -right-1 flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[9px] text-white items-center justify-center font-bold">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        </span>
                    @endif
                </button>

                <!-- Dropdown Panel -->
                <div x-cloak x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden transform"
                     style="top: 100%;">
                    
                    <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-widest">Notifications</h3>
                        <a href="{{ route('notifications.index') }}" class="text-[10px] font-bold text-[#1193d4] hover:underline">Settings</a>
                    </div>

                    <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">
                        @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                            <a href="{{ $notification->data['url'] }}" 
                               class="block p-4 hover:bg-gray-50 transition-colors group">
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 border border-gray-100
                                        {{ $notification->data['type'] === 'connection_request' ? 'bg-purple-50 text-purple-500' : '' }}
                                        {{ $notification->data['type'] === 'new_message' ? 'bg-green-50 text-green-500' : '' }}
                                        {{ $notification->data['type'] === 'like' ? 'bg-red-50 text-red-500' : '' }}
                                        {{ $notification->data['type'] === 'comment' ? 'bg-blue-50 text-[#1193d4]' : '' }}">
                                        <span class="material-symbols-outlined text-sm">
                                            {{ $notification->data['type'] === 'connection_request' ? 'person_add' : '' }}
                                            {{ $notification->data['type'] === 'new_message' ? 'chat_bubble' : '' }}
                                            {{ $notification->data['type'] === 'like' ? 'favorite' : '' }}
                                            {{ $notification->data['type'] === 'comment' ? 'mode_comment' : '' }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-800 leading-snug">
                                            <span class="font-bold text-gray-900">{{ $notification->data['sender_name'] ?? $notification->data['user_name'] ?? 'Someone' }}</span>
                                            {{ $notification->data['message'] }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center">
                                <span class="material-symbols-outlined text-gray-300 text-3xl mb-2">notifications_off</span>
                                <p class="text-xs text-gray-400 font-medium">No new notifications</p>
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('notifications.index') }}" class="block p-3 text-center text-[11px] font-bold text-gray-600 border-t border-gray-50 hover:bg-gray-50 transition-colors uppercase tracking-widest">
                        View all notifications
                    </a>
                </div>
            </div>
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
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('profile.show') }}">View Profile</a>
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('profile.edit') }}">Edit Profile</a>
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('messaging.index') }}">Messages</a>
                    <a class="block px-4 py-2 text-sm text-gray-800 hover:bg-[#1193d4]/10 hover:text-[#1193d4] transition-colors" href="{{ route('connections.index') }}">My Network</a>
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
                        <a href="{{ route('feed') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">Feed</a>
                        <a href="{{ route('messaging.index') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">Messages</a>
                        <a href="{{ route('notifications.index') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors flex items-center justify-between">
                            Notifications
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        </a>
                        <a href="{{ route('connections.index') }}" class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-[#1193d4] hover:bg-[#1193d4]/10 rounded-lg transition-colors">My Network</a>
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
