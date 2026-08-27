<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    public const ESTADO_PENDIENTE = 1;
    public const ESTADO_APROBADA = 2;
    public const ESTADO_RECHAZADA = 3;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'motivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'estado' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
