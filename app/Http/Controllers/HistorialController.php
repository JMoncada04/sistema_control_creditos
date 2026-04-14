<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistorialController extends Controller
{
    public function index(Request $request): Response
    {
        Cliente::sincronizarEstados();

        $historial = Venta::with(['cliente', 'planCuotas'])
            ->when($request->search, fn($q, $s) =>
                $q->whereHas('cliente', fn($c) =>
                    $c->where('nombre', 'like', "%$s%")
                      ->orWhere('identidad', 'like', "%$s%")
                )->orWhere('id', ltrim($s, '#'))
            )
            ->latest('fecha_venta')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($v) => [
                'id'             => $v->id,
                'identidad'      => $v->cliente->identidad,
                'cliente'        => $v->cliente->nombre,
                'venta_id'       => $v->id,
                'fecha'          => $v->fecha_venta->format('d/m/Y'),
                'total'          => $v->total_con_interes,
                'pagado'         => $v->planCuotas->sum('monto_pagado'),
                'saldo'          => $v->total_con_interes - $v->planCuotas->sum('monto_pagado'),
                'cuotas_pagadas' => $v->planCuotas->where('estado', 'Pagada')->count(),
                'cuotas_total'   => $v->planCuotas->count(),
                'estado'         => $v->cliente->estado,
            ]);

        return Inertia::render('Historial/Index', [
            'historial' => $historial,
            'filters'   => $request->only('search'),
        ]);
    }
}
