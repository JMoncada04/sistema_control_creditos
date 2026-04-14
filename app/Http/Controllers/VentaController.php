<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PlanCuota;
use App\Models\Producto;
use App\Models\TasaInteres;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VentaController extends Controller
{
    public function index(Request $request): Response
    {
        $ventas = Venta::with(['cliente', 'planCuotas'])
            ->when($request->search, fn($q, $s) =>
                $q->whereHas('cliente', fn($c) =>
                    $c->where('nombre', 'like', "%$s%")
                      ->orWhere('identidad', 'like', "%$s%")
                )
            )
            ->latest('fecha_venta')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($v) => [
                'id'               => $v->id,
                'cliente'          => $v->cliente->nombre,
                'cliente_id'       => $v->cliente_id,
                'fecha'            => $v->fecha_venta->format('d/m/Y'),
                'productos'        => $v->detalles()->count(),
                'total_bruto'      => $v->total_bruto,
                'porcentaje'       => $v->porcentaje_interes,
                'total_con_interes'=> $v->total_con_interes,
                'plazo_meses'      => $v->plazo_meses,
                'estado'           => $this->estadoVenta($v),
            ]);

        return Inertia::render('Ventas/Index', [
            'ventas'   => $ventas,
            'clientes' => Cliente::where('estado', 'Activo')->select('id', 'identidad', 'nombre')->get(),
            'productos'=> Producto::where('stock_actual', '>', 0)->select('id', 'nombre', 'precio_venta', 'stock_actual')->get(),
            'tasas'    => TasaInteres::orderBy('plazo_meses')->get(),
            'filters'  => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'plazo_meses'=> 'required|integer|min:1',
            'items'      => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        $tasa = TasaInteres::where('plazo_meses', $request->plazo_meses)->firstOrFail();

        DB::transaction(function () use ($request, $tasa) {
            $totalBruto = 0;

            foreach ($request->items as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                if ($producto->stock_actual < $item['cantidad']) {
                    abort(422, "Stock insuficiente para '{$producto->nombre}'.");
                }
                $totalBruto += $producto->precio_venta * $item['cantidad'];
            }

            $interes          = $totalBruto * ($tasa->porcentaje / 100);
            $totalConInteres  = round($totalBruto + $interes, 2);
            $montoCuota       = round($totalConInteres / $request->plazo_meses, 2);
            $fechaVenta       = Carbon::today();

            $venta = Venta::create([
                'cliente_id'         => $request->cliente_id,
                'tasa_interes_id'    => $tasa->id,
                'total_bruto'        => $totalBruto,
                'porcentaje_interes' => $tasa->porcentaje,
                'total_con_interes'  => $totalConInteres,
                'plazo_meses'        => $request->plazo_meses,
                'fecha_venta'        => $fechaVenta,
            ]);

            foreach ($request->items as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $venta->detalles()->create([
                    'producto_id'    => $producto->id,
                    'cantidad'       => $item['cantidad'],
                    'precio_unitario'=> $producto->precio_venta,
                    'subtotal'       => $producto->precio_venta * $item['cantidad'],
                ]);
                $producto->decrement('stock_actual', $item['cantidad']);
            }

            for ($i = 1; $i <= $request->plazo_meses; $i++) {
                PlanCuota::create([
                    'venta_id'         => $venta->id,
                    'numero_cuota'     => $i,
                    'fecha_vencimiento'=> $fechaVenta->copy()->addMonths($i),
                    'monto_cuota'      => $montoCuota,
                    'recargo_mora'     => 0,
                    'total_a_pagar'    => $montoCuota,
                    'monto_pagado'     => 0,
                    'estado'           => 'Pendiente',
                ]);
            }
        });

        return back()->with('success', 'Venta registrada y plan de cuotas generado.');
    }

    private function estadoVenta(Venta $v): string
    {
        if ($v->planCuotas->contains('estado', 'Mora')) return 'Mora';
        if ($v->planCuotas->every(fn($c) => $c->estado === 'Pagada')) return 'Cancelado';
        return 'Al día';
    }
}
