<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Instructor extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the user associated with the Instructor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)->withTimestamps();
    }
}
