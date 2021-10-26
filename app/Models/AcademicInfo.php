<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicInfo extends Model
{
    use HasFactory;
    protected $table = 'academic_info';
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class)->withTimestamps();
    }

}
