<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function option()
    {
        return $this->hasOne(Option::class);
    }
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, $table = "question_tag", "question_id")->withTimestamps();
    }
    public function Mcq()
    {
        return $this->hasOne(Mcq::class);
    }
}
