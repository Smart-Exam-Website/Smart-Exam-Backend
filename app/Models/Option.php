<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function questions(): BelongsToMany
    {
        return $this->HasMany(Question::class)->withTimestamps();
    }

    public function McqAnswer()
    {
        return $this->hasOne(McqAnswer::class);
    }
}
