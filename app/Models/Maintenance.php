<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $guarded = [];

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::created(function (Maintenance $maintenance) {
            // Al registrar la liquidación, actualiza la cita asociada a 'finalizada'
            if ($appointment = $maintenance->diagnosis?->appointment) {
                $appointment->update([
                    'status' => 'finalizada',
                ]);
            }
        });
    }
}
