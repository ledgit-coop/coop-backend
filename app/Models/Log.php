<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'content',
        'model',
        'model_id',

        'parent_model',
        'parent_model_id',

        'created_by',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
