<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class examSession extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'examsession';
    public $timestamps = true;

    public function exams() {
        return $this->belongsToMany(Exam::class, 'examsession','exam_id')->withTimestamps()->withPivot(['numberOfFaces', 'isVerified', 'startTime']);
    }

    public function students() {
        return $this->belongsToMany(Exam::class, 'examsession','student_id')->withTimestamps()->withPivot(['numberOfFaces', 'isVerified', 'startTime']);
    }
}
