@extends('layouts.app')

@section('content')
<div class="flex flex-col min-h-screen bg-[#f8fafc] font-sans">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-3xl mx-auto">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Notifications</h1>
                        <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-bold">Stay updated with your professional activity</p>
                    </div>
                    @if($notifications->where('read_at', null)->count() > 0)
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-[#1193d4] hover:text-[#0f83bd] transition-colors uppercase tracking-wider">
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="divide-y divide-gray-100">
                        @forelse($notifications as $notification)
                            <div class="p-5 flex items-start gap-4 hover:bg-gray-50 transition-all {{ $notification->unread() ? 'bg-blue-50/30' : '' }}" 
                                 id="notification-{{ $notification->id }}">
                                
                                <!-- Icon Based on Type -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 border border-gray-100 shadow-xs
                                    {{ $notification->data['type'] === 'connection_request' ? 'bg-purple-50 text-purple-500' : '' }}
                                    {{ $notification->data['type'] === 'new_message' ? 'bg-green-50 text-green-500' : '' }}
                                    {{ $notification->data['type'] === 'like' ? 'bg-red-50 text-red-500' : '' }}
                                    {{ $notification->data['type'] === 'comment' ? 'bg-blue-50 text-[#1193d4]' : '' }}">
                                    <span class="material-symbols-outlined text-xl">
                                        {{ $notification->data['type'] === 'connection_request' ? 'person_add' : '' }}
                                        {{ $notification->data['type'] === 'new_message' ? 'chat_bubble' : '' }}
                                        {{ $notification->data['type'] === 'like' ? 'favorite' : '' }}
                                        {{ $notification->data['type'] === 'comment' ? 'mode_comment' : '' }}
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm text-gray-800 leading-relaxed italic">
                                            <span class="font-bold text-gray-900 not-italic">{{ $notification->data['sender_name'] ?? $notification->data['user_name'] ?? 'Someone' }}</span>
                                            {{ $notification->data['message'] }}
                                        </p>
                                        <span class="text-[10px] text-gray-400 font-medium shrink-0 ml-4">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                    
                                    @if($notification->data['type'] === 'new_message' && isset($notification->data['content']))
                                        <p class="mt-2 text-xs text-gray-500 truncate bg-gray-50 p-2 rounded-lg border border-gray-100">
                                            "{{ $notification->data['content'] }}"
                                        </p>
                                    @endif

                                    <div class="mt-4 flex items-center gap-4">
                                        <a href="{{ $notification->data['url'] }}" 
                                           onclick="markAsRead('{{ $notification->id }}')"
                                           class="text-[10px] font-bold text-[#1193d4] uppercase tracking-widest hover:underline">
                                            View Details
                                        </a>
                                        @if($notification->unread())
                                            <button onclick="markAsRead('{{ $notification->id }}', true)" 
                                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest hover:text-gray-600">
                                                Mark read
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-20 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <span class="material-symbols-outlined text-gray-300 text-3xl">notifications_off</span>
                                </div>
                                <h3 class="text-base font-bold text-gray-400">No notifications yet</h3>
                                <p class="text-xs text-gray-400 mt-2">We'll let you know when something important happens.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    function markAsRead(id, hideOnly = false) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok && hideOnly) {
                const el = document.getElementById(`notification-${id}`);
                el.classList.remove('bg-blue-50/30');
                el.querySelector('button[onclick*="true"]')?.remove();
            }
        });
    }
</script>
@endsection
