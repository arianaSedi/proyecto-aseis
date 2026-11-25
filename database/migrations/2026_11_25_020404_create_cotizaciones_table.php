<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alternativa_id')
                ->constrained('alternativas')
                ->cascadeOnDelete();

            $table->string('codigo', 20)->unique();
            $table->string('proveedor');
            $table->text('descripcion');
            $table->decimal('monto', 14, 2);
            $table->string('tipo_fuente');
            $table->date('fecha')->nullable();
            $table->text('condicion')->nullable();
            $table->string('archivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};