@extends('layouts.sistema')

@section('titulo', $alternativa ? 'Editar alternativa ' . $alternativa->codigo : 'Nueva alternativa')

@section('contenido')
    @php
        $url = fn (string $nombre, $parametros = []) => Route::has($nombre) ? route($nombre, $parametros) : '#';
        $editando = (bool) $alternativa;
    @endphp

    <form method="POST"
          action="{{ $editando ? $url('alternativas.update', $alternativa->id) : $url('alternativas.store') }}"
          class="max-w-3xl">
        @csrf
        @if ($editando)
            @method('PUT')
        @endif

        <x-tarjeta titulo="Datos de la alternativa" subtitulo="El costo inicial se registra después, en las cotizaciones.">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-campo nombre="codigo" etiqueta="Código" :valor="$alternativa->codigo ?? null" ayuda="Ejemplo: A o B" />
                <x-campo nombre="nombre" etiqueta="Nombre" :valor="$alternativa->nombre ?? null" />
                <x-campo nombre="descripcion" etiqueta="Descripción" tipo="textarea" :valor="$alternativa->descripcion ?? null" class="sm:col-span-2" />
            </div>
        </x-tarjeta>

        <x-tarjeta titulo="Supuestos para la evaluación" subtitulo="Cada dato debe tener su fuente o marcarse como estimación." class="mt-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-campo nombre="vida_util" etiqueta="Vida útil (años)" tipo="number" paso="1"
                         :valor="$alternativa->vida_util ?? 5" ayuda="Bloque A: 5 años (supuesto académico)" />
                <x-campo nombre="porcentaje_salvamento" etiqueta="Valor de salvamento (% de la inversión)" tipo="number" paso="0.01"
                         :valor="$alternativa->porcentaje_salvamento ?? 10" ayuda="Bloque A: 10 % (supuesto académico)" />
                <x-campo nombre="costos_anuales" etiqueta="Costo fijo anual ($)" tipo="number" paso="0.01"
                         :valor="$alternativa->costos_anuales ?? null" ayuda="Bloque A: $30 para A y $90 para B (estimación)" />
                <x-campo nombre="ingresos_anuales" etiqueta="Ahorro o ingreso anual ($)" tipo="number" paso="0.01"
                         :valor="$alternativa->ingresos_anuales ?? null" ayuda="Bloque A: $1.00 × 330 camisas = $330 (supuesto)" />
            </div>
        </x-tarjeta>

        <div class="mt-6 flex gap-3">
            <x-boton>Guardar</x-boton>
            <x-boton :href="$url('alternativas.index')" variante="secundario">Cancelar</x-boton>
        </div>
    </form>
@endsection