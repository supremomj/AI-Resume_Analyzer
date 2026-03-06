<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'google_id',
        'google_token',
        'password',
        'role',
        'contact_number',
        'address',
        'profile_photo',
        'resume_path',
        'ai_analysis',
        'resume_score',
        'recommended_field',
        'email_verification_otp',
        'email_verification_otp_expires_at',
        // Settings
        'email_notifications',
        'alert_frequency',
        'preferred_job_types',
        'profile_public',
        'show_contact',
        'language',
        'jobs_per_page',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'ai_analysis' => 'array',
            'preferred_job_types' => 'array',
            'email_notifications' => 'boolean',
            'profile_public' => 'boolean',
            'show_contact' => 'boolean',
            'jobs_per_page' => 'integer',
        ];
    }

    /**
     * Get the bookmarks for the user.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(JobBookmark::class);
    }

    /**
     * Get the job view history for the user.
     */
    public function jobViewHistory(): HasMany
    {
        return $this->hasMany(JobViewHistory::class);
    }

    /**
     * Calculate profile strength percentage based on completed fields.
     */
    public function getProfileStrength(): array
    {
        $totalScore = 0;
        $maxScore = 0;
        $missingFields = [];

        // First Name & Last Name (20%)
        $maxScore += 20;
        if (!empty($this->first_name) && !empty($this->last_name)) {
            $totalScore += 20;
        } else {
            $missingFields[] = 'First Name and Last Name';
        }

        // Email Verified (10%)
        $maxScore += 10;
        if ($this->hasVerifiedEmail()) {
            $totalScore += 10;
        } else {
            $missingFields[] = 'Email Verification';
        }

        // Contact Number (10%)
        $maxScore += 10;
        if (!empty($this->contact_number)) {
            $totalScore += 10;
        } else {
            $missingFields[] = 'Contact Number';
        }

        // Address (10%)
        $maxScore += 10;
        if (!empty($this->address)) {
            $totalScore += 10;
        } else {
            $missingFields[] = 'Address';
        }

        // Profile Photo (10%)
        $maxScore += 10;
        if (!empty($this->profile_photo)) {
            $totalScore += 10;
        } else {
            $missingFields[] = 'Profile Photo';
        }

        // Resume Uploaded (20%)
        $maxScore += 20;
        if (!empty($this->resume_path)) {
            $totalScore += 20;
        } else {
            $missingFields[] = 'Resume Upload';
        }

        // AI Analysis Complete (20%)
        $maxScore += 20;
        if (!empty($this->ai_analysis) && is_array($this->ai_analysis) && !empty($this->ai_analysis)) {
            $totalScore += 20;
        } else {
            $missingFields[] = 'Resume AI Analysis';
        }

        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;

        // Determine level
        $level = match (true) {
            $percentage >= 90 => 'Expert',
            $percentage >= 70 => 'Advanced',
            $percentage >= 50 => 'Intermediate',
            $percentage >= 30 => 'Beginner',
            default => 'Getting Started',
        };

        return [
            'percentage' => $percentage,
            'level' => $level,
            'missing_fields' => $missingFields,
        ];
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
