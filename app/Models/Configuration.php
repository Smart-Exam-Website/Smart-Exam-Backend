<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuration extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'configs';
    public $primaryKey = 'exam_id';


    public function exam(): BelongsTo {
        return $this->belongsTo(Exam::class);
    }
}
