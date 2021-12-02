<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    public function answer()
    {
        return $this->hasOne(Answer::class);
    }
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
