@extends('layouts.app')

@section('title', 'My Bookmarks - JobMatch PH')

@section('content')
<div class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 font-display">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Page Heading -->
                <div class="mb-6 sm:mb-8">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <h1 class="text-gray-900 text-2xl sm:text-3xl md:text-4xl font-bold leading-tight mb-2">
                                My Bookmarks
                            </h1>
                            <p class="text-gray-600 text-base sm:text-lg font-normal">
                                Jobs you've saved for later review.
                            </p>
                        </div>
                        <a href="{{ route('jobs') }}" class="flex items-center gap-2 text-[#1193d4] hover:text-[#0f83bd] text-sm font-medium whitespace-nowrap transition-colors px-4 py-2 rounded-lg border border-[#1193d4] hover:bg-[#1193d4]/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Browse More Jobs
                        </a>
                    </div>
                </div>

                @if($bookmarks->count() > 0)
                    <!-- Bookmarks Count -->
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm">
                            <span class="font-semibold text-gray-900">{{ $bookmarks->count() }}</span> 
                            {{ $bookmarks->count() === 1 ? 'bookmark' : 'bookmarks' }}
                        </p>
                    </div>

                    <!-- Bookmarks Grid -->
                    <div id="bookmarks-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($bookmarks as $bookmark)
                            <div class="flex flex-col bg-white text-gray-900 rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-primary/20 transition-all duration-200 group" data-bookmark-id="{{ $bookmark->id }}">
                                <div class="relative w-full bg-gradient-to-br from-[#1193d4]/20 to-[#1193d4]/5 aspect-[4/3] rounded-lg mb-4 overflow-hidden flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[#1193d4] text-4xl">work</span>
                                    @if($bookmark->match_score)
                                        <div class="absolute top-2 right-2 bg-[#1193d4] text-white text-xs font-semibold px-2 py-1 rounded-full shadow-sm">
                                            {{ $bookmark->match_score }}% Match
                                        </div>
                                    @endif
                                    <button type="button" 
                                            class="absolute top-2 left-2 remove-bookmark-btn p-2 rounded-full bg-white/90 hover:bg-white shadow-md transition-all duration-200"
                                            data-bookmark-id="{{ $bookmark->id }}"
                                            data-job-url="{{ urlencode($bookmark->job_url) }}"
                                            aria-label="Remove bookmark">
                                        <svg class="w-5 h-5 text-[#1193d4]" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-gray-900 text-lg font-bold mb-2 line-clamp-2">{{ $bookmark->job_title }}</h3>
                                    <p class="text-gray-500 text-sm mb-2">
                                        {{ $bookmark->company ?? 'Company Not Specified' }}
                                    </p>
                                    @if($bookmark->location)
                                        <p class="text-gray-400 text-xs mb-3">
                                            {{ $bookmark->location }}
                                        </p>
                                    @endif
                                    @if($bookmark->description)
                                        <p class="text-gray-600 text-sm mb-3 line-clamp-3 leading-relaxed">
                                            {{ Str::limit(strip_tags($bookmark->description), 150) }}
                                        </p>
                                    @endif
                                    @if($bookmark->source)
                                        <p class="text-gray-400 text-xs mb-2">via {{ $bookmark->source }}</p>
                                    @endif
                                    <p class="text-gray-400 text-xs mb-2">
                                        Saved {{ $bookmark->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex gap-2 mt-4">
                                    <a href="{{ $bookmark->job_url }}" target="_blank" class="flex-1 rounded-lg h-10 px-4 bg-[#1193d4] text-white text-sm font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="flex flex-col items-center justify-center py-16 px-4">
                        <div class="w-24 h-24 bg-[#1193d4]/10 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-12 h-12 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No bookmarks yet</h3>
                        <p class="text-gray-600 text-center mb-6 max-w-md">
                            Start bookmarking jobs you're interested in by clicking the bookmark icon on any job card.
                        </p>
                        <a href="{{ route('jobs') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#1193d4] text-white font-semibold rounded-lg hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Browse Jobs
                        </a>
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

@if($bookmarks->count() > 0)
<script>
    // Get CSRF token
    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '{{ csrf_token() }}';
    }

    // Remove bookmark functionality
    document.addEventListener('DOMContentLoaded', function() {
        const removeButtons = document.querySelectorAll('.remove-bookmark-btn');
        
        removeButtons.forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const bookmarkId = btn.dataset.bookmarkId;
                const jobUrl = decodeURIComponent(btn.dataset.jobUrl);
                const bookmarkCard = btn.closest('[data-bookmark-id]');
                
                // Optimistic UI update - hide the card
                bookmarkCard.style.opacity = '0.5';
                bookmarkCard.style.pointerEvents = 'none';
                
                try {
                    const encodedUrl = encodeURIComponent(jobUrl);
                    const response = await fetch(`/api/bookmarks/${encodedUrl}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const responseData = await response.json();
                    
                    if (responseData.success !== false) {
                        // Remove the card with animation
                        bookmarkCard.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                        bookmarkCard.style.opacity = '0';
                        bookmarkCard.style.transform = 'scale(0.95)';
                        
                        setTimeout(() => {
                            bookmarkCard.remove();
                            
                            // Check if no bookmarks left
                            const remainingBookmarks = document.querySelectorAll('[data-bookmark-id]');
                            if (remainingBookmarks.length === 0) {
                                location.reload(); // Reload to show empty state
                            }
                        }, 300);
                    } else {
                        // Revert on error
                        bookmarkCard.style.opacity = '1';
                        bookmarkCard.style.pointerEvents = 'auto';
                        console.error('Failed to remove bookmark:', responseData.message || 'Unknown error');
                    }
                } catch (error) {
                    console.error('Error removing bookmark:', error);
                    // Revert on error
                    bookmarkCard.style.opacity = '1';
                    bookmarkCard.style.pointerEvents = 'auto';
                }
            });
        });
    });
</script>
@endif
@endsection

