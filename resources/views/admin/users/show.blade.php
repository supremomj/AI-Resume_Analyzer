@extends('admin.layout')

@section('page-title', 'User Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.users') }}" class="text-[#1193d4] hover:text-[#0f83bd] flex items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Back to Users</span>
        </a>
    </div>

    <!-- User Info Card -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <img src="{{ $user->profile_photo ? route('profile.image', ['filename' => $user->profile_photo]) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=80&background=1193d4&color=fff&bold=true' }}"
                         alt="{{ $user->name }}"
                         class="w-20 h-20 rounded-full object-cover">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            @if($user->isAdmin())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Admin</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">User</span>
                            @endif
                            @if($user->hasVerifiedEmail())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Unverified</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Delete User
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Personal Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">First Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->first_name ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->last_name ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->contact_number ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->address ?? 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Account Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('F d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($user->hasVerifiedEmail())
                                    {{ $user->email_verified_at->format('F d, Y') }}
                                @else
                                    Not verified
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Profile Strength</dt>
                            <dd class="mt-1">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-[#1193d4] h-2 rounded-full" style="width: {{ $profileStrength['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $profileStrength['percentage'] }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $profileStrength['level'] }}</p>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Resume Uploaded</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($user->resume_path)
                                    <span class="text-green-600">Yes</span>
                                @else
                                    <span class="text-gray-500">No</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">AI Analysis</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($user->ai_analysis)
                                    <span class="text-green-600">Complete</span>
                                    @if($user->resume_score)
                                        <span class="text-gray-500">(Score: {{ $user->resume_score }})</span>
                                    @endif
                                @else
                                    <span class="text-gray-500">Not available</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Role Management -->
            @if($user->id !== auth()->id())
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Role Management</h3>
                    <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="flex items-center gap-4">
                        @csrf
                        @method('PATCH')
                        <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1193d4] focus:border-transparent">
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-[#1193d4] text-white rounded-lg hover:bg-[#0f83bd] transition-colors">
                            Update Role
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- User Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Bookmarks -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Bookmarks ({{ $user->bookmarks()->count() }})</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($bookmarks as $bookmark)
                        <div class="border-b border-gray-100 last:border-0 pb-4 last:pb-0">
                            <h4 class="font-medium text-gray-900">{{ $bookmark->job_title }}</h4>
                            <p class="text-sm text-gray-600">{{ $bookmark->company }} • {{ $bookmark->location }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $bookmark->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No bookmarks yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Job Views -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Job Views ({{ $user->jobViewHistory()->count() }})</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($viewHistory as $view)
                        <div class="border-b border-gray-100 last:border-0 pb-4 last:pb-0">
                            <h4 class="font-medium text-gray-900">{{ $view->job_title }}</h4>
                            <p class="text-sm text-gray-600">{{ $view->company }} • {{ $view->location }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Viewed {{ $view->view_count }} time(s) • Last: {{ $view->viewed_at->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No job views yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

