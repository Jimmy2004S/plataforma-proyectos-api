<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'user_id'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}