<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormulaStudent extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'formula_student';
    public $timestamps = true;

}
