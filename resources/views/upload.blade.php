@extends('layouts.app')

@section('content')
@php
    $user = auth()->user()->fresh();
@endphp
<div class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 font-display">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center items-start py-20">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-center text-gray-900 mb-2 leading-tight">
                    Find your dream job.
                </h2>
                <p class="text-center text-gray-600 mb-8 text-sm sm:text-base md:text-lg">
                    Upload your resume and let our AI match you with the best opportunities across the Philippines.
                </p>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                    <!-- Left Column: Upload Form -->
                    <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 md:p-8 border border-gray-200">
                        <form action="{{ route('resume.upload.save') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="resume-upload-form">
            @csrf
                        <div class="flex flex-col items-center gap-4 sm:gap-6 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl px-4 sm:px-6 py-6 sm:py-8 hover:border-[#1193d4] transition-colors" id="upload-area">
                            <div class="rounded-full border-4 border-gray-200 bg-gray-100 h-16 w-16 sm:h-20 sm:w-20 flex items-center justify-center mb-1">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                </div>
                            <span class="block text-gray-900 font-semibold text-sm sm:text-base mb-0 text-center px-2">
                    Upload your resume to unlock tailored job matches
                </span>
                            <p class="text-center font-semibold text-gray-700 text-base sm:text-lg mb-1">
                    Drag & drop your resume here
                </p>
                            <span class="text-xs sm:text-sm text-gray-500 mb-2">or</span>
                <input type="file" name="resume" id="resume" class="hidden" accept=".pdf,application/pdf" required>
                            <button type="button" onclick="document.getElementById('resume').click()" class="cursor-pointer rounded-md px-4 sm:px-6 py-2 sm:py-3 font-bold text-white bg-[#1193d4] shadow text-sm sm:text-base hover:bg-[#0f83bd] transition block mb-0 mt-0 w-full" id="upload-button">
                    Upload Resume
                </button>
                            <span class="text-xs text-gray-500 mt-3">
                    Supported format: PDF only (up to 5MB)
                </span>
                            <div id="file-info" class="hidden text-sm text-gray-600 mt-2"></div>
                        </div>
                        
                        <!-- Loading indicator -->
                        <div id="upload-loading" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-[#1193d4]"></div>
                                <span class="text-sm text-blue-800 font-medium">Uploading and analyzing resume... This may take a moment.</span>
                            </div>
            </div>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const form = document.getElementById('resume-upload-form');
                                const fileInput = document.getElementById('resume');
                                const fileInfo = document.getElementById('file-info');
                                const uploadButton = document.getElementById('upload-button');
                                const uploadArea = document.getElementById('upload-area');
                                const loadingDiv = document.getElementById('upload-loading');
                                
                                // Ensure button triggers file input
                                if (uploadButton) {
                                    uploadButton.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        fileInput.click();
                                    });
                                }
                                
                                // Drag and drop functionality
                                if (uploadArea) {
                                    uploadArea.addEventListener('dragover', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        uploadArea.classList.add('border-[#1193d4]', 'bg-blue-50');
                                    });
                                    
                                    uploadArea.addEventListener('dragleave', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        uploadArea.classList.remove('border-[#1193d4]', 'bg-blue-50');
                                    });
                                    
                                    uploadArea.addEventListener('drop', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        uploadArea.classList.remove('border-[#1193d4]', 'bg-blue-50');
                                        
                                        const files = e.dataTransfer.files;
                                        if (files.length > 0) {
                                            fileInput.files = files;
                                            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                                        }
                                    });
                                }
                                
                                // Show file info when selected
                                fileInput.addEventListener('change', function(e) {
                                    if (e.target.files && e.target.files.length > 0) {
                                        const file = e.target.files[0];
                                        const fileSize = (file.size / 1024 / 1024).toFixed(2);
                                        fileInfo.textContent = `Selected: ${file.name} (${fileSize} MB)`;
                                        fileInfo.classList.remove('hidden');
                                        
                                        // Validate file size (5MB max)
                                        if (file.size > 5 * 1024 * 1024) {
                                            alert('File size exceeds 5MB. Please upload a smaller file.');
                                            fileInput.value = '';
                                            fileInfo.classList.add('hidden');
                                            return;
                                        }
                                        
                                        // Validate file type - PDF only
                                        const allowedTypes = ['application/pdf'];
                                        if (!allowedTypes.includes(file.type) && !file.name.match(/\.pdf$/i)) {
                                            alert('Invalid file type. Please upload a PDF file only.');
                                            fileInput.value = '';
                                            fileInfo.classList.add('hidden');
                                            return;
                                        }
                                        
                                        // Auto-submit form when file is selected
                                        setTimeout(() => {
                                            if (!uploadButton.disabled) {
                                                form.submit();
                                                loadingDiv.classList.remove('hidden');
                                                uploadButton.disabled = true;
                                                uploadButton.textContent = 'Uploading...';
                                                uploadArea.classList.add('opacity-50');
                                            }
                                        }, 300);
                                    }
                                });
                                
                                // Prevent double submission
                                form.addEventListener('submit', function(e) {
                                    if (uploadButton.disabled) {
                                        e.preventDefault();
                                        return false;
                                    }
                                    
                                    // Validate file is selected
                                    if (!fileInput.files || fileInput.files.length === 0) {
                                        e.preventDefault();
                                        alert('Please select a resume file to upload.');
                                        return false;
                                    }
                                    
                                    loadingDiv.classList.remove('hidden');
                                    uploadButton.disabled = true;
                                    uploadButton.textContent = 'Uploading...';
                                    uploadArea.classList.add('opacity-50');
                                });
                            });
                        </script>
            @if(session('resume'))
                        <div class="mt-8 w-full flex items-center p-4 bg-green-50 border border-green-200 rounded-xl">
                            <span class="material-symbols-outlined text-green-600 mr-2">check_circle</span>
                            <span class="text-gray-900">{{ session('resume') }}</span>
                            <span class="ml-auto text-xs text-green-600 font-semibold">Uploaded!</span>
            </div>
            @endif
            @error('resume')
                            <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if(session('status'))
                                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                                    <p class="text-sm text-green-800 font-semibold flex items-center">
                                        <span class="material-symbols-outlined mr-2">check_circle</span>
                                        {{ session('status') }}
                                    </p>
                                </div>
                            @endif
                            @if($errors->any())
                                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                                    <p class="text-sm text-red-800 font-semibold mb-2">Upload Error:</p>
                                    <ul class="text-sm text-red-700 list-disc list-inside">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(session('ai_error'))
                                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                                    <p class="text-sm text-yellow-800 mb-2">
                                        <span class="font-semibold">⚠️ AI Analysis Note:</span> {{ session('ai_error') }}
                                    </p>
                                    <div class="bg-yellow-100 p-3 rounded mt-2">
                                        <p class="text-xs font-semibold text-yellow-900 mb-1">To enable AI analysis:</p>
                                        <ol class="text-xs text-yellow-800 list-decimal list-inside space-y-1">
                                            <li>Double-click <code class="bg-white px-1 rounded">start_flask_api.bat</code> in your project root, OR</li>
                                            <li>Run manually: <code class="bg-white px-1 rounded">cd C:\Users\azi\Desktop\UR\AI-Resume-Analyzer\App && python flask_api.py</code></li>
                                        </ol>
                                        <p class="text-xs text-yellow-700 mt-2">The Flask API should show: <code class="bg-white px-1 rounded">Running on http://0.0.0.0:8502</code></p>
                                        <p class="text-xs text-yellow-700 mt-2">Check Laravel logs: <code class="bg-white px-1 rounded">storage\logs\laravel.log</code></p>
                                    </div>
                                </div>
                            @endif
                        </form>
                        <p class="mt-8 text-xs text-center text-gray-600">
                            By uploading your resume, you agree to our
                            <a href="#" class="text-[#1193d4] hover:underline">Terms of Service</a> and
                            <a href="#" class="text-[#1193d4] hover:underline">Privacy Policy</a>.
                        </p>
                    </div>
                    
                    <!-- Right Column: AI Analysis Results -->
                    <div class="lg:sticky lg:top-20">
                        @if($user->ai_analysis)
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-sm">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    <h3 class="text-lg font-bold text-gray-900">AI Analysis Results</h3>
                                </div>
                                
                                <!-- Resume Score -->
                                <div class="mb-5">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-700">Resume Score</span>
                                        <span class="text-lg font-bold text-[#1193d4]">{{ $user->resume_score ?? 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-gradient-to-r from-[#1193d4] to-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ min($user->resume_score ?? 0, 100) }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Recommended Fields -->
                                <div class="mb-5 p-3 bg-white rounded-lg border border-blue-100">
                                    <div class="mb-2">
                                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Recommended Fields</span>
                                        @php
                                            $recommendedFields = $user->ai_analysis['recommended_fields'] ?? [];
                                            // Fallback to single field if old format
                                            if (empty($recommendedFields) && !empty($user->recommended_field)) {
                                                $recommendedFields = [['field' => $user->recommended_field, 'confidence' => 1.0]];
                                            }
                                        @endphp
                                        @if(!empty($recommendedFields) && is_array($recommendedFields))
                                            <div class="mt-2 space-y-1.5">
                                                @foreach($recommendedFields as $index => $rf)
                                                    @php
                                                        $field = is_array($rf) ? ($rf['field'] ?? $rf) : $rf;
                                                        $confidence = is_array($rf) ? ($rf['confidence'] ?? 0) : 1.0;
                                                        $isPrimary = $index === 0;
                                                    @endphp
                                                    <div class="flex items-center justify-between p-2 {{ $isPrimary ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200' }} rounded-lg">
                                                        <div class="flex items-center gap-2">
                                                            @if($isPrimary)
                                                                <span class="text-xs font-bold text-blue-600">#1</span>
                                                            @endif
                                                            <span class="text-sm {{ $isPrimary ? 'font-bold text-[#1193d4]' : 'font-semibold text-gray-700' }}">{{ $field }}</span>
                                                        </div>
                                                        @if(is_array($rf) && isset($rf['confidence']))
                                                            <span class="text-xs text-gray-500">{{ round($rf['confidence'] * 100, 0) }}%</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="mt-1">
                                                <span class="text-base font-bold text-[#1193d4]">{{ $user->recommended_field ?? 'Software Engineering' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @if(isset($user->ai_analysis['ph_experience_level']) && $user->ai_analysis['ph_experience_level'])
                                        <div class="mt-2">
                                            <span class="text-xs font-semibold text-gray-600">Experience Level: </span>
                                            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $user->ai_analysis['ph_experience_level'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Philippine Salary Range - Prominent Display -->
                                @php
                                    // Get salary value directly
                                    $salaryValue = $user->ai_analysis['ph_salary_range'] ?? null;
                                    $hasSalary = false;
                                    
                                    // Check if salary exists and has a valid value (more lenient check)
                                    if (isset($user->ai_analysis['ph_salary_range']) && $user->ai_analysis['ph_salary_range'] !== null) {
                                        $salaryStr = (string)$salaryValue;
                                        $salaryStr = trim($salaryStr);
                                        // Check if it's not empty and not 'None' or 'null' string
                                        if ($salaryStr !== '' && 
                                            strtolower($salaryStr) !== 'none' && 
                                            strtolower($salaryStr) !== 'null') {
                                            $hasSalary = true;
                                            $salaryValue = $salaryStr;
                                        }
                                    }
                                @endphp
                                
                                @if($hasSalary && !empty($salaryValue))
                                    <div class="mb-5 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg shadow-sm">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Philippine Salary Range</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xl font-bold text-green-700">{{ $salaryValue }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Monthly salary range for {{ $user->recommended_field ?? 'this field' }} in the Philippines</p>
                                    </div>
                                @else
                                    <!-- Salary placeholder if not available -->
                                    <div class="mb-5 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Philippine Salary Range</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 mb-2">Salary information will appear here after re-uploading your resume with the updated AI system.</p>
                                        <details class="mt-2">
                                            <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600">Debug Info (Click to expand)</summary>
                                            <div class="mt-2 p-2 bg-gray-100 rounded text-xs font-mono text-gray-700 overflow-auto max-h-40">
                                                @if($user->ai_analysis)
                                                    <div><strong>Has AI Analysis:</strong> Yes</div>
                                                    <div><strong>Recommended Field:</strong> {{ $user->recommended_field ?? 'Not set' }}</div>
                                                    <div><strong>Salary Field Exists:</strong> {{ isset($user->ai_analysis['ph_salary_range']) ? 'Yes' : 'No' }}</div>
                                                    @if(isset($user->ai_analysis['ph_salary_range']))
                                                        <div><strong>Salary Value:</strong> {{ json_encode($user->ai_analysis['ph_salary_range']) }} (Type: {{ gettype($user->ai_analysis['ph_salary_range']) }})</div>
                                                        <div><strong>Is Empty?:</strong> {{ empty($user->ai_analysis['ph_salary_range']) ? 'Yes' : 'No' }}</div>
                                                        <div><strong>Is Null?:</strong> {{ $user->ai_analysis['ph_salary_range'] === null ? 'Yes' : 'No' }}</div>
                                                        <div><strong>Has Salary (Display Check):</strong> {{ $hasSalary ? 'Yes - WILL SHOW' : 'No - Will NOT show' }}</div>
                                                        <div><strong>Raw Value Check:</strong> {{ var_export($user->ai_analysis['ph_salary_range'], true) }}</div>
                                                    @endif
                                                    <div class="mt-2"><strong>Flask API Version:</strong> 
                                                        @if(isset($user->ai_analysis['api_version']))
                                                            <span class="text-green-600">{{ $user->ai_analysis['api_version'] }}</span>
                                                        @else
                                                            <span class="text-red-600">⚠️ OLD API DETECTED - Running flask_api.py instead of flask_api_new.py!</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-2"><strong>Issue:</strong> 
                                                        @if($user->recommended_field === 'Software Engineer')
                                                            <span class="text-orange-600 font-semibold">❌ Your recommended_field is "Software Engineer" (role name). This data was processed with the OLD Flask API code.<br><br>⚠️ <strong>ACTION REQUIRED:</strong><br>✅ Flask API is now running correctly (you restarted it)<br>📤 <strong>Please RE-UPLOAD your resume now</strong> to get the updated analysis with salary data!</span>
                                                        @else
                                                            <span class="text-green-600">✅ Field name looks correct: {{ $user->recommended_field }}</span>
                                                        @endif
                                                    </div>
                                                    @if(!isset($user->ai_analysis['api_version']))
                                                        <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded">
                                                            <strong class="text-yellow-800">⚠️ Old Data Detected:</strong>
                                                            <p class="text-sm text-yellow-700 mt-1">
                                                                Your current resume analysis was processed with the old Flask API.<br>
                                                                <strong>Please re-upload your resume</strong> to get the updated analysis with salary data.
                                                            </p>
                                                        </div>
                                                    @endif
                                                    <div class="mt-2"><strong>All AI Analysis Keys:</strong></div>
                                                    <ul class="list-disc list-inside ml-2">
                                                        @foreach(array_keys($user->ai_analysis ?? []) as $key)
                                                            <li>{{ $key }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div><strong>Has AI Analysis:</strong> No - Please upload your resume first</div>
                                                @endif
                                            </div>
                                        </details>
                                    </div>
                                @endif
                                
                                <!-- Extracted Skills -->
                                <div class="mb-5">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-700">Extracted Skills ({{ count($user->ai_analysis['skills'] ?? []) }})</span>
                                    </div>
                                    @if(isset($user->ai_analysis['skills']) && count($user->ai_analysis['skills']) > 0)
                                        @php
                                            $skillsToShow = 12;
                                            $allSkills = $user->ai_analysis['skills'];
                                            $displaySkills = array_slice($allSkills, 0, $skillsToShow);
                                            $remainingSkills = array_slice($allSkills, $skillsToShow);
                                            $hasMoreSkills = count($remainingSkills) > 0;
                                        @endphp
                                        <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                                            @foreach($displaySkills as $skill)
                                                <span class="px-3 py-1 bg-white border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50 transition-colors">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                        @if($hasMoreSkills)
                                            <button onclick="openSkillsModal({{ json_encode($allSkills) }})" 
                                                    class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-[#1193d4] text-white rounded-full text-xs font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md hover:scale-105">
                                                <span class="material-symbols-outlined text-sm">more_horiz</span>
                                                View All {{ count($allSkills) }} Skills
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-500 italic">No skills extracted</span>
                                    @endif
                                </div>
                                
                                <!-- Skills Modal -->
                                <div id="skills-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                        <!-- Background overlay -->
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeSkillsModal()"></div>
                                        
                                        <!-- Modal panel -->
                                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                                            <!-- Header -->
                                            <div class="bg-gradient-to-r from-[#1193d4] to-blue-600 px-6 py-4">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <span class="material-symbols-outlined text-white text-2xl">psychology</span>
                                                        <h3 class="text-xl font-bold text-white" id="modal-title">
                                                            All Extracted Skills
                                                            <span id="skills-count" class="text-sm font-normal text-blue-100 ml-2">(0)</span>
                                                        </h3>
                                                    </div>
                                                    <button onclick="closeSkillsModal()" class="text-white hover:text-gray-200 transition-colors p-1 rounded-full hover:bg-white/10">
                                                        <span class="material-symbols-outlined text-2xl">close</span>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Modal body -->
                                            <div class="bg-white px-6 py-6">
                                                <div id="skills-container" class="flex flex-wrap gap-2 max-h-96 overflow-y-auto">
                                                    <!-- Skills will be inserted here via JavaScript -->
                                                </div>
                                            </div>
                                            
                                            <!-- Footer -->
                                            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                                                <button onclick="closeSkillsModal()" 
                                                        class="inline-flex items-center gap-2 px-6 py-2 bg-[#1193d4] text-white font-semibold rounded-lg hover:bg-[#0f83bd] transition-all duration-200 shadow-md hover:shadow-lg">
                                                    <span class="material-symbols-outlined text-lg">close</span>
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recommended Skills -->
                                @if(isset($user->ai_analysis['recommended_skills']) && count($user->ai_analysis['recommended_skills']) > 0)
                                    <div class="mb-5">
                                        <span class="text-sm font-semibold text-gray-700 block mb-2">Recommended Skills to Learn</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(array_slice($user->ai_analysis['recommended_skills'], 0, 8) as $skill)
                                                <span class="px-3 py-1 bg-purple-50 border border-purple-200 rounded-full text-xs text-purple-700 hover:bg-purple-100 transition-colors">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Recommended Courses -->
                                <div class="mb-5">
                                    <span class="text-sm font-semibold text-gray-700 block mb-2">Recommended Courses</span>
                                    @if(isset($user->ai_analysis['recommended_courses']) && count($user->ai_analysis['recommended_courses']) > 0)
                                        <div class="space-y-2">
                                            @foreach(array_slice($user->ai_analysis['recommended_courses'], 0, 3) as $course)
                                                <div class="p-2 bg-green-50 border border-green-200 rounded-lg text-xs text-green-800 hover:bg-green-100 transition-colors">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                    {{ $course }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500 italic">No courses recommended</span>
                                    @endif
                                </div>

                                <!-- Matched Jobs -->
                                @php
                                    $matchedJobs = $user->ai_analysis['recommended_jobs'] ?? [];
                                    $matchedJobs = is_array($matchedJobs) ? $matchedJobs : [];
                                    $matchedJobsToShow = array_slice($matchedJobs, 0, 3);
                                @endphp
                                @if(count($matchedJobsToShow) > 0)
                                    <div class="mb-5">
                                        <span class="text-sm font-semibold text-gray-700 block mb-2">Matched Jobs</span>
                                        <div class="space-y-3">
                                            @foreach($matchedJobsToShow as $job)
                                                @php
                                                    $percent = null;
                                                    if (isset($job['deepseek_score'])) {
                                                        $percent = round($job['deepseek_score'] * 100, 1);
                                                    } elseif (isset($job['score'])) {
                                                        $val = floatval($job['score']);
                                                        // Clamp to [0,1] for display safety
                                                        if ($val < 0) { $val = 0; }
                                                        if ($val > 1) { $val = 1; }
                                                        $percent = round($val * 100, 1);
                                                    }
                                                @endphp
                                                <div class="p-3 bg-white border border-blue-100 rounded-lg shadow-sm">
                                                    <div class="flex items-start justify-between">
                                                        <div>
                                                            <p class="text-sm font-bold text-gray-900">
                                                                {{ $job['title'] ?? 'Job Title' }}
                                                            </p>
                                                            <p class="text-xs text-gray-600">
                                                                {{ $job['company'] ?? 'Company' }}
                                                                @if(!empty($job['location']))
                                                                    • {{ $job['location'] }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                        @if(!empty($job['url']))
                                                            <a href="{{ $job['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-[#1193d4] hover:underline">
                                                                View
                                                            </a>
                                                        @endif
                                                    </div>
                                                    @if($percent !== null)
                                                        <p class="text-xs text-blue-600 mt-1">Match: {{ $percent }}%</p>
                                                    @endif
                                                    @if(!empty($job['deepseek_reason']))
                                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $job['deepseek_reason'] }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Extracted Information -->
                                @php
                                    // Extract Program/Degree strictly relying on AI-provided fields to prevent huge text block leaks
                                    $degreeProgram = null;

                                    // 1) Trust the explicit degree/program returned by AI first
                                    foreach (['degree', 'program', 'course', 'education_degree'] as $k) {
                                        if (!$degreeProgram && !empty($user->ai_analysis[$k]) && is_string($user->ai_analysis[$k])) {
                                            $degreeProgram = trim($user->ai_analysis[$k]);
                                        }
                                    }

                                    // 2) Safe fallback: Check education entries but limit string length to < 100 chars to avoid rendering paragraphs
                                    if (!$degreeProgram && isset($user->ai_analysis['education']) && is_array($user->ai_analysis['education'])) {
                                        foreach ($user->ai_analysis['education'] as $edu) {
                                            if (is_array($edu)) {
                                                foreach ($edu as $val) {
                                                    if (is_string($val) && strlen($val) < 100 && preg_match('/\b(bachelor|master|doctor|bs|ba|mba|phd)\b/i', $val)) {
                                                        $degreeProgram = trim($val);
                                                        break 2;
                                                    }
                                                }
                                            } elseif (is_string($edu) && strlen($edu) < 100 && preg_match('/\b(bachelor|master|doctor|bs|ba|mba|phd)\b/i', $edu)) {
                                                $degreeProgram = trim($edu);
                                                break;
                                            }
                                        }
                                    }
                                @endphp

                                <div class="mt-6 pt-4 border-t border-blue-200">
                                    <h4 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">Extracted Information</h4>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-xs font-semibold text-gray-600">Program / Degree:</span>
                                            <span class="text-xs text-gray-800 text-right">
                                                {{ $degreeProgram ?? 'Not found' }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="text-xs font-semibold text-gray-600">Name:</span>
                                            <span class="text-xs text-gray-800 text-right">
                                                {{ $user->ai_analysis['name'] ?? ($user->first_name && $user->last_name ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : ($user->name ?? 'Not found')) }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex justify-between">
                                            <span class="text-xs font-semibold text-gray-600">Email:</span>
                                            <span class="text-xs text-gray-800 text-right break-all">{{ $user->ai_analysis['email'] ?? $user->email ?? 'Not found' }}</span>
                                        </div>
                                        
                                        @if(isset($user->ai_analysis['experience']) && count($user->ai_analysis['experience']) > 0)
                                            <div class="flex justify-between">
                                                <span class="text-xs font-semibold text-gray-600">Experience:</span>
                                                <span class="text-xs text-gray-800">{{ count($user->ai_analysis['experience']) }} position(s)</span>
                                            </div>
                                        @endif
                                        
                                        @if(isset($user->ai_analysis['education']) && count($user->ai_analysis['education']) > 0)
                                            <div class="flex justify-between">
                                                <span class="text-xs font-semibold text-gray-600">Education:</span>
                                                <span class="text-xs text-gray-800">{{ count($user->ai_analysis['education']) }} entry(ies)</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            @if($user->resume_path)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Resume Uploaded</h3>
                                    <p class="text-sm text-gray-700 mb-4">
                                        Your resume has been uploaded, but AI analysis is not available yet.
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        <strong>Resume:</strong> {{ basename($user->resume_path) }}<br>
                                        <strong>Uploaded:</strong> {{ $user->updated_at->format('M d, Y H:i') }}
                                    </p>
                                    <div class="mt-4 p-3 bg-yellow-100 rounded">
                                        <p class="text-xs font-semibold text-yellow-900 mb-2">To enable AI analysis:</p>
                                        <ol class="text-xs text-yellow-800 list-decimal list-inside space-y-1">
                                            <li>Make sure Flask API is running (check the Flask console window)</li>
                                            <li>Upload your resume again to trigger analysis</li>
                                            <li>Check Laravel logs if issues persist: <code class="bg-white px-1 rounded">storage\logs\laravel.log</code></li>
                                        </ol>
                                    </div>
                                </div>
                            @else
                                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs text-gray-500">Upload a resume to see AI analysis results</span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Skills Modal Script -->
<script>
    function openSkillsModal(skills) {
        const modal = document.getElementById('skills-modal');
        const container = document.getElementById('skills-container');
        const skillsCount = document.getElementById('skills-count');
        
        // Update skills count
        skillsCount.textContent = `(${skills.length})`;
        
        // Clear previous skills
        container.innerHTML = '';
        
        // Add all skills
        skills.forEach((skill, index) => {
            const skillBadge = document.createElement('span');
            skillBadge.className = 'px-3 py-1.5 bg-white border-2 border-gray-300 rounded-full text-sm text-gray-700 hover:bg-gray-50 hover:border-[#1193d4] transition-all duration-200 cursor-default';
            skillBadge.textContent = skill;
            skillBadge.style.animation = `fadeIn 0.3s ease-in ${index * 0.02}s both`;
            container.appendChild(skillBadge);
        });
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeSkillsModal() {
        const modal = document.getElementById('skills-modal');
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSkillsModal();
        }
    });
    
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('skills-modal');
        if (event.target === modal) {
            closeSkillsModal();
        }
    });
</script>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #skills-modal {
        backdrop-filter: blur(4px);
    }
    
    #skills-container::-webkit-scrollbar {
        width: 8px;
    }
    
    #skills-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    #skills-container::-webkit-scrollbar-thumb {
        background: #1193d4;
        border-radius: 10px;
    }
    
    #skills-container::-webkit-scrollbar-thumb:hover {
        background: #0f83bd;
    }
</style>
@endsection
