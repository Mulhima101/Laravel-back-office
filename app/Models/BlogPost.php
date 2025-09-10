<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'wordpress_id',
        'priority'
    ];

    protected $casts = [
        'wordpress_id' => 'integer',
        'priority' => 'integer'
    ];

    public function scopeOrderByPriority($query, $direction = 'desc')
    {
        return $query->orderBy('priority', $direction);
    }

    public static function updatePriority($wordpressId, $priority)
    {
        return static::updateOrCreate(
            ['wordpress_id' => $wordpressId],
            ['priority' => $priority]
        );
    }

    public static function getPriority($wordpressId)
    {
        $post = static::where('wordpress_id', $wordpressId)->first();
        return $post ? $post->priority : 0;
    }
}