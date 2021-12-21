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

    public function option()
    {
        return $this->hasMany(Option::class);
    }
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, $table = "question_tag", "question_id")->withTimestamps();
    }
    public function Mcq()
    {
        return $this->hasOne(Mcq::class, 'id');
    }

    public function exams() {
        return $this->belongsToMany(Question::class, 'exam_question', 'question_id')->withTimestamps()->withPivot(['time', 'mark']);;
    }
}
