@extends('layouts.app')

@section('content')
<div class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 font-display text-gray-900">
    <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900">Recommended Jobs</h2>
                        <p class="text-base sm:text-lg text-gray-600 mt-1">Based on your resume analysis.</p>
                    </div>
                    <a href="{{ route('jobs') }}?refresh=true" class="flex items-center gap-2 text-[#1193d4] hover:text-[#0f83bd] text-sm font-medium whitespace-nowrap transition-colors px-4 py-2 rounded-lg border border-[#1193d4] hover:bg-[#1193d4]/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh Jobs
                    </a>
                </div>
            </div>

            <!-- AI Profile Summary -->
            @if(auth()->user()->ai_analysis)
            <div class="mb-6 sm:mb-8 p-4 sm:p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                    Your AI-Powered Profile
                </h3>
                <p class="text-gray-600 mt-1 mb-4 sm:mb-6 text-sm sm:text-base">Jobs are matched based on your resume analysis.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Top Skills</h4>
                        <div class="flex flex-wrap gap-2">
                            @if(isset(auth()->user()->ai_analysis['skills']) && count(auth()->user()->ai_analysis['skills']) > 0)
                                @foreach(array_slice(auth()->user()->ai_analysis['skills'], 0, 5) as $skill)
                                    <span class="bg-[#1193d4]/10 text-[#1193d4] text-sm font-medium px-3 py-1 rounded-full">{{ $skill }}</span>
                                @endforeach
                            @else
                                <span class="text-gray-500 text-sm">No skills extracted</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Recommended Field</h4>
                        <p class="text-[#1193d4] font-bold text-lg">{{ auth()->user()->recommended_field ?? 'Software Engineering' }}</p>
                        @if(auth()->user()->resume_score)
                            <div class="mt-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600">Resume Score</span>
                                    <span class="text-sm font-bold text-[#1193d4]">{{ auth()->user()->resume_score }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-[#1193d4] h-2 rounded-full" style="width: {{ min(auth()->user()->resume_score, 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Job Sources</h4>
                        <ul class="space-y-1 text-gray-600 text-sm">
                            <li>✓ Indeed Philippines</li>
                            <li>✓ Kalibrr</li>
                            <li>✓ OnlineJobs.ph</li>
                            <li>✓ Bossjob</li>
                            <li>✓ WorkAbroad.ph</li>
                            <li>✓ JobsDB</li>
                            <li>✓ JobStreet</li>
                            <li>○ LinkedIn (requires API)</li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <!-- Search + Filters -->
            <form method="GET" action="{{ route('jobs') }}" id="jobs-filter-form" class="mb-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-grow relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                        <input
                            type="text"
                            name="search"
                            id="search-input"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search for jobs (e.g., 'Frontend Developer')"
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white border border-gray-300 focus:ring-2 focus:ring-[#1193d4] focus:border-transparent transition-all text-gray-900 placeholder-gray-400"
                        />
                    </div>
                    <div class="flex gap-2">
                        <!-- Filter Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                type="button"
                                @click="open = !open"
                                class="flex items-center gap-1 sm:gap-2 px-3 sm:px-4 py-2 sm:py-3 rounded-lg bg-white border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors shadow-sm text-sm sm:text-base"
                            >
                                <span class="material-symbols-outlined text-lg sm:text-xl">filter_list</span>
                                <span class="hidden sm:inline">Filter</span>
                                @if(($filters['location'] ?? '') || ($filters['min_score'] ?? '') || ($filters['source'] ?? ''))
                                    <span class="bg-[#1193d4] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ 
                                        (($filters['location'] ?? '') ? 1 : 0) + 
                                        (($filters['min_score'] ?? '') ? 1 : 0) + 
                                        (($filters['source'] ?? '') ? 1 : 0) 
                                    }}</span>
                                @endif
                            </button>
                            <div 
                                x-show="open"
                                @click.away="open = false"
                                x-cloak
                                class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50 p-4"
                            >
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                        <input
                                            type="text"
                                            name="location"
                                            value="{{ $filters['location'] ?? '' }}"
                                            placeholder="e.g., Manila, Quezon City"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1193d4] focus:border-transparent text-gray-900"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Match Score</label>
                                        <select
                                            name="min_score"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1193d4] focus:border-transparent text-gray-900"
                                        >
                                            <option value="">All Scores</option>
                                            <option value="90" {{ ($filters['min_score'] ?? '') == '90' ? 'selected' : '' }}>90%+</option>
                                            <option value="80" {{ ($filters['min_score'] ?? '') == '80' ? 'selected' : '' }}>80%+</option>
                                            <option value="70" {{ ($filters['min_score'] ?? '') == '70' ? 'selected' : '' }}>70%+</option>
                                            <option value="60" {{ ($filters['min_score'] ?? '') == '60' ? 'selected' : '' }}>60%+</option>
                                            <option value="50" {{ ($filters['min_score'] ?? '') == '50' ? 'selected' : '' }}>50%+</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Job Source</label>
                                        <select
                                            name="source"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1193d4] focus:border-transparent text-gray-900"
                                        >
                                            <option value="">All Sources</option>
                                            <option value="Indeed" {{ ($filters['source'] ?? '') == 'Indeed' ? 'selected' : '' }}>Indeed</option>
                                            <option value="Kalibrr" {{ ($filters['source'] ?? '') == 'Kalibrr' ? 'selected' : '' }}>Kalibrr</option>
                                            <option value="OnlineJobs.ph" {{ ($filters['source'] ?? '') == 'OnlineJobs.ph' ? 'selected' : '' }}>OnlineJobs.ph</option>
                                            <option value="Bossjob" {{ ($filters['source'] ?? '') == 'Bossjob' ? 'selected' : '' }}>Bossjob</option>
                                            <option value="WorkAbroad.ph" {{ ($filters['source'] ?? '') == 'WorkAbroad.ph' ? 'selected' : '' }}>WorkAbroad.ph</option>
                                            <option value="JobsDB" {{ ($filters['source'] ?? '') == 'JobsDB' ? 'selected' : '' }}>JobsDB</option>
                                            <option value="JobStreet" {{ ($filters['source'] ?? '') == 'JobStreet' ? 'selected' : '' }}>JobStreet</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            type="submit"
                                            class="flex-1 px-4 py-2 bg-[#1193d4] text-white rounded-lg hover:bg-[#0f83bd] font-medium transition-colors"
                                        >
                                            Apply
                                        </button>
                                        <a
                                            href="{{ route('jobs') }}"
                                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors text-center"
                                        >
                                            Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                type="button"
                                @click="open = !open"
                                class="flex items-center gap-1 sm:gap-2 px-3 sm:px-4 py-2 sm:py-3 rounded-lg bg-white border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors shadow-sm text-sm sm:text-base"
                            >
                                <span class="material-symbols-outlined text-lg sm:text-xl">sort</span>
                                <span class="hidden sm:inline">Sort</span>
                            </button>
                            <div 
                                x-show="open"
                                @click.away="open = false"
                                x-cloak
                                class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-50 p-4"
                            >
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="sort" value="match_score" {{ ($sort ?? 'match_score') == 'match_score' ? 'checked' : '' }} class="text-[#1193d4]">
                                        <span class="text-sm text-gray-700">Match Score</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="sort" value="date" {{ ($sort ?? '') == 'date' ? 'checked' : '' }} class="text-[#1193d4]">
                                        <span class="text-sm text-gray-700">Date Posted</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="sort" value="title" {{ ($sort ?? '') == 'title' ? 'checked' : '' }} class="text-[#1193d4]">
                                        <span class="text-sm text-gray-700">Job Title</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="sort" value="company" {{ ($sort ?? '') == 'company' ? 'checked' : '' }} class="text-[#1193d4]">
                                        <span class="text-sm text-gray-700">Company</span>
                                    </label>
                                    <div class="pt-2 border-t border-gray-200 mt-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="order" value="desc" {{ ($sortOrder ?? 'desc') == 'desc' ? 'checked' : '' }} class="text-[#1193d4]">
                                            <span class="text-sm text-gray-700">High to Low</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="order" value="asc" {{ ($sortOrder ?? '') == 'asc' ? 'checked' : '' }} class="text-[#1193d4]">
                                            <span class="text-sm text-gray-700">Low to High</span>
                                        </label>
                                    </div>
                                    <button
                                        type="submit"
                                        class="w-full mt-3 px-4 py-2 bg-[#1193d4] text-white rounded-lg hover:bg-[#0f83bd] font-medium transition-colors"
                                    >
                                        Apply Sort
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Preserve refresh parameter -->
                @if(request('refresh'))
                    <input type="hidden" name="refresh" value="1">
                @endif
            </form>

            <!-- No jobs found message -->
            @if(empty($jobs) || count($jobs) === 0)
                <div class="mb-6 p-8 bg-gray-50 border border-gray-200 rounded-xl text-center">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <p class="text-lg font-semibold text-gray-700 mb-1">No Jobs Found</p>
                            <p class="text-sm text-gray-600">No jobs are currently available. Please check back later or try refreshing.</p>
                        </div>
                    </div>
                </div>
            @elseif(count($jobs) > 0)
                <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">✓ Live Jobs</span> Showing {{ count($jobs) }} real-time job{{ count($jobs) !== 1 ? 's' : '' }} fetched from OnlineJobs.ph, Indeed, and other Philippine job sites.
                    </p>
                </div>
            @endif

            <!-- Job Cards -->
            <div class="space-y-6" id="jobs-list">
                @if(isset($jobs) && count($jobs) > 0)
                    @php
                        $currentPage = request()->get('page', 1);
                        $perPage = 12;
                        $startIndex = ($currentPage - 1) * $perPage;
                        $paginatedJobs = array_slice($jobs, $startIndex, $perPage);
                    @endphp
                    @foreach($paginatedJobs as $job)
                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-1 {{ ($job['match_score'] ?? 0) >= 80 ? 'border-2 border-[#1193d4]' : '' }}">
                            <div class="md:flex">
                                <div class="md:flex-shrink-0 bg-gradient-to-br from-[#1193d4]/20 to-[#1193d4]/5 w-full md:w-48 h-32 md:h-auto flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[#1193d4] text-4xl sm:text-5xl md:text-6xl">work</span>
                                </div>
                                <div class="p-4 sm:p-6 flex-grow flex flex-col justify-between">
                                    <div>
                                        @if(($job['match_score'] ?? 0) >= 80)
                                            <div class="uppercase tracking-wide text-sm text-[#1193d4] font-semibold mb-2">High Match Job</div>
                                        @endif
                                        <a href="{{ $job['url'] ?? '#' }}" target="_blank" class="block mt-1 text-lg sm:text-xl leading-tight font-bold text-gray-900 hover:text-[#1193d4] transition-colors">
                                            {{ $job['title'] ?? 'Job Title' }}
                                        </a>
                                        <p class="mt-2 text-sm sm:text-base text-gray-600">
                                            <span class="font-medium">{{ $job['company'] ?? 'Company Not Specified' }}</span>
                                            <span class="hidden sm:inline"> | </span>
                                            <span class="block sm:inline">{{ $job['location'] ?? 'Philippines' }}</span>
                                        </p>
                                        @if(isset($job['description']) && strlen($job['description']) > 0)
                                            <p class="mt-3 text-gray-500 text-sm line-clamp-2">{{ Str::limit(strip_tags($job['description']), 150) }}</p>
                                        @endif
                                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                                            <span class="bg-[#1193d4] text-white text-xs font-semibold px-3 py-1 rounded-full">
                                                {{ $job['match_percentage'] ?? $job['match_score'] ?? 0 }}% Match
                                            </span>
                                            @if(isset($job['source']))
                                                <span class="text-xs text-gray-500">
                                                    via {{ $job['source'] }}
                                                </span>
                                            @endif
                                            @if(isset($job['published_at']))
                                                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($job['published_at'])->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ $job['url'] ?? '#' }}" target="_blank" class="bg-[#1193d4] text-white font-bold py-2 px-4 rounded-lg hover:bg-[#0f83bd] transition-colors flex items-center gap-2 shadow-sm hover:shadow-md inline-block">
                                            View Job
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">work_off</span>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No jobs found</h3>
                        <p class="text-gray-600 mb-6">
                            @if(auth()->user()->ai_analysis)
                                We couldn't find any jobs matching your profile at the moment. Try adjusting your filters or check back later.
                            @else
                                Upload your resume to get personalized job recommendations!
                            @endif
                        </p>
                        @if(!auth()->user()->ai_analysis)
                            <a href="{{ route('resume.upload') }}" class="inline-block px-6 py-3 bg-[#1193d4] text-white rounded-lg hover:bg-[#0f83bd] font-semibold">
                                Upload Resume
                            </a>
                        @endif
                    </div>
                @endif
            </div>
            
            <!-- Pagination -->
            @if(isset($jobs) && count($jobs) > 0)
                @php
                    $currentPage = request()->get('page', 1);
                    $perPage = 12;
                    $totalJobs = count($jobs);
                    $totalPages = ceil($totalJobs / $perPage);
                    $startIndex = ($currentPage - 1) * $perPage;
                    $endIndex = min($startIndex + $perPage, $totalJobs);
                    $paginatedJobs = array_slice($jobs, $startIndex, $perPage);
                @endphp
                
                @if($totalPages > 1)
                    <div class="mt-6 flex items-center justify-center gap-2 flex-wrap">
                        <!-- Previous Button -->
                        @if($currentPage > 1)
                            <a href="{{ route('jobs', array_merge(request()->query(), ['page' => $currentPage - 1])) }}" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </span>
                        @endif
                        
                        <!-- Page Numbers (compact) -->
                        @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                            @if($i == $currentPage)
                                <span class="px-3 py-2 text-sm font-semibold text-white bg-[#1193d4] rounded-lg">{{ $i }}</span>
                            @else
                                <a href="{{ route('jobs', array_merge(request()->query(), ['page' => $i])) }}" 
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    {{ $i }}
                                </a>
                            @endif
                        @endfor
                        
                        <!-- Next Button -->
                        @if($currentPage < $totalPages)
                            <a href="{{ route('jobs', array_merge(request()->query(), ['page' => $currentPage + 1])) }}" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-base">chevron_right</span>
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                <span class="material-symbols-outlined text-base">chevron_right</span>
                            </span>
                        @endif
                        
                        <!-- Page Info (compact) -->
                        <span class="px-3 py-2 text-xs text-gray-500">
                            Showing {{ $startIndex + 1 }}-{{ $endIndex }} of {{ $totalJobs }}
                        </span>
                    </div>
                @endif
            @endif
        </div>
    </main>
    
    <script>
        // Auto-submit search on Enter key
        document.getElementById('search-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('jobs-filter-form').submit();
            }
        });
    </script>
    
    @stack('scripts')
</div>
@endsection

