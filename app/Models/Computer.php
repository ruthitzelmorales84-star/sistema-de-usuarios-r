<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Computer extends Model
{
    protected $fillable = [
        'name',
        'location',
        'description',
        'status',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(ComputerReservation::class);
    }

    public function activeReservation(): ?ComputerReservation
    {
        return $this->reservations()
            ->whereNull('cancelled_at')
            ->latest('reserved_at')
            ->first();
    }
}
