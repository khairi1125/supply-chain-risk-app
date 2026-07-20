<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
        'source',
        'content',
        'category',
        'status',
        'sentiment',
        'sentiment_score',
        'sentiment_confidence',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the article
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only published articles
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope to get only draft articles
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get excerpt from content (first 150 characters)
     */
    public function getExcerptAttribute()
    {
        return substr(strip_tags($this->content), 0, 150) . '...';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status === 'published' ? 'success' : 'secondary';
    }

    /**
     * Get category badge color
     */
    public function getCategoryBadgeAttribute()
    {
        $colors = [
            'logistics' => 'primary',
            'economy' => 'info',
            'geopolitics' => 'warning',
            'weather' => 'danger',
        ];

        return $colors[$this->category] ?? 'secondary';
    }
}
