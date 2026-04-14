<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PlanCuota;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $hoy = Carbon::today();

        // ── Tarjetas de estadísticas ─────────────────────────────────────────
        $totalCreditosActivos = Venta::whereHas(
            'planCuotas',
            fn($q) => $q->whereIn('estado', ['Pendiente', 'Mora'])
        )->count();

        $cobradoEsteMes = PlanCuota::where('estado', 'Pagada')
            ->whereMonth('fecha_pago', $hoy->month)
            ->whereYear('fecha_pago', $hoy->year)
            ->sum('monto_pagado');

        $cuotasPendientes = PlanCuota::where('estado', 'Pendiente')
            ->where('fecha_vencimiento', '<=', $hoy->copy()->addDays(30))
            ->count();

        $clientesEnMora = Cliente::where('estado', 'Mora')->count();

        $totalClientes  = Cliente::count();
        $totalProductos = Producto::count();

        // ── Cobrado por mes (últimos 6 meses) ───────────────────────────────
        $cobradoPorMes = collect(range(5, 0))->map(function ($i) use ($hoy) {
            $mes = $hoy->copy()->subMonths($i);
            $cobrado = PlanCuota::where('estado', 'Pagada')
                ->whereMonth('fecha_pago', $mes->month)
                ->whereYear('fecha_pago', $mes->year)
                ->sum('monto_pagado');
            return [
                'mes'     => $mes->translatedFormat('M Y'),
                'cobrado' => round((float) $cobrado, 2),
            ];
        })->values();

        // ── Distribución de cuotas por estado ───────────────────────────────
        $distEstados = [
            ['estado' => 'Pagada',   'total' => PlanCuota::where('estado', 'Pagada')->count()],
            ['estado' => 'Pendiente','total' => PlanCuota::where('estado', 'Pendiente')->count()],
            ['estado' => 'Mora',     'total' => PlanCuota::where('estado', 'Mora')->count()],
        ];

        // ── Top 5 productos más vendidos a crédito ──────────────────────────
        $topProductos = \App\Models\DetalleVenta::selectRaw('producto_id, SUM(cantidad) as total_vendido')
            ->with('producto:id,nombre')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get()
            ->map(fn($d) => [
                'nombre'  => $d->producto->nombre ?? 'N/A',
                'vendido' => (int) $d->total_vendido,
            ]);

        // ── Ingresos proyectados (cuotas pendientes) vs cobrados este mes ───
        $proyectadoMes = PlanCuota::whereIn('estado', ['Pendiente', 'Mora'])
            ->whereMonth('fecha_vencimiento', $hoy->month)
            ->whereYear('fecha_vencimiento', $hoy->year)
            ->sum('total_a_pagar');

        // ── Cuotas próximas a vencer (7 días) ───────────────────────────────
        $proximasCuotas = PlanCuota::with(['venta.cliente'])
            ->whereIn('estado', ['Pendiente', 'Mora'])
            ->where('fecha_vencimiento', '<=', $hoy->copy()->addDays(7))
            ->orderBy('fecha_vencimiento')
            ->take(6)
            ->get()
            ->map(fn($c) => [
                'id'      => $c->id,
                'cliente' => $c->venta->cliente->nombre,
                'venta'   => $c->venta_id,
                'cuota'   => $c->numero_cuota . '/' . $c->venta->planCuotas()->count(),
                'fecha'   => $c->fecha_vencimiento->format('d/m/Y'),
                'monto'   => $c->total_a_pagar,
                'estado'  => $c->estado,
            ]);

        // ── Productos con stock bajo ─────────────────────────────────────────
        $productosStockBajo = Producto::whereRaw('stock_actual <= stock_minimo + 2')
            ->orderByRaw('stock_actual - stock_minimo')
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id'     => $p->id,
                'nombre' => $p->nombre,
                'stock'  => $p->stock_actual,
                'minimo' => $p->stock_minimo,
                'max'    => max($p->stock_minimo * 4, 10),
            ]);

        // ── Últimas ventas ───────────────────────────────────────────────────
        $ultimasVentas = Venta::with(['cliente', 'planCuotas'])
            ->latest('fecha_venta')
            ->take(5)
            ->get()
            ->map(fn($v) => [
                'id'       => $v->id,
                'cliente'  => $v->cliente->nombre,
                'fecha'    => $v->fecha_venta->format('d/m/Y'),
                'plazo'    => $v->plazo_meses,
                'total'    => $v->total_con_interes,
                'progreso' => $v->planCuotas->count()
                    ? round($v->planCuotas->where('estado', 'Pagada')->count() / $v->planCuotas->count() * 100)
                    : 0,
            ]);

        // ── Alertas ──────────────────────────────────────────────────────────
        $alertasMora = PlanCuota::with(['venta.cliente'])
            ->where('estado', 'Mora')
            ->get()
            ->map(fn($c) => [
                'tipo'   => 'mora',
                'titulo' => $c->venta->cliente->nombre . ' — Venta #' . $c->venta_id,
                'desc'   => 'Cuota ' . $c->numero_cuota . ' vencida con recargo de mora.',
                'color'  => 'error',
                'icon'   => 'mdi-alert-circle',
            ]);

        $alertasStock = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->get()
            ->map(fn($p) => [
                'tipo'   => 'stock',
                'titulo' => 'Stock crítico: ' . $p->nombre,
                'desc'   => 'Solo ' . $p->stock_actual . ' unidades. Mínimo: ' . $p->stock_minimo,
                'color'  => 'warning',
                'icon'   => 'mdi-alert',
            ]);

        $alertas = collect($alertasMora)->merge(collect($alertasStock))->values();

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'creditosActivos'  => $totalCreditosActivos,
                'cobradoEsteMes'   => round((float) $cobradoEsteMes, 2),
                'proyectadoMes'    => round((float) $proyectadoMes, 2),
                'cuotasPendientes' => $cuotasPendientes,
                'clientesEnMora'   => $clientesEnMora,
                'totalClientes'    => $totalClientes,
                'totalProductos'   => $totalProductos,
            ],
            'cobradoPorMes'      => $cobradoPorMes,
            'distEstados'        => $distEstados,
            'topProductos'       => $topProductos,
            'proximasCuotas'     => $proximasCuotas,
            'productosStockBajo' => $productosStockBajo,
            'ultimasVentas'      => $ultimasVentas,
            'alertas'            => $alertas,
        ]);
    }

    public function pdf(): \Illuminate\Http\Response
    {
        // Reutilizamos los mismos datos del index
        $data = $this->getDashboardData();
        $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.dashboard', $data)
            ->setPaper('letter', 'portrait');
        return $pdf->download('reporte-dashboard-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getDashboardData(): array
    {
        $hoy = Carbon::today();
        return [
            'fecha'            => $hoy->translatedFormat('d \d\e F \d\e Y'),
            'cobradoEsteMes'   => round((float) PlanCuota::where('estado','Pagada')->whereMonth('fecha_pago',$hoy->month)->whereYear('fecha_pago',$hoy->year)->sum('monto_pagado'), 2),
            'creditosActivos'  => Venta::whereHas('planCuotas', fn($q) => $q->whereIn('estado',['Pendiente','Mora']))->count(),
            'clientesEnMora'   => Cliente::where('estado','Mora')->count(),
            'cuotasPendientes' => PlanCuota::where('estado','Pendiente')->where('fecha_vencimiento','<=',$hoy->copy()->addDays(30))->count(),
            'proximasCuotas'   => PlanCuota::with(['venta.cliente'])->whereIn('estado',['Pendiente','Mora'])->where('fecha_vencimiento','<=',$hoy->copy()->addDays(7))->orderBy('fecha_vencimiento')->take(10)->get()->map(fn($c) => ['cliente'=>$c->venta->cliente->nombre,'venta'=>$c->venta_id,'cuota'=>$c->numero_cuota,'fecha'=>$c->fecha_vencimiento->format('d/m/Y'),'monto'=>$c->total_a_pagar,'estado'=>$c->estado]),
            'alertasMora'      => PlanCuota::with(['venta.cliente'])->where('estado','Mora')->get()->map(fn($c) => ['cliente'=>$c->venta->cliente->nombre,'venta'=>$c->venta_id,'cuota'=>$c->numero_cuota]),
        ];
    }
}
