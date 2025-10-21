<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'exact_score',
        'correct_difference',
        'correct_winner',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
