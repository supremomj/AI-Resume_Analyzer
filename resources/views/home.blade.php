@extends('layouts.app')

@section('title', 'JobMatch PH - Dashboard')

@section('content')
<div class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 font-display">
    <div class="layout-container flex h-full grow flex-col">
        <main class="flex flex-1 justify-center">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Success Message (e.g., after email verification) -->
                @if(session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl shadow-sm">
                        <p class="text-sm text-green-800 font-semibold flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('status') }}
                        </p>
                    </div>
                @endif

                <!-- PageHeading -->
                <div class="mb-6 sm:mb-8">
                    <h1 class="text-gray-900 text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-tight mb-2">
                        Welcome back, <span class="text-[#1193d4]">{{ auth()->user()->first_name ?? auth()->user()->name ?? 'User' }}</span>!
                    </h1>
                    <p class="text-gray-600 text-base sm:text-lg font-normal">
                        Here's your personalized job dashboard.
                    </p>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- New Job Matches Section -->
                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-0 mb-4">
                                <h2 class="text-gray-900 text-xl sm:text-2xl font-bold">New Job Matches For You</h2>
                                <div class="flex items-center gap-3">
                                    <button id="refresh-jobs-btn" onclick="loadJobs(true)" class="flex items-center gap-2 text-[#1193d4] hover:text-[#0f83bd] text-sm font-medium whitespace-nowrap transition-colors">
                                        <svg id="refresh-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <span id="refresh-text">Refresh</span>
                                    </button>
                                    <a href="{{ route('jobs') }}" class="text-[#1193d4] hover:text-[#0f83bd] text-sm font-medium whitespace-nowrap">View All →</a>
                                </div>
                            </div>
                            
                            <!-- Search and Filter Section -->
                            <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-grow relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                        <input
                                            type="text"
                                            id="job-search-input"
                                            placeholder="Search jobs by title, company, or location..."
                                            class="w-full pl-10 pr-4 py-2.5 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-[#1193d4] focus:border-transparent transition-all text-gray-900 placeholder-gray-400"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <select id="filter-location" class="px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-[#1193d4] focus:border-transparent text-gray-900 text-sm">
                                            <option value="">All Locations</option>
                                            <option value="Manila">Manila</option>
                                            <option value="Makati">Makati</option>
                                            <option value="Taguig">Taguig</option>
                                            <option value="Quezon City">Quezon City</option>
                                            <option value="Remote">Remote</option>
                                        </select>
                                        <select id="filter-match-score" class="px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-[#1193d4] focus:border-transparent text-gray-900 text-sm">
                                            <option value="">All Match Scores</option>
                                            <option value="80">80%+ Match</option>
                                            <option value="60">60%+ Match</option>
                                            <option value="40">40%+ Match</option>
                                        </select>
                                        <button onclick="clearFilters()" class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors text-sm font-medium">
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="jobs-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Skeleton Loaders (shown initially) -->
                                <div id="skeleton-loaders" class="col-span-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-5 animate-pulse">
                                        <div class="w-full bg-gray-200 aspect-[4/3] rounded-lg mb-4"></div>
                                        <div class="h-5 bg-gray-200 rounded mb-2"></div>
                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                                        <div class="h-10 bg-gray-200 rounded"></div>
                                    </div>
                                    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-5 animate-pulse">
                                        <div class="w-full bg-gray-200 aspect-[4/3] rounded-lg mb-4"></div>
                                        <div class="h-5 bg-gray-200 rounded mb-2"></div>
                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                                        <div class="h-10 bg-gray-200 rounded"></div>
                                    </div>
                                    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-5 animate-pulse">
                                        <div class="w-full bg-gray-200 aspect-[4/3] rounded-lg mb-4"></div>
                                        <div class="h-5 bg-gray-200 rounded mb-2"></div>
                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                                        <div class="h-10 bg-gray-200 rounded"></div>
                                    </div>
                                    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-5 animate-pulse">
                                        <div class="w-full bg-gray-200 aspect-[4/3] rounded-lg mb-4"></div>
                                        <div class="h-5 bg-gray-200 rounded mb-2"></div>
                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                                        <div class="h-10 bg-gray-200 rounded"></div>
                                    </div>
                                    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-5 animate-pulse">
                                        <div class="w-full bg-gray-200 aspect-[4/3] rounded-lg mb-4"></div>
                                        <div class="h-5 bg-gray-200 rounded mb-2"></div>
                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                                        <div class="h-10 bg-gray-200 rounded"></div>
                                    </div>
                                    <div class="flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-5 animate-pulse">
                                        <div class="w-full bg-gray-200 aspect-[4/3] rounded-lg mb-4"></div>
                                        <div class="h-5 bg-gray-200 rounded mb-2"></div>
                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                                        <div class="h-10 bg-gray-200 rounded"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pagination Controls -->
                            <div id="jobs-pagination" class="mt-6 flex items-center justify-center gap-2 flex-wrap hidden">
                                <!-- Previous Button -->
                                <button id="prev-page-btn" onclick="changePage(currentPage - 1)" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                        Previous
                                    </span>
                                </button>
                                
                                <!-- Page Numbers -->
                                <div id="page-numbers" class="flex items-center gap-1">
                                    <!-- Page numbers will be inserted here -->
                                </div>
                                
                                <!-- Next Button -->
                                <button id="next-page-btn" onclick="changePage(currentPage + 1)" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium">
                                    <span class="flex items-center gap-1">
                                        Next
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            
                            <script>
                                let refreshInterval;
                                let autoRefreshInterval;
                                let lastRefreshTime = null;
                                let currentPage = 1;
                                let totalPages = 1;
                                let jobsPerPage = 6;
                                let allJobs = [];
                                
                                // Auto-refresh jobs every 5-10 minutes (randomized)
                                function startAutoRefresh() {
                                    // Clear existing interval if any
                                    if (autoRefreshInterval) {
                                        clearInterval(autoRefreshInterval);
                                    }
                                    
                                    // Random interval between 5-10 minutes
                                    const minInterval = 5 * 60 * 1000; // 5 minutes
                                    const maxInterval = 10 * 60 * 1000; // 10 minutes
                                    const randomInterval = Math.floor(Math.random() * (maxInterval - minInterval + 1)) + minInterval;
                                    
                                    autoRefreshInterval = setInterval(() => {
                                        console.log('Auto-refreshing jobs...');
                                        loadJobs(true, 1); // Force refresh
                                    }, randomInterval);
                                    
                                    const minutes = Math.round(randomInterval / 1000 / 60);
                                    console.log(`Auto-refresh scheduled in ${minutes} minutes`);
                                }
                                
                                // Stop auto-refresh
                                function stopAutoRefresh() {
                                    if (autoRefreshInterval) {
                                        clearInterval(autoRefreshInterval);
                                        autoRefreshInterval = null;
                                    }
                                }
                                
                                function loadJobs(forceRefresh = false, page = 1) {
                                    const container = document.getElementById('jobs-container');
                                    const refreshBtn = document.getElementById('refresh-jobs-btn');
                                    const refreshIcon = document.getElementById('refresh-icon');
                                    const refreshText = document.getElementById('refresh-text');
                                    
                                    // Show loading state (skeleton loaders are already shown)
                                    if (forceRefresh || page === 1) {
                                        if (forceRefresh) {
                                            refreshBtn.disabled = true;
                                            refreshIcon.classList.add('animate-spin');
                                            refreshText.textContent = 'Refreshing...';
                                        }
                                        // Keep skeleton loaders visible, they'll be replaced when jobs load
                                    }
                                    
                                    const url = '{{ route("jobs.home") }}?limit=30' + (forceRefresh ? '&refresh=true' : '');
                                    
                                    fetch(url)
                                        .then(response => {
                                            if (!response.ok) {
                                                throw new Error(`HTTP error! status: ${response.status}`);
                                            }
                                            return response.json();
                                        })
                                        .then(data => {
                                            // Reset refresh button
                                            if (forceRefresh) {
                                                refreshBtn.disabled = false;
                                                refreshIcon.classList.remove('animate-spin');
                                                refreshText.textContent = 'Refresh';
                                                lastRefreshTime = new Date();
                                                updateLastRefreshTime();
                                            }
                                            
                                            if (data.success && data.jobs && data.jobs.length > 0) {
                                                // Filter out any sample jobs (shouldn't exist anymore, but just in case)
                                                allJobs = data.jobs.filter(job => !job.is_sample && job.source !== 'Sample Jobs');
                                                
                                                if (allJobs.length > 0) {
                                                    console.log('✅ Jobs found:', allJobs.length);
                                                } else {
                                                    console.log('⚠️ No real jobs available.');
                                                    allJobs = []; // Empty array for blank state
                                                }
                                                
                                                totalPages = Math.ceil(allJobs.length / jobsPerPage);
                                                currentPage = Math.min(page, totalPages);
                                                
                                                displayJobs();
                                                updatePagination();
                                            } else {
                                                const container = document.getElementById('jobs-container');
                                                const pagination = document.getElementById('jobs-pagination');
                                                const skeletonLoaders = document.getElementById('skeleton-loaders');
                                                
                                                if (skeletonLoaders) skeletonLoaders.style.display = 'none';
                                                
                                                const hasAiAnalysis = {{ auth()->user()->ai_analysis ? 'true' : 'false' }};
                                                if (!hasAiAnalysis) {
                                                    showEmptyState(container, 'no-resume');
                                                } else {
                                                    showEmptyState(container, 'no-jobs');
                                                }
                                                pagination.classList.add('hidden');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error loading jobs:', error);
                                            const container = document.getElementById('jobs-container');
                                            const pagination = document.getElementById('jobs-pagination');
                                            const skeletonLoaders = document.getElementById('skeleton-loaders');
                                            const refreshBtn = document.getElementById('refresh-jobs-btn');
                                            const refreshIcon = document.getElementById('refresh-icon');
                                            const refreshText = document.getElementById('refresh-text');
                                            
                                            if (skeletonLoaders) skeletonLoaders.style.display = 'none';
                                            
                                            if (forceRefresh) {
                                                refreshBtn.disabled = false;
                                                refreshIcon.classList.remove('animate-spin');
                                                refreshText.textContent = 'Refresh';
                                            }
                                            
                                            showEmptyState(container, 'error');
                                            pagination.classList.add('hidden');
                                        });
                                }
                                
                                function displayJobs() {
                                    const container = document.getElementById('jobs-container');
                                    const skeletonLoaders = document.getElementById('skeleton-loaders');
                                    
                                    // Hide skeleton loaders
                                    if (skeletonLoaders) {
                                        skeletonLoaders.style.display = 'none';
                                    }
                                    
                                    const startIndex = (currentPage - 1) * jobsPerPage;
                                    const endIndex = startIndex + jobsPerPage;
                                    const pageJobs = allJobs.slice(startIndex, endIndex);
                                    
                                    // Sample jobs removed - no longer checking for sample jobs
                                    // Show empty state if no jobs available
                                    if (allJobs.length === 0 || pageJobs.length === 0) {
                                        showEmptyState(container, 'no-jobs');
                                        return;
                                    }
                                    
                                    // No sample jobs notice needed - removed sample jobs feature
                                    let headerHtml = '';
                                    
                                    container.innerHTML = headerHtml + pageJobs.map(job => {
                                        // Get job title - ensure it's clean and not the description
                                        let jobTitle = (job.title || 'Job Title').trim();
                                        // Remove HTML tags and entities from title
                                        jobTitle = jobTitle.replace(/<[^>]*>/g, '').trim();
                                        // Remove HTML entities like &nbsp;, &amp;, etc.
                                        jobTitle = jobTitle.replace(/&[a-z]+;/gi, ' ').trim();
                                        // Remove "br />" and similar patterns
                                        jobTitle = jobTitle.replace(/\s*(br\s*\/?|<\/?[^>]+>)\s*/gi, ' ').trim();
                                        // Clean up multiple spaces
                                        jobTitle = jobTitle.replace(/\s+/g, ' ').trim();
                                        
                                        // If title contains <br /> or description text, extract only the title part
                                        if (jobTitle.includes('<br') || jobTitle.includes('br />')) {
                                            // Split on br tags and take first part
                                            const parts = jobTitle.split(/<br\s*\/?>/i);
                                            if (parts[0] && parts[0].trim().length > 5) {
                                                jobTitle = parts[0].trim();
                                            }
                                        }
                                        
                                        // Validate title doesn't look like description
                                        const descriptionPatterns = /^(we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i;
                                        if (descriptionPatterns.test(jobTitle)) {
                                            // If title looks like description, try to extract actual title
                                            // Look for common job title patterns before description words
                                            const titleMatch = jobTitle.match(/([A-Z][A-Za-z\s&,\-()]{5,80})\s*(?:we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i);
                                            if (titleMatch && titleMatch[1] && titleMatch[1].trim().length > 5) {
                                                jobTitle = titleMatch[1].trim();
                                            } else {
                                                // Fallback: use first part before common description words
                                                const parts = jobTitle.split(/\s+(?:we are|looking for|join|hiring|need|fast-growing|skilled|this role|technology|data engineering|based|developer|create|role is)/i);
                                                if (parts[0] && parts[0].trim().length > 5) {
                                                    jobTitle = parts[0].trim();
                                                } else {
                                                    // Last resort: if still looks like description, use generic
                                                    jobTitle = 'Job Opportunity';
                                                }
                                            }
                                        }
                                        
                                        // Final cleanup - remove any remaining HTML artifacts
                                        jobTitle = jobTitle.replace(/&[a-z]+;/gi, ' ').trim();
                                        jobTitle = jobTitle.replace(/\s+/g, ' ').trim();
                                        
                                        // Get description - use actual description if available
                                        let description = (job.description || '').trim();
                                        // Clean up description - remove HTML tags if any
                                        if (description) {
                                            description = description.replace(/<[^>]*>/g, '').trim();
                                        }
                                        
                                        // Check if description is the same as title (common issue)
                                        if (description && description.toLowerCase() === jobTitle.toLowerCase()) {
                                            description = ''; // Clear it if it's the same as title
                                        }
                                        
                                        // If no description or description is too short, show a generic message
                                        if (!description || description.length < 20) {
                                            description = `Join ${job.company || 'our team'} as a ${jobTitle || 'professional'}. Click to view full details and requirements.`;
                                        } else {
                                            // Limit to 150 characters for display
                                            if (description.length > 150) {
                                                description = description.substring(0, 150) + '...';
                                            }
                                        }
                                        
                                        const jobUrl = job.url || '#';
                                        const jobUrlEncoded = encodeURIComponent(jobUrl);
                                        const matchScore = job.match_percentage || job.match_score || 0;
                                        
                                        // Determine job type badge (extract from title or description)
                                        const jobType = jobTitle.toLowerCase().includes('remote') || (description || '').toLowerCase().includes('remote') ? 'Remote' :
                                                      jobTitle.toLowerCase().includes('part') ? 'Part-time' :
                                                      jobTitle.toLowerCase().includes('contract') ? 'Contract' : 'Full-time';
                                        
                                        // Extract salary if available (look for common patterns)
                                        let salaryInfo = '';
                                        const salaryMatch = (description || '').match(/(?:₱|PHP|php|peso)[\s,]*(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)/i);
                                        if (salaryMatch) {
                                            salaryInfo = `₱${salaryMatch[1]}`;
                                        }
                                        
                                        // Match score color
                                        const matchColor = matchScore >= 80 ? 'bg-green-500' : matchScore >= 60 ? 'bg-[#1193d4]' : matchScore >= 40 ? 'bg-yellow-500' : 'bg-gray-500';
                                        
                                        return `
                                        <div class="flex flex-col bg-white text-gray-900 rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-lg hover:border-[#1193d4]/30 transition-all duration-200 group" data-job-url="${jobUrlEncoded}" data-job-title="${jobTitle.toLowerCase()}" data-job-company="${(job.company || '').toLowerCase()}" data-job-location="${(job.location || '').toLowerCase()}">
                                            <div class="relative w-full bg-gradient-to-br from-[#1193d4]/20 via-[#1193d4]/10 to-[#1193d4]/5 aspect-[4/3] rounded-lg mb-4 overflow-hidden flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[#1193d4] text-5xl opacity-50">work</span>
                                                <div class="absolute top-2 right-2 ${matchColor} text-white text-xs font-semibold px-2.5 py-1 rounded-full shadow-md">${matchScore}% Match</div>
                                                <button type="button" 
                                                        class="absolute top-2 left-2 bookmark-btn p-2 rounded-full bg-white/90 hover:bg-white shadow-md transition-all duration-200 z-10"
                                                        data-job-url="${jobUrlEncoded}"
                                                        data-job-title="${jobTitle.replace(/"/g, '&quot;')}"
                                                        data-job-company="${(job.company || '').replace(/"/g, '&quot;')}"
                                                        data-job-location="${(job.location || '').replace(/"/g, '&quot;')}"
                                                        data-job-source="${(job.source || '').replace(/"/g, '&quot;')}"
                                                        data-job-description="${description.replace(/"/g, '&quot;')}"
                                                        data-match-score="${matchScore}"
                                                        aria-label="Bookmark this job">
                                                    <svg class="w-5 h-5 text-gray-400 bookmark-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                    </svg>
                                                    <svg class="w-5 h-5 text-[#1193d4] bookmark-icon-filled hidden" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <h3 class="text-gray-900 text-lg font-bold line-clamp-2 flex-1">${jobTitle}</h3>
                                                </div>
                                                <p class="text-gray-600 text-sm font-medium mb-2">
                                                    ${job.company || 'Company Not Specified'}
                                                </p>
                                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                                    <span class="inline-flex items-center gap-1 text-gray-500 text-xs">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        ${job.location || 'Philippines'}
                                                    </span>
                                                    <span class="px-2 py-0.5 bg-[#1193d4]/10 text-[#1193d4] text-xs font-medium rounded-full">${jobType}</span>
                                                    ${salaryInfo ? `<span class="px-2 py-0.5 bg-green-50 text-green-700 text-xs font-medium rounded-full">${salaryInfo}</span>` : ''}
                                                </div>
                                                <p class="text-gray-600 text-sm mb-3 line-clamp-2 leading-relaxed">
                                                    ${description}
                                                </p>
                                                ${job.source ? `<p class="text-gray-400 text-xs mb-2 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                    </svg>
                                                    via ${job.source}
                                                </p>` : ''}
                                            </div>
                                            <div class="flex gap-2 mt-auto pt-3">
                                                <a href="${jobUrl}" target="_blank" onclick="trackJobView('${jobUrlEncoded}', '${jobTitle.replace(/'/g, "\\'")}', '${(job.company || '').replace(/'/g, "\\'")}', '${(job.location || '').replace(/'/g, "\\'")}', '${(job.source || '').replace(/'/g, "\\'")}', '${matchScore}')" class="flex-1 rounded-lg h-10 px-4 bg-[#1193d4] text-white text-sm font-semibold hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                                                    <span>View Details</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                    }).join('');
                                }
                                
                                function changePage(page) {
                                    if (page < 1 || page > totalPages) return;
                                    
                                    currentPage = page;
                                    displayJobs();
                                    updatePagination();
                                    
                                    // Scroll to top of jobs section
                                    document.getElementById('jobs-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                                
                                function updatePagination() {
                                    const pagination = document.getElementById('jobs-pagination');
                                    const prevBtn = document.getElementById('prev-page-btn');
                                    const nextBtn = document.getElementById('next-page-btn');
                                    const pageNumbers = document.getElementById('page-numbers');
                                    
                                    if (totalPages <= 1) {
                                        pagination.classList.add('hidden');
                                        return;
                                    }
                                    
                                    pagination.classList.remove('hidden');
                                    
                                    // Update Previous/Next buttons
                                    prevBtn.disabled = currentPage === 1;
                                    nextBtn.disabled = currentPage === totalPages;
                                    
                                    // Generate page numbers
                                    let pageHtml = '';
                                    const maxVisiblePages = 5;
                                    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
                                    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
                                    
                                    // Adjust start if we're near the end
                                    if (endPage - startPage < maxVisiblePages - 1) {
                                        startPage = Math.max(1, endPage - maxVisiblePages + 1);
                                    }
                                    
                                    // First page
                                    if (startPage > 1) {
                                        pageHtml += `
                                            <button onclick="changePage(1)" class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">1</button>
                                        `;
                                        if (startPage > 2) {
                                            pageHtml += `<span class="px-2 text-gray-500">...</span>`;
                                        }
                                    }
                                    
                                    // Page numbers
                                    for (let i = startPage; i <= endPage; i++) {
                                        if (i === currentPage) {
                                            pageHtml += `
                                                <button class="px-3 py-2 rounded-lg bg-[#1193d4] text-white font-semibold text-sm shadow-sm">${i}</button>
                                            `;
                                        } else {
                                            pageHtml += `
                                                <button onclick="changePage(${i})" class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">${i}</button>
                                            `;
                                        }
                                    }
                                    
                                    // Last page
                                    if (endPage < totalPages) {
                                        if (endPage < totalPages - 1) {
                                            pageHtml += `<span class="px-2 text-gray-500">...</span>`;
                                        }
                                        pageHtml += `
                                            <button onclick="changePage(${totalPages})" class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">${totalPages}</button>
                                        `;
                                    }
                                    
                                    pageNumbers.innerHTML = pageHtml;
                                }
                                
                                function updateLastRefreshTime() {
                                    if (lastRefreshTime) {
                                        const timeAgo = Math.floor((new Date() - lastRefreshTime) / 1000);
                                        const minutes = Math.floor(timeAgo / 60);
                                        const seconds = timeAgo % 60;
                                        
                                        const refreshText = document.getElementById('refresh-text');
                                        if (minutes > 0) {
                                            refreshText.textContent = `Refreshed ${minutes}m ago`;
                                        } else if (seconds > 0) {
                                            refreshText.textContent = `Refreshed ${seconds}s ago`;
                                        } else {
                                            refreshText.textContent = 'Just refreshed';
                                        }
                                        
                                        // Update every 10 seconds
                                        setTimeout(updateLastRefreshTime, 10000);
                                    }
                                }
                                
                                // Bookmark functionality
                                let bookmarkStates = {}; // Cache bookmark states
                                
                                // Use event delegation for bookmark buttons (more reliable)
                                document.addEventListener('click', function(e) {
                                    const bookmarkBtn = e.target.closest('.bookmark-btn');
                                    if (bookmarkBtn) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        toggleBookmark(bookmarkBtn);
                                    }
                                });
                                
                                async function checkBookmarkStatus(jobUrl) {
                                    // Use cached state if available
                                    if (bookmarkStates[jobUrl] !== undefined) {
                                        return bookmarkStates[jobUrl];
                                    }
                                    
                                    try {
                                        // Use query parameter instead of route parameter for better URL handling
                                        const encodedUrl = encodeURIComponent(jobUrl);
                                        const response = await fetch(`/api/bookmarks/check?job_url=${encodedUrl}`, {
                                            headers: {
                                                'Accept': 'application/json',
                                            }
                                        });
                                        
                                        if (!response.ok) {
                                            console.warn('Bookmark check failed:', response.status);
                                            return false;
                                        }
                                        
                                        const data = await response.json();
                                        const isBookmarked = data.success && data.is_bookmarked === true;
                                        bookmarkStates[jobUrl] = isBookmarked;
                                        return isBookmarked;
                                    } catch (error) {
                                        console.error('Error checking bookmark status:', error);
                                        return false;
                                    }
                                }
                                
                                // Get CSRF token
                                function getCsrfToken() {
                                    const token = document.querySelector('meta[name="csrf-token"]');
                                    return token ? token.getAttribute('content') : '{{ csrf_token() }}';
                                }
                                
                                async function toggleBookmark(btn) {
                                    // Prevent multiple clicks
                                    if (btn.disabled || btn.classList.contains('processing')) return;
                                    btn.disabled = true;
                                    btn.classList.add('processing');
                                    
                                    const jobUrl = decodeURIComponent(btn.dataset.jobUrl);
                                    const jobTitle = btn.dataset.jobTitle;
                                    const jobCompany = btn.dataset.jobCompany || '';
                                    const jobLocation = btn.dataset.jobLocation || '';
                                    const jobSource = btn.dataset.jobSource || '';
                                    const jobDescription = btn.dataset.jobDescription || '';
                                    const matchScore = parseInt(btn.dataset.matchScore) || 0;
                                    
                                    const icon = btn.querySelector('.bookmark-icon');
                                    const iconFilled = btn.querySelector('.bookmark-icon-filled');
                                    
                                    // Check current state from DOM and cache
                                    const isCurrentlyBookmarked = (iconFilled && !iconFilled.classList.contains('hidden')) || 
                                                                  bookmarkStates[jobUrl] === true;
                                    
                                    // Optimistic UI update
                                    if (isCurrentlyBookmarked) {
                                        // Remove bookmark
                                        if (icon) icon.classList.remove('hidden');
                                        if (iconFilled) iconFilled.classList.add('hidden');
                                        btn.classList.remove('bg-[#1193d4]/10');
                                        bookmarkStates[jobUrl] = false; // Update cache immediately
                                    } else {
                                        // Add bookmark
                                        if (icon) icon.classList.add('hidden');
                                        if (iconFilled) iconFilled.classList.remove('hidden');
                                        btn.classList.add('bg-[#1193d4]/10');
                                        bookmarkStates[jobUrl] = true; // Update cache immediately
                                    }
                                    
                                    try {
                                        let response;
                                        let responseData;
                                        const csrfToken = getCsrfToken();
                                        
                                        if (isCurrentlyBookmarked) {
                                            // Remove bookmark
                                            const encodedUrl = encodeURIComponent(jobUrl);
                                            response = await fetch(`/api/bookmarks/${encodedUrl}`, {
                                                method: 'DELETE',
                                                headers: {
                                                    'X-CSRF-TOKEN': csrfToken,
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                },
                                            });
                                            
                                            if (!response.ok) {
                                                throw new Error(`HTTP error! status: ${response.status}`);
                                            }
                                            
                                            responseData = await response.json();
                                            
                                            if (responseData.success !== false) {
                                                bookmarkStates[jobUrl] = false;
                                            } else {
                                                // Revert on error
                                                icon.classList.add('hidden');
                                                iconFilled.classList.remove('hidden');
                                                btn.classList.add('bg-[#1193d4]/10');
                                                bookmarkStates[jobUrl] = true;
                                                console.error('Failed to remove bookmark:', responseData.message || 'Unknown error');
                                            }
                                        } else {
                                            // Add bookmark
                                            response = await fetch('/api/bookmarks', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': csrfToken,
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                },
                                                body: JSON.stringify({
                                                    job_title: jobTitle,
                                                    job_url: jobUrl,
                                                    company: jobCompany,
                                                    location: jobLocation,
                                                    source: jobSource,
                                                    match_score: matchScore,
                                                    description: jobDescription,
                                                }),
                                            });
                                            
                                            if (!response.ok) {
                                                throw new Error(`HTTP error! status: ${response.status}`);
                                            }
                                            
                                            responseData = await response.json();
                                            
                                            if (responseData.success !== false) {
                                                bookmarkStates[jobUrl] = true;
                                            } else {
                                                // Revert on error
                                                icon.classList.remove('hidden');
                                                iconFilled.classList.add('hidden');
                                                btn.classList.remove('bg-[#1193d4]/10');
                                                bookmarkStates[jobUrl] = false;
                                                console.error('Failed to add bookmark:', responseData.message || 'Unknown error');
                                            }
                                        }
                                    } catch (error) {
                                        console.error('Error toggling bookmark:', error);
                                        // Revert on error - restore to original state
                                        if (isCurrentlyBookmarked) {
                                            // Was bookmarked, restore to bookmarked state
                                            if (icon) icon.classList.add('hidden');
                                            if (iconFilled) iconFilled.classList.remove('hidden');
                                            btn.classList.add('bg-[#1193d4]/10');
                                            bookmarkStates[jobUrl] = true;
                                        } else {
                                            // Was not bookmarked, restore to unbookmarked state
                                            if (icon) icon.classList.remove('hidden');
                                            if (iconFilled) iconFilled.classList.add('hidden');
                                            btn.classList.remove('bg-[#1193d4]/10');
                                            bookmarkStates[jobUrl] = false;
                                        }
                                        
                                        // Show error notification (if function exists)
                                        if (typeof showNotification === 'function') {
                                            showNotification('Failed to update bookmark. Please try again.', 'error');
                                        } else {
                                            console.error('Failed to update bookmark. Please try again.');
                                        }
                                    } finally {
                                        btn.disabled = false;
                                        btn.classList.remove('processing');
                                    }
                                }
                                
                                // Update bookmark icons after jobs are displayed
                                async function updateBookmarkIcons(forceUpdate = false) {
                                    const bookmarkBtns = document.querySelectorAll('.bookmark-btn');
                                    
                                    // Process buttons in batches to avoid overwhelming the server
                                    const batchSize = 5;
                                    const processedUrls = new Set(); // Track processed URLs to avoid duplicates
                                    
                                    for (let i = 0; i < bookmarkBtns.length; i += batchSize) {
                                        const batch = Array.from(bookmarkBtns).slice(i, i + batchSize);
                                        await Promise.all(batch.map(async (btn) => {
                                            // Get the encoded URL from data attribute
                                            const encodedUrl = btn.dataset.jobUrl;
                                            if (!encodedUrl) return; // Skip if no URL
                                            
                                            const jobUrl = decodeURIComponent(encodedUrl);
                                            
                                            // Skip if already processed in this run (unless forced)
                                            if (!forceUpdate && processedUrls.has(jobUrl)) {
                                                return;
                                            }
                                            processedUrls.add(jobUrl);
                                            
                                            // Check if state is already known (from user action or previous check)
                                            if (bookmarkStates[jobUrl] !== undefined) {
                                                // Use cached state
                                                const isBookmarked = bookmarkStates[jobUrl];
                                                const icon = btn.querySelector('.bookmark-icon');
                                                const iconFilled = btn.querySelector('.bookmark-icon-filled');
                                                
                                                if (isBookmarked) {
                                                    if (icon) icon.classList.add('hidden');
                                                    if (iconFilled) iconFilled.classList.remove('hidden');
                                                    btn.classList.add('bg-[#1193d4]/10');
                                                } else {
                                                    if (icon) icon.classList.remove('hidden');
                                                    if (iconFilled) iconFilled.classList.add('hidden');
                                                    btn.classList.remove('bg-[#1193d4]/10');
                                                }
                                            } else {
                                                // Fetch from server
                                                const isBookmarked = await checkBookmarkStatus(jobUrl);
                                                const icon = btn.querySelector('.bookmark-icon');
                                                const iconFilled = btn.querySelector('.bookmark-icon-filled');
                                                
                                                if (isBookmarked) {
                                                    if (icon) icon.classList.add('hidden');
                                                    if (iconFilled) iconFilled.classList.remove('hidden');
                                                    btn.classList.add('bg-[#1193d4]/10');
                                                } else {
                                                    if (icon) icon.classList.remove('hidden');
                                                    if (iconFilled) iconFilled.classList.add('hidden');
                                                    btn.classList.remove('bg-[#1193d4]/10');
                                                }
                                            }
                                        }));
                                        
                                        // Small delay between batches to avoid rate limiting
                                        if (i + batchSize < bookmarkBtns.length) {
                                            await new Promise(resolve => setTimeout(resolve, 100));
                                        }
                                    }
                                }
                                
                                // Update displayJobs to call updateBookmarkIcons
                                const originalDisplayJobs = displayJobs;
                                displayJobs = function() {
                                    originalDisplayJobs();
                                    // Small delay to ensure DOM is ready
                                    setTimeout(() => {
                                        updateBookmarkIcons(false);
                                    }, 150);
                                };
                                
                                // Search and Filter functionality
                                function filterJobs() {
                                    const searchTerm = document.getElementById('job-search-input').value.toLowerCase();
                                    const locationFilter = document.getElementById('filter-location').value.toLowerCase();
                                    const matchScoreFilter = parseInt(document.getElementById('filter-match-score').value) || 0;
                                    
                                    const jobCards = document.querySelectorAll('[data-job-url]');
                                    let visibleCount = 0;
                                    
                                    jobCards.forEach(card => {
                                        const title = card.dataset.jobTitle || '';
                                        const company = card.dataset.jobCompany || '';
                                        const location = card.dataset.jobLocation || '';
                                        const matchScore = parseInt(card.querySelector('.bookmark-btn')?.dataset.matchScore || 0);
                                        
                                        const matchesSearch = !searchTerm || 
                                            title.includes(searchTerm) || 
                                            company.includes(searchTerm) || 
                                            location.includes(searchTerm);
                                        
                                        const matchesLocation = !locationFilter || location.includes(locationFilter);
                                        const matchesScore = !matchScoreFilter || matchScore >= matchScoreFilter;
                                        
                                        if (matchesSearch && matchesLocation && matchesScore) {
                                            card.style.display = 'flex';
                                            visibleCount++;
                                        } else {
                                            card.style.display = 'none';
                                        }
                                    });
                                    
                                    // Show empty state if no jobs visible
                                    const container = document.getElementById('jobs-container');
                                    let emptyState = container.querySelector('.no-jobs-message');
                                    if (visibleCount === 0 && jobCards.length > 0) {
                                        if (!emptyState) {
                                            emptyState = document.createElement('div');
                                            emptyState.className = 'col-span-full text-center py-12 no-jobs-message';
                                            emptyState.innerHTML = `
                                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                                <p class="text-gray-600 text-lg font-medium mb-2">No jobs match your filters</p>
                                                <p class="text-gray-500 text-sm mb-4">Try adjusting your search or filter criteria</p>
                                                <button onclick="clearFilters()" class="px-4 py-2 bg-[#1193d4] text-white rounded-lg hover:bg-[#0f83bd] transition-colors text-sm font-medium">
                                                    Clear Filters
                                                </button>
                                            `;
                                            container.appendChild(emptyState);
                                        }
                                    } else if (emptyState) {
                                        emptyState.remove();
                                    }
                                }
                                
                                function clearFilters() {
                                    document.getElementById('job-search-input').value = '';
                                    document.getElementById('filter-location').value = '';
                                    document.getElementById('filter-match-score').value = '';
                                    filterJobs();
                                }
                                
                                // Track job view
                                function trackJobView(jobUrl, jobTitle, jobCompany, jobLocation, jobSource, matchScore) {
                                    // Track asynchronously (don't block navigation)
                                    fetch('/api/job-views/track', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': getCsrfToken(),
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            job_title: jobTitle,
                                            job_url: decodeURIComponent(jobUrl),
                                            company: jobCompany,
                                            location: jobLocation,
                                            source: jobSource,
                                            match_score: parseInt(matchScore) || 0,
                                        }),
                                    }).catch(err => console.error('Error tracking job view:', err));
                                    
                                    // Reload recently viewed after a short delay
                                    setTimeout(loadRecentlyViewed, 1000);
                                }
                                
                                // Load recently viewed jobs
                                async function loadRecentlyViewed() {
                                    const container = document.getElementById('recently-viewed-container');
                                    
                                    try {
                                        const response = await fetch('/api/job-views/recently-viewed');
                                        const data = await response.json();
                                        
                                        if (data.success && data.jobs && data.jobs.length > 0) {
                                            const clearBtn = document.getElementById('clear-history-btn');
                                            if (clearBtn) clearBtn.classList.remove('hidden');
                                            
                                            container.innerHTML = data.jobs.slice(0, 5).map(job => `
                                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 bg-white text-gray-900 rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-[#1193d4]/20 transition-all duration-200">
                                                    <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                                        <div class="w-12 h-12 flex-shrink-0 bg-gradient-to-br from-[#1193d4]/20 to-[#1193d4]/5 rounded-lg flex items-center justify-center">
                                                            <span class="material-symbols-outlined text-[#1193d4] text-2xl">work</span>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-gray-900 text-sm sm:text-base font-medium truncate">${job.job_title || 'Job Title'}</p>
                                                            <p class="text-gray-600 text-xs sm:text-sm truncate">${job.company || 'Company'}${job.location ? ' - ' + job.location : ''}</p>
                                                            <p class="text-gray-400 text-xs mt-1">Viewed ${new Date(job.viewed_at).toLocaleDateString()}</p>
                                                        </div>
                                                    </div>
                                                    <a href="${job.job_url}" target="_blank" class="w-full sm:w-auto rounded-lg h-9 px-4 bg-[#1193d4] text-white text-sm font-semibold hover:bg-[#0f83bd] transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1">
                                                        View
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            `).join('');
                                        } else {
                                            const clearBtn = document.getElementById('clear-history-btn');
                                            if (clearBtn) clearBtn.classList.add('hidden');
                                            
                                            container.innerHTML = `
                                                <div class="text-center py-8">
                                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <p class="text-gray-500 text-sm">No recently viewed jobs</p>
                                                    <p class="text-gray-400 text-xs mt-1">Jobs you view will appear here</p>
                                                </div>
                                            `;
                                        }
                                    } catch (error) {
                                        console.error('Error loading recently viewed:', error);
                                        container.innerHTML = `
                                            <div class="text-center py-8">
                                                <p class="text-gray-500 text-sm">Unable to load recently viewed jobs</p>
                                            </div>
                                        `;
                                    }
                                }
                                
                                // Clear view history
                                async function clearViewHistory() {
                                    if (!confirm('Are you sure you want to clear your view history?')) return;
                                    
                                    try {
                                        const response = await fetch('/api/job-views/clear', {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': getCsrfToken(),
                                                'Accept': 'application/json',
                                            },
                                        });
                                        
                                        if (response.ok) {
                                            loadRecentlyViewed();
                                            showNotification('View history cleared successfully', 'success');
                                        }
                                    } catch (error) {
                                        console.error('Error clearing view history:', error);
                                        showNotification('Failed to clear view history', 'error');
                                    }
                                }
                                
                                // Notification system
                                function showNotification(message, type = 'info') {
                                    const notification = document.createElement('div');
                                    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
                                        type === 'success' ? 'bg-green-500 text-white' :
                                        type === 'error' ? 'bg-red-500 text-white' :
                                        'bg-[#1193d4] text-white'
                                    }`;
                                    notification.innerHTML = `
                                        <div class="flex items-center gap-2">
                                            ${type === 'success' ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' : ''}
                                            <span>${message}</span>
                                            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:opacity-75">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            </button>
                                        </div>
                                    `;
                                    document.body.appendChild(notification);
                                    
                                    setTimeout(() => {
                                        notification.style.opacity = '0';
                                        notification.style.transform = 'translateX(100%)';
                                        setTimeout(() => notification.remove(), 300);
                                    }, 3000);
                                }
                                
                                // Improved empty states
                                function showEmptyState(container, type = 'no-jobs') {
                                    const messages = {
                                        'no-jobs': {
                                            icon: 'work_off',
                                            title: 'No job matches found',
                                            message: 'Make sure you\'ve uploaded your resume for personalized job recommendations.',
                                            action: { text: 'Upload Resume', href: '{{ route("resume.upload") }}' }
                                        },
                                        'no-resume': {
                                            icon: 'description',
                                            title: 'Upload your resume',
                                            message: 'Upload your resume to get AI-powered job recommendations tailored to your skills.',
                                            action: { text: 'Upload Resume', href: '{{ route("resume.upload") }}' }
                                        },
                                        'error': {
                                            icon: 'error_outline',
                                            title: 'Unable to load jobs',
                                            message: 'There was an error loading job matches. Please try again later.',
                                            action: { text: 'Try Again', onclick: 'loadJobs(true)' }
                                        }
                                    };
                                    
                                    const config = messages[type] || messages['no-jobs'];
                                    
                                    container.innerHTML = `
                                        <div class="col-span-full text-center py-12">
                                            <span class="material-symbols-outlined text-gray-300 text-6xl mb-4 block">${config.icon}</span>
                                            <h3 class="text-gray-900 text-lg font-semibold mb-2">${config.title}</h3>
                                            <p class="text-gray-600 text-sm mb-6 max-w-md mx-auto">${config.message}</p>
                                            ${config.action ? `
                                                ${config.action.href ? `
                                                    <a href="${config.action.href}" class="inline-block px-6 py-3 bg-[#1193d4] text-white rounded-lg hover:bg-[#0f83bd] transition-colors text-sm font-semibold shadow-sm hover:shadow-md">
                                                        ${config.action.text}
                                                    </a>
                                                ` : config.action.onclick ? `
                                                    <button onclick="${config.action.onclick}" class="px-6 py-3 bg-[#1193d4] text-white rounded-lg hover:bg-[#0f83bd] transition-colors text-sm font-semibold shadow-sm hover:shadow-md">
                                                        ${config.action.text}
                                                    </button>
                                                ` : ''}
                                            ` : ''}
                                        </div>
                                    `;
                                }
                                
                                // Add event listeners for search and filter
                                // Initialize on page load
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Start auto-refresh for real-time job updates
                                    startAutoRefresh();
                                    
                                    // Load jobs on page load
                                    const searchInput = document.getElementById('job-search-input');
                                    const locationFilter = document.getElementById('filter-location');
                                    const matchScoreFilter = document.getElementById('filter-match-score');
                                    
                                    if (searchInput) {
                                        searchInput.addEventListener('input', filterJobs);
                                        searchInput.addEventListener('keyup', (e) => {
                                            if (e.key === 'Enter') filterJobs();
                                        });
                                    }
                                    
                                    if (locationFilter) {
                                        locationFilter.addEventListener('change', filterJobs);
                                    }
                                    
                                    if (matchScoreFilter) {
                                        matchScoreFilter.addEventListener('change', filterJobs);
                                    }
                                    
                                    // Load recently viewed jobs
                                    loadRecentlyViewed();
                                    
                                    // Load jobs - force refresh on initial load to get fresh jobs from sources
                                    loadJobs(true, 1);
                                    
                                    // Start auto-refresh (5-10 minutes)
                                    startAutoRefresh();
                                });
                                
                                // Clear interval when page is hidden
                                document.addEventListener('visibilitychange', function() {
                                    if (document.hidden) {
                                        stopAutoRefresh(); // Stop auto-refresh when page is hidden
                                    } else {
                                        // Reload when page becomes visible again
                                        loadJobs(true, currentPage); // Force refresh when page becomes visible
                                        loadRecentlyViewed();
                                        startAutoRefresh(); // Restart auto-refresh
                                    }
                                });
                            </script>
                        </div>
                        <!-- Recently Viewed -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <h2 class="text-gray-900 text-xl sm:text-2xl font-bold">Recently Viewed Jobs</h2>
                                <button id="clear-history-btn" onclick="clearViewHistory()" class="text-xs text-gray-500 hover:text-[#1193d4] transition-colors hidden">Clear</button>
                            </div>
                            <div id="recently-viewed-container" class="flex flex-col gap-3">
                                <div class="flex items-center justify-center py-8">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#1193d4]"></div>
                                    <span class="ml-3 text-gray-600 text-sm">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Side Cards -->
                    <div class="lg:col-span-1 flex flex-col gap-6 lg:sticky lg:top-20 lg:self-start">
                        <!-- AI Analysis Summary Card -->
                        @if(auth()->user()->ai_analysis && !empty(auth()->user()->ai_analysis))
                            <div class="flex flex-col gap-5 bg-white text-gray-900 rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-5 h-5 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    <h3 class="text-gray-900 text-base sm:text-lg font-bold">AI Analysis Summary</h3>
                                </div>
                                
                                <!-- Resume Score -->
                                @if(auth()->user()->resume_score)
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-semibold text-gray-700">Resume Score</span>
                                            <span class="text-lg font-bold text-[#1193d4]">{{ auth()->user()->resume_score }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r from-[#1193d4] to-blue-500 h-2.5 rounded-full shadow-sm transition-all duration-500" 
                                                 style="width: {{ min(auth()->user()->resume_score, 100) }}%"></div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Recommended Field -->
                                @if(auth()->user()->recommended_field)
                                    <div class="mb-4">
                                        <span class="text-sm font-semibold text-gray-700">Recommended Field: </span>
                                        <span class="text-base font-bold text-[#1193d4]">{{ auth()->user()->recommended_field }}</span>
                                    </div>
                                @endif
                                
                                <!-- Top Skills -->
                                @if(isset(auth()->user()->ai_analysis['skills']) && is_array(auth()->user()->ai_analysis['skills']) && count(auth()->user()->ai_analysis['skills']) > 0)
                                    <div class="mb-4">
                                        <span class="text-sm font-semibold text-gray-700 block mb-2">Top Skills:</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(array_slice(auth()->user()->ai_analysis['skills'], 0, 5) as $skill)
                                                <span class="px-2.5 py-1 bg-[#1193d4]/10 text-[#1193d4] text-xs font-medium rounded-full">
                                                    {{ ucwords($skill) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Quick Insight -->
                                @if(auth()->user()->resume_score && auth()->user()->resume_score >= 70)
                                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <p class="text-xs text-green-800">
                                            <span class="font-semibold">Great!</span> Your resume is well-optimized. You're likely to get strong job matches.
                                        </p>
                                    </div>
                                @elseif(auth()->user()->resume_score && auth()->user()->resume_score >= 50)
                                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="text-xs text-yellow-800">
                                            <span class="font-semibold">Good start!</span> Consider adding more skills and experience to improve your matches.
                                        </p>
                                    </div>
                                @endif
                                
                                <a href="{{ route('resume.upload') }}" 
                                   class="mt-2 rounded-lg h-10 px-4 bg-gray-100 text-gray-800 text-sm font-semibold w-full flex items-center justify-center gap-2 hover:bg-gray-200 transition-all duration-200 border border-gray-300">
                                    View Full Analysis
                                </a>
                            </div>
                        @endif
                        
                        <!-- Profile Strength Card -->
                        <div class="flex flex-col gap-5 bg-white text-gray-900 rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="text-gray-900 text-base sm:text-lg font-bold">Profile Strength</h3>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl font-bold text-[#1193d4]">{{ $profileStrength['level'] }}</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                @if($profileStrength['percentage'] < 100)
                                    Complete your profile to get more accurate job recommendations.
                                @else
                                    Your profile is complete! You're getting the best job matches.
                                @endif
                            </p>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-1">
                                <div class="bg-gradient-to-r from-[#1193d4] to-blue-500 h-3 rounded-full shadow-sm transition-all duration-500" 
                                     style="width: {{ $profileStrength['percentage'] }}%"></div>
                            </div>
                            <p class="text-gray-500 text-xs text-right">{{ $profileStrength['percentage'] }}% Complete</p>
                            
                            @if(count($profileStrength['missing_fields']) > 0)
                                <div class="mt-2 mb-2">
                                    <p class="text-xs text-gray-600 mb-2 font-medium">Missing:</p>
                                    <ul class="text-xs text-gray-500 space-y-1">
                                        @foreach(array_slice($profileStrength['missing_fields'], 0, 3) as $field)
                                            <li class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                {{ $field }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <a href="{{ route('profile.edit') }}"
                            class="mt-2 rounded-lg h-10 px-4 bg-[#1193d4] text-white text-sm font-semibold w-full flex items-center justify-center gap-2 hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md">
                                @if($profileStrength['percentage'] < 100)
                                    Complete Your Profile
                                @else
                                    Update Profile
                                @endif
                            </a>
                        </div>
                        <!-- My Bookmarks Card -->
                        <div class="flex flex-col gap-5 bg-white text-gray-900 rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <h3 class="text-gray-900 text-base sm:text-lg font-bold">My Bookmarks</h3>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl font-bold text-[#1193d4]">{{ $bookmarkCount }}</span>
                                <span class="text-gray-600 text-sm">{{ $bookmarkCount === 1 ? 'bookmark' : 'bookmarks' }}</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                @if($bookmarkCount > 0)
                                    You have {{ $bookmarkCount }} saved {{ $bookmarkCount === 1 ? 'job' : 'jobs' }} to review.
                                @else
                                    Start bookmarking jobs you're interested in.
                                @endif
                            </p>
                            <a href="{{ route('bookmarks') }}"
                            class="mt-2 rounded-lg h-10 px-4 bg-[#1193d4] text-white text-sm font-semibold w-full flex items-center justify-center gap-2 hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                View Bookmarks
                            </a>
                        </div>
                        
                        <!-- My Resume Card -->
                        <div class="flex flex-col gap-5 bg-white text-gray-900 rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-gray-900 text-base sm:text-lg font-bold">My Resume</h3>
                            </div>
                            @if(auth()->user()->resume_path)
                                <div class="flex items-center gap-3 rounded-lg bg-gray-50 border border-gray-200 p-4">
                                    <div class="flex-shrink-0 w-12 h-12 bg-[#1193d4]/10 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#1193d4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-gray-900 text-sm font-semibold truncate">
                                            {{ basename(auth()->user()->resume_path) }}
                                        </p>
                                        <p class="text-gray-500 text-xs mt-1">
                                            Uploaded {{ auth()->user()->updated_at ? auth()->user()->updated_at->format('d M Y') : 'recently' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ asset('storage/' . auth()->user()->resume_path) }}" 
                                       target="_blank"
                                       class="flex-1 rounded-lg h-10 px-4 bg-[#1193d4] text-white text-sm font-semibold flex items-center justify-center gap-2 hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Download
                                    </a>
                                    <a href="{{ route('resume.upload') }}" 
                                       class="flex-1 rounded-lg h-10 px-4 bg-gray-100 text-gray-800 text-sm font-semibold flex items-center justify-center gap-2 hover:bg-gray-200 transition-all duration-200 border border-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Replace
                                    </a>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center gap-3 rounded-lg bg-gray-50 p-6 border-2 border-dashed border-gray-300">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div class="text-center">
                                        <p class="text-gray-600 text-sm font-medium">No resume uploaded</p>
                                        <p class="text-gray-500 text-xs mt-1">Upload your resume to get better job matches</p>
                                    </div>
                                </div>
                                <a href="{{ route('resume.upload') }}" 
                                   class="rounded-lg h-10 px-4 bg-[#1193d4] text-white text-sm font-semibold w-full gap-2 flex items-center justify-center hover:bg-[#0f83bd] transition-all duration-200 shadow-sm hover:shadow-md">
                                    Upload Resume
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </div>
</div>
@endsection
