<?php

namespace Database\Seeders;

use App\Models\Alternativa;
use App\Models\Cotizacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BloqueASeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ALTERNATIVA A
            $alternativaA = Alternativa::updateOrCreate(
                ['codigo' => 'A'],
                [
                    'nombre' => 'Prensa térmica para camisas con DTF',
                    'descripcion' => 'Prensa plana para aplicar transferencias DTF compradas a terceros a las 110 camisas de cada jornada.',
                    'vida_util' => 5,
                    'porcentaje_salvamento' => 10,
                    'costos_anuales' => 30,
                    'ingresos_anuales' => 330,
                ]
            );

            // COTIZACIÓN A1
            Cotizacion::updateOrCreate(
                ['codigo' => 'A1'],
                [
                    'alternativa_id' => $alternativaA->id,
                    'proveedor' => 'Soluciones Centroamérica, San Miguel',
                    'descripcion' => 'Prensa térmica plana Colormaker de 32 × 45 cm',
                    'monto' => 450.00,
                    'tipo_fuente' => 'captura',
                    'fecha' => '2026-11-23',
                    'condicion' => 'Precio en conversación de WhatsApp facilitada por la ASEIS; confirmar garantía y vigencia',
                ]
            );

            // COTIZACIÓN A2
            Cotizacion::updateOrCreate(
                ['codigo' => 'A2'],
                [
                    'alternativa_id' => $alternativaA->id,
                    'proveedor' => 'ACOSA, San Miguel',
                    'descripcion' => 'Prensa plana SB05 de 38 × 38 cm',
                    'monto' => 395.00,
                    'tipo_fuente' => 'catalogo',
                    'fecha' => null,
                    'condicion' => 'Precio de catálogo anterior; verificar existencia y precio actual',
                ]
            );

            // ALTERNATIVA B
            $alternativaB = Alternativa::updateOrCreate(
                ['codigo' => 'B'],
                [
                    'nombre' => 'Equipo para sublimación multiproducto',
                    'descripcion' => 'Prensa térmica y equipo de sublimación para tazas, termos, lapiceros, gorras y camisas.',
                    'vida_util' => 5,
                    'porcentaje_salvamento' => 10,
                    'costos_anuales' => 90,
                    'ingresos_anuales' => 330,
                ]
            );

            // COTIZACIÓN B1
            Cotizacion::updateOrCreate(
                ['codigo' => 'B1'],
                [
                    'alternativa_id' => $alternativaB->id,
                    'proveedor' => 'Soluciones Centroamérica, San Miguel',
                    'descripcion' => 'Combo sublimación 10 en 1 con Epson F170, papel A4 y cinta térmica',
                    'monto' => 1119.00,
                    'tipo_fuente' => 'captura',
                    'fecha' => null,
                    'condicion' => 'Promoción anunciada para octubre de 2026; requiere reconfirmación',
                ]
            );

            // COTIZACIÓN B2
            Cotizacion::updateOrCreate(
                ['codigo' => 'B2'],
                [
                    'alternativa_id' => $alternativaB->id,
                    'proveedor' => 'Varios proveedores',
                    'descripcion' => 'Prensa 8 en 1 (ACOSA $454.99), Epson F170 (Valdés $367.25), prensa de lapiceros (TodoTransfer $299.99) y horno (Sublimaster $349.99)',
                    'monto' => 1472.22,
                    'tipo_fuente' => 'catalogo',
                    'fecha' => null,
                    'condicion' => 'Precios publicados; no incluyen fletes ni insumos',
                ]
            );

        });
    }
}