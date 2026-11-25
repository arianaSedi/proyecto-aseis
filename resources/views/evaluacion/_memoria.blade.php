@php
    $pa = fn (float $i, int $n) => $i == 0 ? $n : ((1 + $i) ** $n - 1) / ($i * (1 + $i) ** $n);
    $pf = fn (float $i, int $n) => 1 / (1 + $i) ** $n;
    $af = fn (float $i, int $n) => $i == 0 ? 1 / $n : $i / ((1 + $i) ** $n - 1);
    $n4 = fn ($v) => number_format($v, 4);
    $n2 = fn ($v) => number_format($v, 2);
        $d = fn ($v) => (round($v, 2) < 0 ? '−$' : '$') . number_format(abs($v), 2);
@endphp

<x-tarjeta titulo="Memoria de cálculo" subtitulo="Cálculo paso a paso con factores de tabla para la verificación cruzada contra el cálculo manual." class="mt-6">
    <div class="space-y-3">
        @foreach ($resultados as $r)
            @php
                $i = $tmar;
                $n = (int) $r->vida_util;
                $P = (float) $r->inversion;
                $S = (float) $r->salvamento;
                $I = (float) $r->ingresos_anuales;
                $C = (float) $r->costos_anuales;

                $fPA = $pa($i, $n);
                $fPF = $pf($i, $n);
                $fAP = 1 / $fPA;
                $fAF = $af($i, $n);

                // Recalculo con factores para comparar con el resultado del sistema
                $vpnManual = -$P + ($I - $C) * $fPA + $S * $fPF;
                $caueManual = $P * $fAP - $S * $fAF + $C - $I;
                $vpnEnTir = $r->tir === null ? null : -$P + ($I - $C) * $pa($r->tir, $n) + $S * $pf($r->tir, $n);

                $okVpn = abs($vpnManual - $r->vpn) < 0.01;
                $okCaue = abs($caueManual - $r->caue) < 0.01;
            @endphp

            <details class="rounded-xl border border-fondo-200">
                <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-2 px-4 py-3">
                    <span class="font-semibold">{{ $r->codigo }} · {{ $r->nombre }}</span>
                    <x-insignia :color="$okVpn && $okCaue ? 'verde' : 'ambar'">
                        {{ $okVpn && $okCaue ? 'Verificado ✓' : 'Revisar' }}
                    </x-insignia>
                </summary>

                <div class="grid gap-4 border-t border-fondo-200 p-4 text-sm lg:grid-cols-2">
                    {{-- Datos y factores --}}
                    <div class="rounded-xl bg-fondo-100 p-4 lg:col-span-2">
                        <p>P = ${{ $n2($P) }} · S = ${{ $n2($S) }} · I = ${{ $n2($I) }} · C = ${{ $n2($C) }} · n = {{ $n }} · i = {{ $n2($i * 100) }} %</p>
                        <p class="mt-1 text-slate-600">
                            (P/A, i, n) = {{ $n4($fPA) }} · (P/F, i, n) = {{ $n4($fPF) }} ·
                            (A/P, i, n) = {{ $n4($fAP) }} · (A/F, i, n) = {{ $n4($fAF) }}
                        </p>
                    </div>

                    {{-- VPN --}}
                    <div>
                        <p class="font-semibold">VPN</p>
                        <p class="text-slate-600">VPN = −P + (I − C)(P/A, i, n) + S(P/F, i, n)</p>
                        <p>= −{{ $n2($P) }} + ({{ $n2($I) }} − {{ $n2($C) }})({{ $n4($fPA) }}) + {{ $n2($S) }}({{ $n4($fPF) }})</p>
                        <p>= <strong>{{ $d($vpnManual) }}</strong></p>
                        <p class="text-xs {{ $okVpn ? 'text-emerald-600' : 'text-amber-700' }}">
                            Sistema: {{ $d($r->vpn) }} {{ $okVpn ? '✓ coincide' : '≠ revisar' }}
                        </p>
                    </div>

                    {{-- CAUE --}}
                    <div>
                        <p class="font-semibold">CAUE</p>
                        <p class="text-slate-600">CAUE = P(A/P, i, n) − S(A/F, i, n) + C − I</p>
                        <p>= {{ $n2($P) }}({{ $n4($fAP) }}) − {{ $n2($S) }}({{ $n4($fAF) }}) + {{ $n2($C) }} − {{ $n2($I) }}</p>
                        <p>= <strong>{{ $d($caueManual) }}</strong></p>
                        <p class="text-xs {{ $okCaue ? 'text-emerald-600' : 'text-amber-700' }}">
                            Sistema: {{ $d($r->caue) }} {{ $okCaue ? '✓ coincide' : '≠ revisar' }}
                        </p>
                    </div>

                    {{-- TIR --}}
                    <div>
                        <p class="font-semibold">TIR</p>
                        <p class="text-slate-600">0 = −P + (I − C)(P/A, TIR, n) + S(P/F, TIR, n)</p>
                        @if ($r->tir === null)
                            <p>No existe TIR: el flujo no cambia de signo.</p>
                        @else
                            <p>TIR = <strong>{{ $n2($r->tir * 100) }} %</strong></p>
                            <p class="text-xs text-slate-500">Comprobación: VPN calculado a la TIR = {{ $d($vpnEnTir) }} (≈ 0)</p>
                        @endif
                    </div>

                    {{-- B/C y recuperacion --}}
                    <div>
                        <p class="font-semibold">Relación B/C y recuperación simple</p>
                        <p class="text-slate-600">B/C = VP(beneficios) / VP(costos)</p>
                        <p>B/C del sistema = <strong>{{ $r->bc === null ? '—' : number_format($r->bc, 4) }}</strong></p>
                        <p class="mt-2 text-slate-600">Recuperación = P / (I − C)</p>
                        <p>
                            = {{ $n2($P) }} / {{ $n2($I - $C) }} =
                            <strong>{{ $r->recuperacion === null ? '—' : $n2($r->recuperacion) . ' años' }}</strong>
                        </p>
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</x-tarjeta>