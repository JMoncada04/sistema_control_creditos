<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PlanCuota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlanCuotaController extends Controller
{
    public function index(Request $request): Response
    {
        $hasFilter = $request->filled('search') ||
                     ($request->filled('estado') && $request->estado !== 'Todos');

        $cuotas = PlanCuota::with(['venta.cliente'])
            ->when(! $hasFilter, fn($q) => $q->whereRaw('1 = 0'))
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($sub) =>
                    $sub->whereHas('venta.cliente', fn($c) =>
                        $c->where('nombre', 'like', "%$s%")
                          ->orWhere('identidad', 'like', "%$s%")
                    )->orWhere('venta_id', ltrim($s, '#'))
                )
            )
            ->when($request->estado && $request->estado !== 'Todos', fn($q) =>
                $q->where('estado', $request->estado)
            )
            ->orderBy('fecha_vencimiento')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($c) => [
                'id'             => $c->id,
                'venta_id'       => $c->venta_id,
                'cliente'        => $c->venta->cliente->nombre,
                'num'            => $c->numero_cuota,
                'total_cuotas'   => $c->venta->planCuotas()->count(),
                'vencimiento'    => $c->fecha_vencimiento->format('d/m/Y'),
                'monto_cuota'    => $c->monto_cuota,
                'saldo_pendiente'=> $c->saldo_pendiente,
                'recargo_mora'   => $c->recargo_mora,
                'total_a_pagar'  => $c->total_a_pagar,
                'monto_pagado'   => $c->monto_pagado,
                'tipo_pago'      => $c->tipo_pago,
                'estado'         => $c->estado,
                'total_deuda_venta' => round((float) $c->venta->planCuotas()
                    ->whereIn('estado', ['Pendiente', 'Mora'])
                    ->sum('total_a_pagar'), 2),
            ]);

        $this->actualizarMoras();
        Cliente::sincronizarEstados();

        return Inertia::render('Cuotas/Index', [
            'cuotas'  => $cuotas,
            'filters' => $request->only('search', 'estado'),
        ]);
    }

    public function pagar(Request $request, PlanCuota $planCuota): RedirectResponse
    {
        if ($planCuota->estado === 'Pagada') {
            return back()->with('error', 'Esta cuota ya fue pagada.');
        }

        $request->validate([
            'monto_recibido' => 'required|numeric|min:0.01',
        ]);

        $totalACobrar  = round((float) $planCuota->total_a_pagar, 2);

        // Calcular deuda total de todas las cuotas pendientes/mora de esta venta
        $totalDeudaVenta = round((float) PlanCuota::where('venta_id', $planCuota->venta_id)
            ->whereIn('estado', ['Pendiente', 'Mora'])
            ->sum('total_a_pagar'), 2);

        // REGLA: no se permite pagar más de lo que se debe en total
        $montoRecibido = min(round((float) $request->monto_recibido, 2), $totalDeudaVenta);
        $tipoPago      = 'completo';

        DB::transaction(function () use ($planCuota, $montoRecibido, $totalACobrar, &$tipoPago) {

            if ($montoRecibido < $totalACobrar) {
                // PAGO PARCIAL
                $tipoPago = 'parcial';
                $saldoRestante = round($totalACobrar - $montoRecibido, 2);
                $planCuota->update([
                    'monto_pagado'    => round($planCuota->monto_pagado + $montoRecibido, 2),
                    'saldo_pendiente' => $saldoRestante,
                    'total_a_pagar'   => $saldoRestante,
                    'tipo_pago'       => 'parcial',
                    'fecha_pago'      => now()->toDateString(),
                ]);
                return;
            }

            if (abs($montoRecibido - $totalACobrar) < 0.01) {
                // PAGO EXACTO
                $tipoPago = 'completo';
                $planCuota->update([
                    'monto_pagado'    => $totalACobrar,
                    'saldo_pendiente' => 0,
                    'estado'          => 'Pagada',
                    'tipo_pago'       => 'completo',
                    'fecha_pago'      => now()->toDateString(),
                ]);
                $this->actualizarEstadoCliente($planCuota);
                return;
            }

            // PAGO CON EXCEDENTE
            $tipoPago = 'anticipado';
            $planCuota->update([
                'monto_pagado'    => $totalACobrar,
                'saldo_pendiente' => 0,
                'estado'          => 'Pagada',
                'tipo_pago'       => 'anticipado',
                'fecha_pago'      => now()->toDateString(),
            ]);

            $excedente = round($montoRecibido - $totalACobrar, 2);

            $cuotasSiguientes = PlanCuota::where('venta_id', $planCuota->venta_id)
                ->where('numero_cuota', '>', $planCuota->numero_cuota)
                ->whereIn('estado', ['Pendiente', 'Mora'])
                ->orderBy('numero_cuota')
                ->get();

            foreach ($cuotasSiguientes as $siguiente) {
                if ($excedente <= 0) break;
                $totalSiguiente = round((float) $siguiente->total_a_pagar, 2);

                if ($excedente >= $totalSiguiente - 0.01) {
                    $siguiente->update([
                        'monto_pagado'    => $totalSiguiente,
                        'saldo_pendiente' => 0,
                        'estado'          => 'Pagada',
                        'tipo_pago'       => 'anticipado',
                        'fecha_pago'      => now()->toDateString(),
                    ]);
                    $excedente = round($excedente - $totalSiguiente, 2);
                } else {
                    $saldoRestante = round($totalSiguiente - $excedente, 2);
                    $siguiente->update([
                        'monto_pagado'    => round($siguiente->monto_pagado + $excedente, 2),
                        'saldo_pendiente' => $saldoRestante,
                        'total_a_pagar'   => $saldoRestante,
                        'tipo_pago'       => 'parcial',
                        'fecha_pago'      => now()->toDateString(),
                    ]);
                    $excedente = 0;
                }
            }

            $this->actualizarEstadoCliente($planCuota);
        });

        $mensajes = [
            'parcial'    => 'Pago parcial registrado. El saldo restante fue actualizado en la cuota.',
            'anticipado' => 'Pago registrado. El excedente fue aplicado automáticamente a las cuotas siguientes.',
            'completo'   => 'Cuota pagada completamente.',
        ];

        return back()->with('success', $mensajes[$tipoPago] ?? 'Pago registrado.');
    }

    private function actualizarEstadoCliente(PlanCuota $planCuota): void
    {
        $cliente = $planCuota->venta->cliente;
        $tieneMora = PlanCuota::whereHas('venta', fn($q) => $q->where('cliente_id', $cliente->id))
            ->where('estado', 'Mora')
            ->exists();

        if (!$tieneMora && $cliente->estado === 'Mora') {
            $cliente->update(['estado' => 'Activo']);
        }
    }

    private function actualizarMoras(): void
    {
        $cuotas = PlanCuota::where('estado', 'Pendiente')
            ->whereDate('fecha_vencimiento', '<', now())
            ->get();

        foreach ($cuotas as $cuota) {

            // ejemplo: mora del 5%
            $recargo = round($cuota->monto_cuota * 0.05, 2);

            $cuota->update([
                'recargo_mora'  => $recargo,
                'total_a_pagar' => round($cuota->monto_cuota + $recargo, 2),
                'estado'        => 'Mora',
            ]);
        }
    }
}
