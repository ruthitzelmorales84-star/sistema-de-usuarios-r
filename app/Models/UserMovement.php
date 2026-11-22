<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMovement extends Model
{
    protected $fillable = [
        'action',
        'description',
        'actor_name',
    ];
}