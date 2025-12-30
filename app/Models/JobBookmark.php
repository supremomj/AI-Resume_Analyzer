<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_title',
        'job_url',
        'job_url_hash',
        'company',
        'location',
        'source',
        'match_score',
        'description',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically generate hash when creating/updating
        static::saving(function ($bookmark) {
            if ($bookmark->job_url && !$bookmark->job_url_hash) {
                $bookmark->job_url_hash = hash('sha256', $bookmark->job_url);
            }
        });
    }

    /**
     * Get the user that owns the bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

