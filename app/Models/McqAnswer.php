<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McqAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'mcq_answers';

    public function option()
    {
        return $this->belongsTo(Option::class, 'id');
    }

    public function Mcq()
    {
        return $this->belongsTo(Mcq::class);
    }
}
