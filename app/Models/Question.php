<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function options()
    {
        return $this->hasMany(Option::class);
    }
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, $table = "question_tag", "question_id")->withTimestamps();
    }
    public function exams()
    {
        return $this->belongsToMany(Question::class, 'exam_question', 'question_id')->withTimestamps()->withPivot(['time', 'mark']);;
    }
    public function answers(): BelongsToMany
    {
        return $this->belongsToMany(Answer::class);
    }
    public function answer()
    {
        return $this->hasMany(Answer::class);
    }
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
