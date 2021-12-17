<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MCQAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'mcq_answers';

    public function answer(): BelongsTo {
        return $this->belongsTo(Answer::class);
    }

    public function MCQQuestion(): BelongsTo {
        return $this->belongsTo(Question::class);
    }
}
