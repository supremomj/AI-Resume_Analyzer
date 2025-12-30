@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-[#19212b] rounded-xl shadow-md p-8 mt-8 border border-[#23303a]">
    <!-- Profile Image, Name, and Edit Button -->
    <div class="flex items-center gap-6 mb-8">
        <img class="w-24 h-24 rounded-full object-cover border border-[#23303a] bg-gray-100"
             src="{{ $user->profile_photo ? route('profile.image', ['filename' => $user->profile_photo]) . '?v=' . $user->updated_at->timestamp : 'https://ui-avatars.com/api/?name=' . urlencode(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) . '&size=96&background=1193d4&color=fff&bold=true' }}"
             alt="Profile image"
             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }}&size=96&background=1193d4&color=fff&bold=true';">
        <div class="flex-1">
            <div class="font-bold text-xl text-white">
                {{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}
                @if(empty($user->first_name) && empty($user->last_name))
                    {{ $user->name }}
                @endif
            </div>
            <div class="text-blue-100 text-sm">{{ $user->email }}</div>
        </div>
        @if(auth()->check() && auth()->id() == $user->id)
            <a href="{{ route('profile.edit') }}" class="ml-auto px-4 py-2 rounded-xl bg-primary text-white font-semibold hover:bg-blue-700">Edit Profile</a>
        @endif
    </div>

    <div class="space-y-8">
        <div>
            <h4 class="font-semibold text-lg mb-2 text-white">Personal Information</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-blue-200">
                @if(!empty($user->first_name) || !empty($user->last_name))
                    <div>
                        <dt class="text-xs text-blue-300 mb-1">First Name</dt>
                        <dd class="font-medium">{{ $user->first_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-blue-300 mb-1">Last Name</dt>
                        <dd class="font-medium">{{ $user->last_name }}</dd>
                    </div>
                @endif
                @if(!empty($user->contact_number))
                    <div>
                        <dt class="text-xs text-blue-300 mb-1">Contact Number</dt>
                        <dd class="font-medium">{{ $user->contact_number }}</dd>
                    </div>
                @endif
                @if(!empty($user->address))
                    <div>
                        <dt class="text-xs text-blue-300 mb-1">Address</dt>
                        <dd class="font-medium">{{ $user->address }}</dd>
                    </div>
                @endif
            </dl>
        </div>
        <div>
            <h4 class="font-semibold text-lg mb-2 text-white">Account</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-blue-200">
                <div>
                    <dt class="text-xs text-blue-300 mb-1">Email Address</dt>
                    <dd class="font-medium">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-blue-300 mb-1">Resume</dt>
                    @if($user->resume_path)
                        <dd class="font-medium flex items-center gap-2">
                            <a href="{{ asset('storage/' . $user->resume_path) }}" target="_blank" class="text-primary underline font-semibold">
                                Download Resume
                            </a>
                            @if(auth()->check() && auth()->id() == $user->id)
                                <a href="{{ route('resume.upload') }}"
                                   onclick="return confirm('You will be redirected to upload or replace your resume. Continue?')"
                                   class="inline-block px-3 py-1 rounded bg-blue-800 text-white text-xs font-semibold hover:bg-blue-700 ml-1">
                                    Upload/Replace
                                </a>
                            @endif
                        </dd>
                    @else
                        <dd class="italic text-blue-300">
                            No resume uploaded.
                            @if(auth()->check() && auth()->id() == $user->id)
                                <a href="{{ route('resume.upload') }}"
                                   class="ml-2 px-2 py-1 rounded bg-blue-800 text-white text-xs font-semibold hover:bg-blue-700">
                                    Upload Resume
                                </a>
                            @endif
                        </dd>
                    @endif
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
