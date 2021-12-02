<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionsRole extends Model
{
    use HasFactory;
    protected $table = 'permissions_roles';
    protected $guarded = [];
}
