@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div x-data="{ showDeleteModal: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }" class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-100 font-display">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-4xl mx-auto">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-4xl text-[#1193d4]">edit</span>
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Edit Profile</h1>
                    </div>
                    <p class="text-gray-600 ml-12">Update your personal information and account settings</p>
                </div>

                <!-- Success/Error Toast Messages -->
                @if(session('success'))
                    <div id="success-toast" class="fixed bottom-6 right-6 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 animate-slide-in-right">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span class="font-semibold">{{ session('success') }}</span>
                        <button onclick="document.getElementById('success-toast').remove()" class="ml-4 hover:bg-green-600 rounded p-1">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div id="error-toast" class="fixed bottom-6 right-6 z-50 bg-red-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 animate-slide-in-right max-w-md">
                        <span class="material-symbols-outlined">error</span>
                        <div class="flex-1">
                            <p class="font-semibold mb-1">Please fix the following errors:</p>
                            <ul class="text-sm list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button onclick="document.getElementById('error-toast').remove()" class="ml-4 hover:bg-red-600 rounded p-1">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                @endif

                <!-- Main Form Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form" class="divide-y divide-gray-200">
                        @csrf
                        @method('PATCH')

                        <!-- Profile Photo Section -->
                        <div class="p-6 sm:p-8">
                            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                                <div class="relative flex-shrink-0 group">
                                    <div class="relative">
                                        <img id="profile_preview" 
                                             class="w-32 h-32 sm:w-40 sm:h-40 rounded-full object-cover border-4 border-white shadow-xl ring-4 ring-[#1193d4]/20 bg-gradient-to-br from-[#1193d4]/10 to-blue-100 transition-all duration-300 group-hover:ring-[#1193d4]/40"
                                             src="{{ $user->profile_photo ? route('profile.image', ['filename' => $user->profile_photo]) . '?v=' . $user->updated_at->timestamp : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&size=160&background=1193d4&color=fff&bold=true' }}"
                                             alt="Profile image"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&size=160&background=1193d4&color=fff&bold=true';">
                                        <label for="profile_photo" class="absolute bottom-0 right-0 bg-[#1193d4] text-white rounded-full p-3 cursor-pointer hover:bg-[#0f83bd] transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-110 group">
                                            <span class="material-symbols-outlined text-xl">photo_camera</span>
                                            <input type="file" id="profile_photo" name="profile_photo"
                                                   accept="image/png,image/jpeg,image/jpg,image/webp" 
                                                   class="hidden"
                                                   onchange="previewProfilePhoto(this)">
                                        </label>
                                    </div>
                                </div>
                                <div class="flex-1 text-center sm:text-left">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Profile Photo</h3>
                                    <p class="text-sm text-gray-600 mb-3">Upload a JPG, PNG, or WEBP image (max 2MB)</p>
                                    @if($user->profile_photo)
                                        <button type="submit" name="remove_photo" value="1" 
                                                onclick="return confirm('Are you sure you want to remove your profile photo?')"
                                                class="inline-flex items-center gap-2 text-sm text-red-600 hover:text-red-700 font-medium transition-colors">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                            Remove Photo
                                        </button>
                                    @endif
                                    @error('profile_photo')
                                        <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    <div id="file-info" class="mt-2 text-xs text-gray-500 hidden"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information Section -->
                        <div class="p-6 sm:p-8">
                            <div class="flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-2xl text-[#1193d4]">person</span>
                                <h2 class="text-2xl font-bold text-gray-900">Personal Information</h2>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- First Name -->
                                <div>
                                    <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">badge</span>
                                        <input type="text" name="first_name" id="first_name"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 @error('first_name') border-red-500 @enderror"
                                               value="{{ old('first_name', $user->first_name) }}" 
                                               required
                                               autocomplete="given-name">
                                    </div>
                                    @error('first_name')
                                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">badge</span>
                                        <input type="text" name="last_name" id="last_name"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 @error('last_name') border-red-500 @enderror"
                                               value="{{ old('last_name', $user->last_name) }}" 
                                               required
                                               autocomplete="family-name">
                                    </div>
                                    @error('last_name')
                                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Contact Number -->
                                <div>
                                    <label for="contact_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Contact Number
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">phone</span>
                                        <input type="tel" name="contact_number" id="contact_number"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 @error('contact_number') border-red-500 @enderror"
                                               value="{{ old('contact_number', $user->contact_number) }}"
                                               pattern="^(09|\+639)\d{9}$" 
                                               placeholder="+63 917 123 4567"
                                               autocomplete="tel">
                                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-500">PH Format</span>
                                    </div>
                                    @error('contact_number')
                                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Format: +639XXXXXXXXX or 09XXXXXXXXX</p>
                                </div>

                                <!-- Address -->
                                <div>
                                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Address
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">location_on</span>
                                        <input type="text" name="address" id="address"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 @error('address') border-red-500 @enderror"
                                               value="{{ old('address', $user->address) }}"
                                               placeholder="City, Province"
                                               autocomplete="street-address">
                                    </div>
                                    @error('address')
                                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Email (Read-only) -->
                                <div class="md:col-span-2">
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Email Address
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">email</span>
                                        <input type="email" id="email"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50 text-gray-600 cursor-not-allowed"
                                               value="{{ $user->email }}" 
                                               disabled
                                               autocomplete="email">
                                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Cannot be changed</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Email address cannot be modified for security reasons</p>
                                </div>
                            </div>
                        </div>

                        <!-- Resume Section -->
                        <div class="p-6 sm:p-8 bg-blue-50/50">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="material-symbols-outlined text-2xl text-[#1193d4]">description</span>
                                <h2 class="text-xl font-bold text-gray-900">Resume</h2>
                            </div>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex-1">
                                    @if($user->resume_path)
                                        <a href="{{ asset('storage/' . $user->resume_path) }}" target="_blank"
                                           class="inline-flex items-center gap-2 text-[#1193d4] hover:text-[#0f83bd] font-semibold transition-colors">
                                            <span class="material-symbols-outlined">visibility</span>
                                            View Current Resume
                                        </a>
                                        <p class="text-sm text-gray-600 mt-1">Upload a new resume to replace the current one</p>
                                    @else
                                        <p class="text-gray-600 font-medium">No resume uploaded yet</p>
                                        <p class="text-sm text-gray-500 mt-1">Upload your resume to get AI-powered job recommendations</p>
                                    @endif
                                </div>
                                <a href="{{ route('resume.upload') }}"
                                   class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-[#1193d4] text-white font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105">
                                    <span class="material-symbols-outlined">upload_file</span>
                                    {{ $user->resume_path ? 'Update Resume' : 'Upload Resume' }}
                                </a>
                            </div>
                        </div>

                        <!-- Account Security Section -->
                        <div class="p-6 sm:p-8">
                            <div class="flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-2xl text-[#1193d4]">lock</span>
                                <h2 class="text-2xl font-bold text-gray-900">Account Security</h2>
                            </div>
                            <p class="text-sm text-gray-600 mb-6 bg-blue-50 p-4 rounded-lg border-l-4 border-[#1193d4]">
                                <span class="material-symbols-outlined inline-block align-middle mr-2">info</span>
                                Leave password fields blank if you do not want to change your password.
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Current Password -->
                                <div>
                                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Current Password
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">lock</span>
                                        <input type="password" name="current_password" id="current_password"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 @error('current_password') border-red-500 @enderror"
                                               autocomplete="current-password"
                                               placeholder="Enter current password">
                                        <button type="button" onclick="togglePasswordVisibility('current_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <span class="material-symbols-outlined text-xl" id="current_password_icon">visibility_off</span>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div>
                                    <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                        New Password
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">lock_reset</span>
                                        <input type="password" name="new_password" id="new_password"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 @error('new_password') border-red-500 @enderror"
                                               autocomplete="new-password"
                                               placeholder="Enter new password">
                                        <button type="button" onclick="togglePasswordVisibility('new_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <span class="material-symbols-outlined text-xl" id="new_password_icon">visibility_off</span>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters with uppercase, lowercase, and number</p>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="md:col-span-2">
                                    <label for="new_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Confirm New Password
                                    </label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">lock_reset</span>
                                        <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                               class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 @error('new_password_confirmation') border-red-500 @enderror"
                                               autocomplete="new-password"
                                               placeholder="Confirm new password">
                                        <button type="button" onclick="togglePasswordVisibility('new_password_confirmation')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <span class="material-symbols-outlined text-xl" id="new_password_confirmation_icon">visibility_off</span>
                                        </button>
                                    </div>
                                    @error('new_password_confirmation')
                                        <p class="text-sm text-red-600 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">error</span>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="p-6 sm:p-8 bg-gray-50 flex flex-col sm:flex-row justify-end gap-4">
                            <a href="{{ url()->previous() }}" 
                               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg border-2 border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                                <span class="material-symbols-outlined">close</span>
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center justify-center gap-2 px-8 py-3 rounded-lg bg-[#1193d4] text-white font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105">
                                <span class="material-symbols-outlined">save</span>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone Section -->
                <div class="mt-8 bg-white rounded-2xl shadow-xl overflow-hidden border border-red-200">
                    <div class="p-6 sm:p-8 border-b border-red-100 bg-red-50/30">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-3xl text-red-600">report_problem</span>
                            <h2 class="text-2xl font-bold text-gray-900">{{ __('settings.danger_zone') ?? 'Danger Zone' }}</h2>
                        </div>
                        <p class="text-sm text-gray-600 mt-1 ml-12">{{ __('settings.delete_account_desc') }}</p>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-lg border-2 border-red-100 bg-red-50/50">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-red-600 text-2xl">delete_forever</span>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ __('settings.delete_account') }}</h3>
                                    <p class="text-sm text-gray-600">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
                                </div>
                            </div>
                            
                            <button type="button" @click="showDeleteModal = true"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105">
                                <span class="material-symbols-outlined text-lg">delete</span>
                                {{ __('settings.delete_button') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Account Modal -->
    <div x-show="showDeleteModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showDeleteModal = false">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
                    @csrf
                    @method('delete')

                    <div class="flex items-center gap-3 mb-4 text-red-600">
                        <span class="material-symbols-outlined text-3xl">warning</span>
                        <h2 class="text-2xl font-bold">{{ __('Are you sure?') }}</h2>
                    </div>

                    <p class="text-gray-600 mb-6 font-medium">
                        {{ __('Once your account is deleted, all of its data will be permanently cleared. Please enter your password to confirm.') }}
                    </p>

                    <div class="mb-6">
                        <label for="delete_password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">lock</span>
                            <input type="password" name="password" id="delete_password"
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200"
                                   placeholder="Enter your password" required>
                        </div>
                        @if($errors->userDeletion->has('password'))
                            <p class="text-sm text-red-600 mt-2">{{ $errors->userDeletion->first('password') }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <button type="button" @click="showDeleteModal = false"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg border-2 border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition-all duration-200">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <span class="material-symbols-outlined text-lg">delete_forever</span>
                            {{ __('Delete Account Permanently') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    function previewProfilePhoto(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            
            // Check file size
            if (file.size > maxSize) {
                alert('File size exceeds 2MB. Please choose a smaller file.');
                input.value = '';
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please upload JPG, PNG, or WEBP image.');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profile_preview');
                preview.src = e.target.result;
                preview.onerror = null;
                
                // Show file info
                const fileInfo = document.getElementById('file-info');
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fileInfo.textContent = `Selected: ${file.name} (${fileSize} MB)`;
                fileInfo.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
    
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '_icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility_off';
        }
    }
    
    // Auto-hide toast messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const successToast = document.getElementById('success-toast');
        const errorToast = document.getElementById('error-toast');
        
        if (successToast) {
            setTimeout(() => {
                successToast.style.transition = 'opacity 0.5s';
                successToast.style.opacity = '0';
                setTimeout(() => successToast.remove(), 500);
            }, 5000);
        }
        
        if (errorToast) {
            setTimeout(() => {
                errorToast.style.transition = 'opacity 0.5s';
                errorToast.style.opacity = '0';
                setTimeout(() => errorToast.remove(), 500);
            }, 8000);
        }
        
        // Force reload of profile image if it failed to load
        const preview = document.getElementById('profile_preview');
        if (preview) {
            preview.onerror = function() {
                this.onerror = null;
                const fallbackUrl = 'https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&size=160&background=1193d4&color=fff&bold=true';
                this.src = fallbackUrl;
            };
        }
    });
    
    // Form validation feedback
    const form = document.getElementById('profile-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> Saving...';
            }
        });
    }
</script>

<style>
    @keyframes slide-in-right {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .animate-slide-in-right {
        animation: slide-in-right 0.3s ease-out;
    }
</style>
@endsection
