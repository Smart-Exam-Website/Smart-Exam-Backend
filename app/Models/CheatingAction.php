<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheatingAction extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'cheating_actions';
}
