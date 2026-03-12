@extends('layouts.app')

@section('content')
<div class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-100 font-display">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-4xl mx-auto">
                <!-- Header Section -->
                <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="material-symbols-outlined text-4xl text-[#1193d4]">account_circle</span>
                            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">{{ __('profile.title') }}</h1>
                        </div>
                        <p class="text-gray-600 ml-12">{{ __('profile.subtitle') }}</p>
                    </div>
                    
                    @if(auth()->check() && auth()->id() == $user->id)
                        <div class="ml-12 sm:ml-0">
                            <a href="{{ route('profile.edit') }}" 
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-[#1193d4] text-white font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105">
                                <span class="material-symbols-outlined">edit</span>
                                {{ __('profile.edit_profile') }}
                            </a>
                        </div>
                    @elseif(auth()->check())
                        <div class="ml-12 sm:ml-0">
                            @if($connectionStatus === 'accepted')
                                <div class="flex gap-2">
                                    <a href="{{ route('messaging.index', $user) }}" class="inline-flex items-center gap-2 px-6 py-2 rounded-full bg-white text-[#1193d4] text-sm font-bold border border-[#1193d4] hover:bg-blue-50 transition-all shadow-sm">
                                        <span class="material-symbols-outlined text-xl">chat</span>
                                        Message
                                    </a>
                                    <button class="inline-flex items-center gap-2 px-6 py-2 rounded-full bg-gray-100 text-gray-700 text-sm font-bold border border-gray-200 cursor-default" disabled>
                                        <span class="material-symbols-outlined text-green-500 text-xl">check_circle</span>
                                        Connected
                                    </button>
                                    <form action="{{ route('connections.remove', $user) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Remove this connection?')" 
                                                class="p-2 rounded-full border border-gray-200 text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all">
                                            <span class="material-symbols-outlined text-xl">person_remove</span>
                                        </button>
                                    </form>
                                </div>
                            @elseif($connectionStatus === 'requested')
                                <button class="inline-flex items-center gap-2 px-6 py-2 rounded-full bg-gray-50 text-gray-400 text-sm font-bold border border-gray-200 cursor-default" disabled>
                                    <span class="material-symbols-outlined text-xl">hourglass_top</span>
                                    Pending
                                </button>
                            @elseif($connectionStatus === 'pending_acceptance')
                                <div class="flex gap-2">
                                    <form action="{{ route('connections.accept', $user->receivedRequests()->where('user_id', auth()->id())->first() ?? $user->sentRequests()->where('connected_user_id', auth()->id())->first()) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 rounded-full bg-[#1193d4] text-white text-sm font-bold hover:bg-[#0f83bd] transition-all shadow-md">
                                            Accept
                                        </button>
                                    </form>
                                    <form action="{{ route('connections.reject', $user->receivedRequests()->where('user_id', auth()->id())->first() ?? $user->sentRequests()->where('connected_user_id', auth()->id())->first()) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-full border border-gray-200 text-gray-400 hover:bg-gray-50 transition-all">
                                            <span class="material-symbols-outlined text-xl">close</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('connections.request', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-2 px-8 py-2.5 rounded-full bg-[#1193d4] text-white text-sm font-bold hover:bg-[#0f83bd] transition-all shadow-lg hover:scale-105">
                                        <span class="material-symbols-outlined text-xl">person_add</span>
                                        Connect
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                @if(session('success'))
                    <div class="mb-6 bg-green-500 text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 animate-slide-in-right">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-500 text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 animate-slide-in-right">
                        <span class="material-symbols-outlined">error</span>
                        <span class="font-semibold">{{ session('error') }}</span>
                    </div>
                @endif
                <!-- Profile Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <!-- Top Cover/Header -->
                    <div class="h-32 bg-gradient-to-r from-[#1193d4] to-blue-600 opacity-90"></div>
                    
                    <div class="px-6 py-8 sm:px-10 relative">
                        <!-- Profile Image (Absolute Positioned) -->
                        <div class="absolute -top-16 left-6 sm:left-10">
                            <img class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl ring-4 ring-[#1193d4]/10 bg-white"
                                 src="{{ $user->profile_photo ? route('profile.image', ['filename' => $user->profile_photo]) . '?v=' . $user->updated_at->timestamp : 'https://ui-avatars.com/api/?name=' . urlencode(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) . '&size=128&background=1193d4&color=fff&bold=true' }}"
                                 alt="Profile image"
                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }}&size=128&background=1193d4&color=fff&bold=true';">
                        </div>
                        
                        <!-- Name and Email (Pushed Right) -->
                        <div class="mt-16 sm:mt-0 sm:ml-40 mb-8">
                            <h2 class="text-3xl font-bold text-gray-900">
                                {{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}
                                @if(empty($user->first_name) && empty($user->last_name))
                                    {{ $user->name }}
                                @endif
                            </h2>
                            <p class="text-lg text-gray-600 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[#1193d4] text-xl">email</span>
                                {{ $user->email }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                            <!-- Personal Details -->
                            <div class="space-y-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-[#1193d4]">person</span>
                                    <h3 class="text-xl font-bold text-gray-900">{{ __('profile.personal_information') }}</h3>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-4 ml-8">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('profile.first_name') }}</p>
                                        <p class="text-gray-900 font-medium">{{ $user->first_name ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('profile.last_name') }}</p>
                                        <p class="text-gray-900 font-medium">{{ $user->last_name ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('profile.contact_number') }}</p>
                                        <p class="text-gray-900 font-medium">{{ $user->contact_number ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('profile.address') }}</p>
                                        <p class="text-gray-900 font-medium">{{ $user->address ?: '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Details -->
                            <div class="space-y-6 pt-8 md:pt-0 md:pl-8">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-[#1193d4]">account_box</span>
                                    <h3 class="text-xl font-bold text-gray-900">{{ __('profile.account_details') }}</h3>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-4 ml-8">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('profile.email_address') }}</p>
                                        <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('profile.resume') }}</p>
                                        @if($user->resume_path)
                                            <div class="flex items-center gap-3 mt-1">
                                                <a href="{{ asset('storage/' . $user->resume_path) }}" target="_blank" 
                                                   class="inline-flex items-center gap-2 text-[#1193d4] hover:text-[#0f83bd] font-semibold transition-colors">
                                                    <span class="material-symbols-outlined">description</span>
                                                    {{ __('profile.view_resume') }}
                                                </a>
                                                @if(auth()->check() && auth()->id() == $user->id)
                                                    <a href="{{ route('resume.upload') }}"
                                                       onclick="return confirm('{{ __('profile.confirm_redirect_resume') }}')"
                                                       class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-2 py-1 rounded transition-colors">
                                                        {{ __('profile.update_resume') }}
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex items-center gap-3 mt-1">
                                                <p class="text-gray-500 italic">{{ __('profile.no_resume') }}</p>
                                                @if(auth()->check() && auth()->id() == $user->id)
                                                    <a href="{{ route('resume.upload') }}"
                                                       class="text-xs bg-[#1193d4]/10 hover:bg-[#1193d4]/20 text-[#1193d4] px-2 py-1 rounded transition-colors font-semibold">
                                                        {{ __('profile.upload_resume') }}
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
