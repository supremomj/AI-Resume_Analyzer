<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'provider',
        'url',
        'description',
        'field',
        'is_free',
        'rating',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'rating' => 'decimal:1',
    ];

    /**
     * Scope: filter courses by a recommended field.
     */
    public function scopeForField($query, string $field)
    {
        return $query->where('field', $field);
    }

    /**
     * Scope: search courses whose title or description contains any of the given keywords.
     */
    public function scopeMatchingKeywords($query, array $keywords)
    {
        return $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (strlen($keyword) > 2) {
                    $q->orWhere('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                }
            }
        });
    }
}
