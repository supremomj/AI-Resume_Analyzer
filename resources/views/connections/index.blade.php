@extends('layouts.app')

@section('content')
<div class="flex flex-col min-h-screen bg-[#f8fafc] font-sans text-gray-900">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-5xl mx-auto">
                
                <!-- Minimal Header Section -->
                <div class="mb-8 border-b border-gray-200 pb-6">
                    <div class="flex items-center gap-3 mb-2 text-gray-900">
                        <span class="material-symbols-outlined text-3xl font-light">hub</span>
                        <h1 class="text-2xl font-bold tracking-tight">Professional Network</h1>
                    </div>
                    <p class="text-sm text-gray-500">Manage your connections and discover new opportunities through your community.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Left Column: Pending Requests & Discovery -->
                    <div class="lg:col-span-4 space-y-6">
                        
                        <!-- Discover People -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100 bg-[#fbfbfb]">
                                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-[#1193d4]">explore</span>
                                    Discover Professionals
                                </h2>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @forelse($discoverUsers as $dUser)
                                    <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                                        <img class="w-10 h-10 rounded-full object-cover border border-gray-100"
                                             src="{{ $dUser->profile_photo ? route('profile.image', ['filename' => $dUser->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($dUser->first_name . ' ' . $dUser->last_name) . '&background=1193d4&color=fff' }}"
                                             alt="Avatar">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-gray-900 truncate">{{ $dUser->first_name }} {{ $dUser->last_name }}</p>
                                            <p class="text-[10px] text-gray-500 mb-2 truncate">{{ $dUser->recommended_field ?? 'Professional' }}</p>
                                            <form action="{{ route('connections.request', $dUser) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-[10px] border border-[#1193d4] text-[#1193d4] px-3 py-1 rounded-full font-bold uppercase tracking-wider hover:bg-blue-50 transition-colors">Connect</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-10 text-center">
                                        <p class="text-xs text-gray-400 font-medium">No suggestions right now</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Invitations -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100 bg-[#fbfbfb]">
                                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">person_add</span>
                                    Invitations
                                    @if($pendingReceived->count() > 0)
                                        <span class="bg-[#1193d4] text-white text-[10px] px-2 py-0.5 rounded-full">{{ $pendingReceived->count() }}</span>
                                    @endif
                                </h2>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @forelse($pendingReceived as $request)
                                    <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                                        <img class="w-12 h-12 rounded-full object-cover border border-gray-100"
                                             src="{{ $request->requester->profile_photo ? route('profile.image', ['filename' => $request->requester->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($request->requester->first_name . ' ' . $request->requester->last_name) . '&background=1193d4&color=fff' }}"
                                             alt="Requester">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-gray-900 truncate">{{ $request->requester->first_name }} {{ $request->requester->last_name }}</p>
                                            <p class="text-[10px] text-gray-500 mb-2 truncate">{{ $request->requester->recommended_field ?? 'Member' }}</p>
                                            <div class="flex gap-2">
                                                <form action="{{ route('connections.accept', $request) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-[10px] bg-[#1193d4] text-white px-3 py-1.5 rounded-full font-bold uppercase tracking-wider hover:bg-[#0f83bd] transition-colors">Accept</button>
                                                </form>
                                                <form action="{{ route('connections.reject', $request) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-[10px] bg-white text-gray-500 border border-gray-200 px-3 py-1.5 rounded-full font-bold uppercase tracking-wider hover:bg-gray-50 transition-colors">Ignore</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-5 py-10 text-center">
                                        <p class="text-xs text-gray-400 font-medium">No pending invitations</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Sent Requests Summary -->
                        @if($pendingSent->count() > 0)
                            <div class="bg-white rounded-xl border border-gray-200 p-5">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Awaiting response</h3>
                                <div class="space-y-4">
                                    @foreach($pendingSent as $request)
                                        <div class="flex items-center gap-3">
                                            <img class="w-8 h-8 rounded-full border border-gray-100 grayscale-[0.5]"
                                                 src="{{ $request->recipient->profile_photo ? route('profile.image', ['filename' => $request->recipient->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($request->recipient->first_name . ' ' . $request->recipient->last_name) . '&background=ccc&color=fff' }}"
                                                 alt="Recipient">
                                            <p class="text-xs font-bold text-gray-700 truncate">{{ $request->recipient->first_name }} {{ $request->recipient->last_name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column: Connections Grid -->
                    <div class="lg:col-span-8">
                        <div class="bg-white rounded-xl border border-gray-200 min-h-[500px]">
                            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900">All Connections</h2>
                                    <p class="text-xs text-gray-500">{{ $connections->count() }} professionals</p>
                                </div>
                                <div class="relative w-full sm:w-64">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    <input type="text" placeholder="Search..." class="w-full pl-9 pr-4 py-2 rounded-full border-gray-200 focus:border-[#1193d4] focus:ring-0 text-xs bg-gray-50/50">
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($connections as $connection)
                                        <div class="group flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-[#1193d4]/20 hover:bg-blue-50/10 transition-all duration-200">
                                            <div class="relative">
                                                <img class="w-14 h-14 rounded-full object-cover border border-gray-200 shadow-xs"
                                                     src="{{ $connection->profile_photo ? route('profile.image', ['filename' => $connection->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($connection->first_name . ' ' . $connection->last_name) . '&size=128&background=1193d4&color=fff&bold=true' }}"
                                                     alt="Profile">
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-bold text-gray-900 truncate group-hover:text-[#1193d4] transition-colors">
                                                    {{ $connection->first_name }} {{ $connection->last_name }}
                                                </h3>
                                                <p class="text-[11px] text-gray-500 truncate mb-1">{{ $connection->recommended_field ?? 'Member' }}</p>
                                                <a href="{{ route('profile.show', ['user' => $connection->id]) }}" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-[#1193d4] transition-colors">View Profile</a>
                                            </div>

                                            <!-- Minimal Dropdown -->
                                            <div class="relative self-start" x-data="{ open: false }">
                                                <button @click="open = !open" class="text-gray-300 hover:text-gray-500 p-1">
                                                    <span class="material-symbols-outlined text-lg">more_horiz</span>
                                                </button>
                                                <div x-show="open" @click.away="open = false" 
                                                     class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-xl border border-gray-100 z-20 py-1"
                                                     x-cloak x-transition>
                                                    <form action="{{ route('connections.remove', $connection) }}" method="POST" onsubmit="return confirm('Remove contact?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-[10px] text-red-500 font-bold hover:bg-red-50 flex items-center gap-2 uppercase tracking-wider">
                                                            <span class="material-symbols-outlined text-sm">remove</span>
                                                            Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-full py-20 text-center">
                                            <p class="text-sm text-gray-400 font-medium italic">You haven't built any connections yet.</p>
                                        </div>
                                    @endforelse
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
