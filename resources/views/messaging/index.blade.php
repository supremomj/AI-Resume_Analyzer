@extends('layouts.app')

@section('content')
<div class="flex flex-col h-[calc(100vh-64px)] bg-[#f8fafc] font-sans overflow-hidden text-gray-900">
    <div class="flex flex-1 overflow-hidden">
        
        <!-- Left Sidebar: Conversations List -->
        <div class="w-full md:w-80 lg:w-96 border-r border-gray-200 bg-white flex flex-col {{ $user ? 'hidden md:flex' : 'flex' }}">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-[#fbfbfb]">
                <h1 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Messaging</h1>
                <span class="material-symbols-outlined text-gray-400 text-xl">edit_square</span>
            </div>
            
            <div class="p-3 border-b border-gray-100 italic text-[10px] text-gray-400 bg-white">
                Connect and message with your network
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                @forelse($conversations as $conversation)
                    <a href="{{ route('messaging.index', $conversation) }}" 
                       class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-colors {{ optional($user)->id === $conversation->id ? 'bg-blue-50/50 border-l-4 border-[#1193d4]' : '' }}">
                        <img class="w-12 h-12 rounded-full object-cover border border-gray-100"
                             src="{{ $conversation->profile_photo ? route('profile.image', ['filename' => $conversation->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($conversation->first_name . ' ' . $conversation->last_name) . '&background=1193d4&color=fff' }}"
                             alt="Avatar">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <h3 class="text-sm font-bold text-gray-900 truncate">{{ $conversation->first_name }} {{ $conversation->last_name }}</h3>
                            </div>
                            <p class="text-[11px] text-gray-500 truncate">{{ $conversation->recommended_field ?? 'Member' }}</p>
                        </div>
                    </a>
                @empty
                    <div class="p-10 text-center">
                        <p class="text-xs text-gray-400 font-medium italic">No conversations yet.</p>
                        <a href="{{ route('connections.index') }}" class="text-[#1193d4] text-[10px] font-bold uppercase mt-2 block hover:underline">Find Connections</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col bg-white {{ !$user ? 'hidden md:flex' : 'flex' }}">
            @if($user)
                <!-- Chat Header -->
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-white shadow-sm z-10">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('messaging.index') }}" class="md:hidden text-gray-400">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </a>
                        <img class="w-10 h-10 rounded-full object-cover border border-gray-100"
                             src="{{ $user->profile_photo ? route('profile.image', ['filename' => $user->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&background=1193d4&color=fff' }}"
                             alt="Chat User">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h2>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $user->recommended_field ?? 'Professional' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="text-gray-300 hover:text-gray-500 transition-colors">
                            <span class="material-symbols-outlined text-xl">videocam</span>
                        </button>
                        <button class="text-gray-300 hover:text-gray-500 transition-colors">
                            <span class="material-symbols-outlined text-xl">info</span>
                        </button>
                    </div>
                </div>

                <!-- Messages List -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/30" id="message-container">
                    @foreach($messages as $message)
                        <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] flex items-end gap-2">
                                @if($message->sender_id !== auth()->id())
                                    <img class="w-6 h-6 rounded-full object-cover shrink-0 mb-1"
                                         src="{{ $user->profile_photo ? route('profile.image', ['filename' => $user->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&background=1193d4&color=fff' }}"
                                         alt="">
                                @endif
                                <div class="relative group">
                                    <div class="px-4 py-2 rounded-2xl text-sm {{ $message->sender_id === auth()->id() ? 'bg-[#1193d4] text-white rounded-br-none' : 'bg-white text-gray-800 border border-gray-200 shadow-xs rounded-bl-none' }}">
                                        {{ $message->content }}
                                    </div>
                                    <p class="text-[9px] text-gray-400 mt-1 {{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">
                                        {{ $message->created_at->format('g:i A') }}
                                        @if($message->sender_id === auth()->id() && $message->is_read)
                                            • Read
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Message Input -->
                <div class="p-4 border-t border-gray-100 bg-white">
                    <form action="{{ route('messaging.store', $user) }}" method="POST" class="flex items-end gap-3">
                        @csrf
                        <div class="flex-1 relative">
                            <textarea name="content" rows="1" 
                                      class="w-full rounded-2xl border-gray-200 focus:border-[#1193d4] focus:ring-0 text-sm py-3 px-4 resize-none bg-gray-50/50"
                                      placeholder="Write a message..." required
                                      onkeydown="if(event.keyCode == 13 && !event.shiftKey) { event.preventDefault(); this.form.submit(); }"></textarea>
                        </div>
                        <button type="submit" class="bg-[#1193d4] text-white p-3 rounded-full hover:bg-[#0f83bd] transition-all shadow-md shrink-0">
                            <span class="material-symbols-outlined text-xl">send</span>
                        </button>
                    </form>
                </div>
            @else
                <!-- Empty State -->
                <div class="flex-1 flex flex-col items-center justify-center p-10 bg-gray-50/20 text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-[#1193d4] text-3xl">chat_bubble</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Select a conversation</h2>
                    <p class="text-xs text-gray-500 max-w-xs mx-auto">Click on a connection to start messaging and building your professional relationships.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Scroll to bottom of message container
    const container = document.getElementById('message-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
</script>
@endsection
