<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormulaQuestion extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'formula_questions';
    public $timestamps = true;

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function students() {
        return $this->belongsToMany(Student::class, 'formula_student', 'formula_id');
    }
}
