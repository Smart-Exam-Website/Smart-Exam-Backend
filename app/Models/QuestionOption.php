<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'question_option';

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
}
