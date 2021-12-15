<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, $table = "question_tag", "tag_id")->withTimestamps();
    }
}
