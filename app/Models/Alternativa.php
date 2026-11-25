<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alternativa extends Model
{
    protected $table = 'alternativas';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'vida_util',
        'porcentaje_salvamento',
        'costos_anuales',
        'ingresos_anuales',
    ];

    protected $casts = [
        'vida_util' => 'integer',
        'porcentaje_salvamento' => 'decimal:4',
        'costos_anuales' => 'decimal:2',
        'ingresos_anuales' => 'decimal:2',
    ];

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }
}