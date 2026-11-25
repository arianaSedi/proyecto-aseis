<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'alternativa_id',
        'codigo',
        'proveedor',
        'descripcion',
        'monto',
        'tipo_fuente',
        'fecha',
        'condicion',
        'archivo',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    protected $appends = [
        'archivo_url',
    ];

    public function alternativa(): BelongsTo
    {
        return $this->belongsTo(Alternativa::class);
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo
            ? Storage::disk('public')->url($this->archivo)
            : null;
    }
}