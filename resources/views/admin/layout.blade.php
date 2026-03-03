<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Panel - {{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white min-h-screen fixed left-0 top-0">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-8">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        @include('partials.logo', ['class' => 'h-10 w-auto'])
                        <span>Admin Panel</span>
                    </a>
                </h1>
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-[#1193d4]' : '' }}">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('admin.users*') ? 'bg-gray-800 text-[#1193d4]' : '' }}">
                        <span class="material-symbols-outlined">people</span>
                        <span>Users</span>
                    </a>
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors">
                        <span class="material-symbols-outlined">home</span>
                        <span>Back to Site</span>
                    </a>
                </nav>
            </div>
            <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ auth()->user()->profile_photo ? route('profile.image', ['filename' => auth()->user()->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&size=40&background=1193d4&color=fff&bold=true' }}"
                         alt="profile" 
                         class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">Administrator</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors text-left">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Admin Panel')</h2>
                        <div class="flex items-center gap-4">
                            @if(session('success'))
                                <div class="px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm">
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="px-4 py-2 bg-red-100 text-red-800 rounded-lg text-sm">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

