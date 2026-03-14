@extends('layouts.app')

@section('content')
<div x-data="{ showDeleteModal: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }" class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-100 font-display">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-4xl mx-auto">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-4xl text-[#1193d4]">settings</span>
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">{{ __('settings.title') }}</h1>
                    </div>
                    <p class="text-gray-600 ml-12">{{ __('settings.subtitle') }}</p>
                </div>

                <!-- Success/Error Toast Messages -->
                @if(session('success'))
                    <div id="success-toast" class="fixed bottom-6 right-6 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 animate-slide-in-right">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span class="font-semibold">{{ session('success') ?? __('settings.saved_successfully') }}</span>
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

                <!-- Settings Form -->
                <form action="{{ route('settings.update') }}" method="POST" id="settings-form" class="space-y-6">
                    @csrf
                    @method('PATCH')



                    <!-- Account Preferences Section -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                        <div class="p-6 sm:p-8 border-b border-gray-200 bg-gradient-to-r from-[#1193d4]/5 to-blue-50/50">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-3xl text-[#1193d4]">person</span>
                                <h2 class="text-2xl font-bold text-gray-900">{{ __('settings.account_preferences') }}</h2>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 ml-12">{{ __('settings.account_preferences_subtitle') }}</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <!-- Language Preference -->
                            <div class="p-4 rounded-lg border-2 border-gray-100">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="material-symbols-outlined text-[#1193d4]">language</span>
                                    <label for="language" class="text-base font-semibold text-gray-900">
                                        {{ __('settings.language_preference') }}
                                    </label>
                                </div>
                                <p class="text-sm text-gray-600 mb-3 ml-7">{{ __('settings.language_preference_desc') }}</p>
                                <div class="relative ml-7 max-w-xs">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl">translate</span>
                                    <select name="language" id="language"
                                            class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#1193d4] focus:ring-2 focus:ring-[#1193d4]/20 transition-all duration-200 appearance-none bg-white">
                                        <option value="en" {{ old('language', $user->language ?? 'en') === 'en' ? 'selected' : '' }}>{{ __('settings.language_english') }}</option>
                                        <option value="tl" {{ old('language', $user->language ?? 'en') === 'tl' ? 'selected' : '' }}>{{ __('settings.language_tagalog') }}</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">arrow_drop_down</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Data Management Section -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                        <div class="p-6 sm:p-8 border-b border-gray-200 bg-gradient-to-r from-[#1193d4]/5 to-blue-50/50">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-3xl text-[#1193d4]">database</span>
                                <h2 class="text-2xl font-bold text-gray-900">{{ __('settings.data_management') }}</h2>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 ml-12">{{ __('settings.data_management_subtitle') }}</p>
                        </div>

                        <div class="p-6 sm:p-8 space-y-4">
                            <!-- Export Data -->
                            <div class="flex items-center justify-between gap-4 p-4 rounded-lg border-2 border-gray-100 hover:border-[#1193d4]/30 transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[#1193d4] text-2xl">download</span>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">{{ __('settings.export_data') }}</h3>
                                        <p class="text-sm text-gray-600">{{ __('settings.export_data_desc') }}</p>
                                    </div>
                                </div>
                                <button type="button" onclick="exportData()" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1193d4] text-white font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105">
                                    <span class="material-symbols-outlined text-lg">file_download</span>
                                    {{ __('settings.export_button') }}
                                </button>
                            </div>

                            <!-- Delete Account -->
                            <div class="flex items-center justify-between gap-4 p-4 rounded-lg border-2 border-red-100 bg-red-50/50">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-red-600 text-2xl">delete_forever</span>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">{{ __('settings.delete_account') }}</h3>
                                        <p class="text-sm text-gray-600">{{ __('settings.delete_account_desc') }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="showDeleteModal = true"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                    {{ __('settings.delete_button') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-4">
                        <a href="{{ url()->previous() }}" 
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg border-2 border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                            <span class="material-symbols-outlined">close</span>
                            {{ __('settings.cancel') }}
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center justify-center gap-2 px-8 py-3 rounded-lg bg-[#1193d4] text-white font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105">
                            <span class="material-symbols-outlined">save</span>
                            {{ __('settings.save_changes') }}
                        </button>
                    </div>
                </form>
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
    // Auto-hide toast messages
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
    });
    
    // Export user data
    function exportData() {
        if (confirm('This will download all your account data in JSON format. Continue?')) {
            window.location.href = '{{ route("settings.export") }}';
        }
    }

    
    // Form validation and loading state
    const form = document.getElementById('settings-form');
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
    
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>
@endsection

