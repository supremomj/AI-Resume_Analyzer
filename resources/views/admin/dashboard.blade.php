@extends('admin.layout')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <span class="material-symbols-outlined text-blue-600 text-3xl">people</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Verified Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['verified_users']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $stats['total_users'] > 0 ? round(($stats['verified_users'] / $stats['total_users']) * 100, 1) : 0 }}% of total
                    </p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <span class="material-symbols-outlined text-green-600 text-3xl">verified</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Bookmarks</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_bookmarks']) }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <span class="material-symbols-outlined text-yellow-600 text-3xl">bookmark</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Job Views</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_views']) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <span class="material-symbols-outlined text-purple-600 text-3xl">visibility</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Users with Resumes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['users_with_resumes']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $stats['total_users'] > 0 ? round(($stats['users_with_resumes'] / $stats['total_users']) * 100, 1) : 0 }}% of users
                    </p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-lg">
                    <span class="material-symbols-outlined text-indigo-600 text-3xl">description</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">AI Analysis Complete</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['users_with_ai_analysis']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $stats['total_users'] > 0 ? round(($stats['users_with_ai_analysis'] / $stats['total_users']) * 100, 1) : 0 }}% of users
                    </p>
                </div>
                <div class="p-3 bg-pink-100 rounded-lg">
                    <span class="material-symbols-outlined text-pink-600 text-3xl">psychology</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->profile_photo ? route('profile.image', ['filename' => $user->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=40&background=1193d4&color=fff&bold=true' }}"
                                     alt="{{ $user->name }}"
                                     class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                                @if($user->isAdmin())
                                    <span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-800 text-xs rounded">Admin</span>
                                @else
                                    <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-800 text-xs rounded">User</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No users found.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.users') }}" class="text-[#1193d4] hover:text-[#0f83bd] text-sm font-medium">View All Users →</a>
                </div>
            </div>
        </div>

        <!-- Top Job Sources -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Job Sources</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topJobSources as $source)
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $source->source ?? 'Unknown' }}</p>
                                <div class="mt-2 bg-gray-200 rounded-full h-2">
                                    <div class="bg-[#1193d4] h-2 rounded-full" style="width: {{ $topJobSources->max('count') > 0 ? ($source->count / $topJobSources->max('count')) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="ml-4 text-right">
                                <p class="text-lg font-bold text-gray-900">{{ $source->count }}</p>
                                <p class="text-xs text-gray-500">bookmarks</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No job sources data available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

