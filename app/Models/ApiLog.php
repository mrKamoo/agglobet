<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = ['type', 'status', 'message', 'details'];

    protected $casts = [
        'details' => 'array',
    ];
}
