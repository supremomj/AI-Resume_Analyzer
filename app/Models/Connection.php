<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    protected $fillable = [
        'user_id',
        'connected_user_id',
        'status',
    ];

    /**
     * The user who initiated the connection.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The user who received the connection request.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'connected_user_id');
    }
}
