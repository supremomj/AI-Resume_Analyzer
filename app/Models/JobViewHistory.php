<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobViewHistory extends Model
{
    protected $table = 'job_view_history';

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
        'view_count',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the view history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
