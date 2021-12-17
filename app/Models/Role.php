<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return this->belongstoMany(User::class);
    }
}
