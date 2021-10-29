<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, $table = "departments_instructors", "department_id")->withTimestamps();
    }
    public function students(): HasMany
    {
        return $this->HasMany(Student::class)->withTimestamps();
    }
}
