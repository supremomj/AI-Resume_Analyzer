@extends('layouts.app')

@section('content')
<div class="flex flex-col min-h-screen bg-[#f8fafc] font-sans text-gray-900">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-5xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Left Sidebar: Profile Summary -->
                    <div class="hidden lg:block lg:col-span-3">
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-24">
                            <!-- Minimal Header -->
                            <div class="h-20 bg-[#1193d4]"></div>
                            
                            <div class="px-5 pb-5 text-center transform -translate-y-10">
                                <img class="w-20 h-20 rounded-full mx-auto border-4 border-white shadow-sm mb-3 object-cover"
                                     src="{{ auth()->user()->profile_photo ? route('profile.image', ['filename' => auth()->user()->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name . ' ' . auth()->user()->last_name) . '&background=1193d4&color=fff' }}"
                                     alt="Profile">
                                <h2 class="text-base font-bold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h2>
                                <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->recommended_field ?? 'Professional' }}</p>
                                
                                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                                    <span class="text-gray-500">Connections</span>
                                    <span class="font-bold text-[#1193d4]">{{ auth()->user()->connections()->count() }}</span>
                                </div>
                            </div>

                            <a href="{{ route('profile.show') }}" class="block p-3 text-center text-xs font-bold text-gray-600 border-t border-gray-100 hover:bg-gray-50 transition-colors uppercase tracking-widest">
                                My Profile
                            </a>
                        </div>
                    </div>

                    <!-- Main Feed -->
                    <div class="lg:col-span-6 space-y-4">
                        
                        <!-- Create Post Box: Minimalist -->
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center gap-3">
                                <img class="w-10 h-10 rounded-full object-cover grayscale-[0.5]"
                                     src="{{ auth()->user()->profile_photo ? route('profile.image', ['filename' => auth()->user()->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name . ' ' . auth()->user()->last_name) . '&background=1193d4&color=fff' }}"
                                     alt="Me">
                                <button onclick="document.getElementById('post-modal').classList.remove('hidden')" 
                                        class="flex-1 text-left px-4 py-2.5 rounded-full border border-gray-200 bg-gray-50 text-gray-500 text-sm font-medium hover:border-gray-300 transition-all cursor-pointer">
                                    Start a conversation...
                                </button>
                            </div>
                        </div>

                        <!-- Posts List -->
                        <div class="space-y-4">
                            @forelse($posts as $post)
                                <div class="bg-white rounded-xl border border-gray-200">
                                    <!-- Post Header -->
                                    <div class="p-4 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('profile.show', $post->user) }}">
                                                <img class="w-10 h-10 rounded-full object-cover"
                                                     src="{{ $post->user->profile_photo ? route('profile.image', ['filename' => $post->user->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($post->user->first_name . ' ' . $post->user->last_name) . '&background=1193d4&color=fff' }}"
                                                     alt="Poster">
                                            </a>
                                            <div class="min-w-0">
                                                <a href="{{ route('profile.show', $post->user) }}" class="text-sm font-bold text-gray-900 hover:text-[#1193d4] transition-colors leading-tight block truncate">
                                                    {{ $post->user->first_name }} {{ $post->user->last_name }}
                                                </a>
                                                <p class="text-[11px] text-gray-500 leading-tight">
                                                    {{ $post->user->recommended_field ?? 'Member' }} • {{ $post->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        @if($post->user_id === auth()->id())
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1">
                                                    <span class="material-symbols-outlined text-xl">more_vert</span>
                                                </button>
                                                <div x-show="open" @click.away="open = false" 
                                                     class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-xl border border-gray-200 z-20 py-1"
                                                     x-cloak x-transition>
                                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-600 font-bold hover:bg-gray-50 flex items-center gap-2">
                                                            <span class="material-symbols-outlined text-sm">delete</span>
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Post Content -->
                                    <div class="px-4 pb-4">
                                        <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">{{ $post->content }}</p>
                                    </div>

                                    <!-- Post Image if exists -->
                                    @if($post->image_path)
                                        <div class="border-y border-gray-100 bg-[#fbfbfb]">
                                            <img src="{{ asset('storage/' . $post->image_path) }}" 
                                                 class="max-h-[400px] w-full object-contain mx-auto"
                                                 alt="Post attachment">
                                        </div>
                                    @endif

                                    <!-- Engagement Stats -->
                                    @if($post->likes_count > 0 || $post->comments_count > 0)
                                        <div class="px-4 py-2 flex items-center justify-between border-b border-gray-50 text-[10px] text-gray-500 font-medium">
                                            <div class="flex items-center gap-1">
                                                @if($post->likes_count > 0)
                                                    <div class="flex items-center -space-x-1">
                                                        <span class="w-4 h-4 rounded-full bg-blue-100 flex items-center justify-center border border-white z-10">
                                                            <span class="material-symbols-outlined text-[10px] text-[#1193d4] font-bold">thumb_up</span>
                                                        </span>
                                                    </div>
                                                    <span>{{ $post->likes_count }} {{ Str::plural('like', $post->likes_count) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                @if($post->comments_count > 0)
                                                    <span>{{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Post Actions & Comments Area -->
                                    <div x-data="{ showComments: false }">
                                        <div class="px-2 py-1 flex items-center gap-1">
                                            <!-- Like Button -->
                                            <form action="{{ route('posts.like', $post) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-50 transition-colors {{ $post->user_liked ? 'text-[#1193d4]' : 'text-gray-500' }}">
                                                    <span class="material-symbols-outlined text-xl {{ $post->user_liked ? 'fill-1' : '' }}">thumb_up</span>
                                                    <span class="text-[11px] font-bold uppercase tracking-wider">Like</span>
                                                </button>
                                            </form>

                                            <!-- Comment Toggle Button -->
                                            <button @click="showComments = !showComments" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-50 text-gray-500 transition-colors">
                                                <span class="material-symbols-outlined text-xl">chat_bubble</span>
                                                <span class="text-[11px] font-bold uppercase tracking-wider">Comment</span>
                                            </button>

                                            <!-- Share Button (Copy Link) -->
                                            <button onclick="navigator.clipboard.writeText('{{ route('feed') }}'); alert('Link copied to clipboard!')" 
                                                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-50 text-gray-500 transition-colors">
                                                <span class="material-symbols-outlined text-xl">share</span>
                                                <span class="text-[11px] font-bold uppercase tracking-wider">Share</span>
                                            </button>
                                        </div>

                                        <!-- Comments Section -->
                                        <div x-show="showComments" x-transition class="border-t border-gray-100 bg-gray-50/30 p-4 space-y-4">
                                            <!-- Comment Input -->
                                            <div class="flex gap-3">
                                                <img class="w-8 h-8 rounded-full object-cover shrink-0"
                                                     src="{{ auth()->user()->profile_photo ? route('profile.image', ['filename' => auth()->user()->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name . ' ' . auth()->user()->last_name) . '&background=1193d4&color=fff' }}"
                                                     alt="">
                                                <form action="{{ route('posts.comment', $post) }}" method="POST" class="flex-1">
                                                    @csrf
                                                    <div class="relative">
                                                        <textarea name="content" rows="1" 
                                                                  class="w-full rounded-2xl border-gray-200 focus:border-[#1193d4] focus:ring-0 text-xs py-2 px-4 pr-10 resize-none bg-white"
                                                                  placeholder="Add a comment..." required
                                                                  onkeydown="if(event.keyCode == 13 && !event.shiftKey) { event.preventDefault(); this.form.submit(); }"></textarea>
                                                        <button type="submit" class="absolute right-2 top-1.5 text-[#1193d4] hover:text-[#0f83bd]">
                                                            <span class="material-symbols-outlined text-xl font-bold">send</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Existing Comments -->
                                            <div class="space-y-4">
                                                @foreach($post->comments as $comment)
                                                    <div class="flex gap-3">
                                                        <a href="{{ route('profile.show', $comment->user) }}">
                                                            <img class="w-8 h-8 rounded-full object-cover shrink-0"
                                                                 src="{{ $comment->user->profile_photo ? route('profile.image', ['filename' => $comment->user->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->first_name . ' ' . $comment->user->last_name) . '&background=1193d4&color=fff' }}"
                                                                 alt="">
                                                        </a>
                                                        <div class="flex-1 bg-gray-100/70 p-3 rounded-2xl rounded-tl-none">
                                                            <div class="flex justify-between items-baseline mb-1">
                                                                <a href="{{ route('profile.show', $comment->user) }}" class="text-[11px] font-bold text-gray-900 hover:text-[#1193d4]">
                                                                    {{ $comment->user->first_name }} {{ $comment->user->last_name }}
                                                                </a>
                                                                <span class="text-[9px] text-gray-400 font-medium">{{ $comment->created_at->diffForHumans() }}</span>
                                                            </div>
                                                            <p class="text-xs text-gray-700 leading-normal">{{ $comment->content }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
                                    <h3 class="text-base font-bold text-gray-400">Your feed is currenty empty</h3>
                                    <p class="text-gray-400 text-xs mt-2">Connect with others to see their professional updates.</p>
                                    <a href="{{ route('connections.index') }}" class="inline-block mt-4 text-[#1193d4] text-xs font-bold hover:underline">Find Connections</a>
                                </div>
                            @endforelse

                            <div class="mt-6">
                                {{ $posts->links() }}
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="hidden lg:block lg:col-span-3">
                        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-24">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Trending</h3>
                            <div class="space-y-4">
                                @foreach(['AI in the Workplace', 'Remote Work Trends 2026', 'Career Growth Strategies'] as $trend)
                                <div class="group cursor-pointer">
                                    <p class="text-sm font-bold text-gray-800 group-hover:text-[#1193d4] transition-colors leading-tight truncate">{{ $trend }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1 uppercase">Top News</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mt-4 px-2">
                           <p class="text-[10px] text-gray-400 text-center">HanapBuh.AI © 2026</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Post Modal: Minimal -->
    <div id="post-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs transition-opacity" onclick="document.getElementById('post-modal').classList.add('hidden')"></div>

            <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full relative z-10">
                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-bold text-gray-900">Create post</h2>
                            <button type="button" onclick="document.getElementById('post-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <span class="material-symbols-outlined text-2xl">close</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-3 mb-6">
                            <img class="w-10 h-10 rounded-full object-cover"
                                 src="{{ auth()->user()->profile_photo ? route('profile.image', ['filename' => auth()->user()->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name . ' ' . auth()->user()->last_name) . '&background=1193d4&color=fff' }}"
                                 alt="Me">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h3>
                                <p class="text-[10px] text-gray-500 font-bold uppercase flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">public</span>
                                    Anyone
                                </p>
                            </div>
                        </div>

                        <textarea name="content" rows="4" 
                                  class="w-full text-base border-none focus:ring-0 placeholder-gray-400 resize-none mb-4"
                                  placeholder="What's on your mind?" required></textarea>

                        <div class="flex items-center gap-4">
                            <label class="cursor-pointer group flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition-all border border-transparent hover:border-gray-100">
                                <span class="material-symbols-outlined text-blue-500">add_photo_alternate</span>
                                <span class="text-xs font-bold text-gray-500 group-hover:text-gray-900">Add Photo</span>
                                <input type="file" name="image" class="hidden" accept="image/*">
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                        <button type="submit" 
                                class="px-6 py-2 rounded-full bg-[#1193d4] text-white text-sm font-bold hover:bg-[#0f83bd] transition-all shadow-md">
                            Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="fixed bottom-6 right-6 z-[100] bg-gray-900 text-white px-5 py-3 rounded-lg shadow-2xl flex items-center gap-3 animate-slide-in-right" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
        <span class="material-symbols-outlined text-green-400 text-xl">check_circle</span>
        <span class="font-bold text-xs">{{ session('success') }}</span>
    </div>
@endif

<style>
@keyframes slide-in-right {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.animate-slide-in-right {
    animation: slide-in-right 0.3s ease-out forwards;
}
</style>
@endsection
