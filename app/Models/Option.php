<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Option extends Model
{
    use HasFactory;
    protected $guarded = [];

    

    public function McqAnswer()
    {
        return $this->hasOne(McqAnswer::class);
    }

    public function question() {
        return $this->belongsTo(Question::class);
    }
    public function answers(): BelongsToMany {
        return $this->belongsToMany(Answer::class);
    }
}
