<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Job API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API keys and settings for various job posting sources.
    | Leave API keys empty to use web scraping as fallback.
    |
    */

    'indeed' => [
        'enabled' => env('INDEED_API_ENABLED', false),
        'publisher_id' => env('INDEED_PUBLISHER_ID', ''),
        'api_key' => env('INDEED_API_KEY', ''),
        'use_rss' => env('INDEED_USE_RSS', true), // Fallback to RSS if API not available
    ],

    'adzuna' => [
        'enabled' => env('ADZUNA_API_ENABLED', false),
        'app_id' => env('ADZUNA_APP_ID', ''),
        'app_key' => env('ADZUNA_APP_KEY', ''),
        'country' => 'ph', // Philippines
    ],

    'jooble' => [
        'enabled' => env('JOOBLE_API_ENABLED', false),
        'api_key' => env('JOOBLE_API_KEY', ''),
        'country' => 'ph', // Philippines
    ],

    'jobdata' => [
        'enabled' => env('JOBDATA_API_ENABLED', false),
        'api_key' => env('JOBDATA_API_KEY', ''),
    ],

    'scraperapi' => [
        'enabled' => env('SCRAPERAPI_ENABLED', false),
        'api_key' => env('SCRAPERAPI_KEY', ''),
        'use_for' => ['linkedin', 'indeed', 'glassdoor'], // Sites to use ScraperAPI for
    ],

    'linkedin' => [
        'enabled' => env('LINKEDIN_API_ENABLED', false),
        'client_id' => env('LINKEDIN_CLIENT_ID', ''),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET', ''),
        'access_token' => env('LINKEDIN_ACCESS_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for each API to avoid exceeding quotas.
    |
    */
    'rate_limits' => [
        'adzuna' => [
            'requests_per_minute' => 50,
            'requests_per_day' => 1000,
        ],
        'jooble' => [
            'requests_per_minute' => 30,
            'requests_per_day' => 500,
        ],
        'jobdata' => [
            'requests_per_minute' => 20,
            'requests_per_day' => 1000,
        ],
        'scraperapi' => [
            'requests_per_minute' => 10,
            'requests_per_day' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategy
    |--------------------------------------------------------------------------
    |
    | If API fails, fallback to web scraping
    |
    */
    'fallback_to_scraping' => env('JOB_API_FALLBACK_TO_SCRAPING', true),
];

