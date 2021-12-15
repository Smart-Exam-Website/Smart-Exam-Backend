<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mcq extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
    public function McqAnswers(): HasMany
    {
        return $this->hasMany(McqAnswer::class);
    }
}
