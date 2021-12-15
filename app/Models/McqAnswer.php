<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McqAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function answer()
    {
        return $this->belongsTo(Answer::class, 'answer_id');
    }

    public function Mcq()
    {
        return $this->belongsTo(Mcq::class, 'question_id');
    }
}
