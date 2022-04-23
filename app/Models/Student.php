<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Department;

class Student extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function answers()
    {
        return $this->belongsToMany(Answer::class);
    }

    public function sessions()
    {
        return $this->belongsToMany(examSession::class, 'examSession', 'student_id')->withTimestamps()->withPivot(['numberOfFaces', 'isVerified', 'startTime']);
    }

    public function formulaQuestions() {
        return $this->belongsToMany(FormulaQuestion::class, 'formula_student', 'student_id')->withTimestamps();
    }
}
