<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\PlanCuota;
use App\Models\Producto;
use App\Models\TasaInteres;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CreditosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTasas();
        $this->seedProductos();

        $clientes = $this->seedClientes();

        $tasa3  = TasaInteres::where('plazo_meses', 3)->first();
        $tasa6  = TasaInteres::where('plazo_meses', 6)->first();
        $tasa12 = TasaInteres::where('plazo_meses', 12)->first();

        $prods = Producto::orderBy('id')->get();

        $tv          = $prods->firstWhere('codigo', 'TV-001');
        $laptop      = $prods->firstWhere('codigo', 'LAP-002');
        $celular     = $prods->firstWhere('codigo', 'CEL-003');
        $refrigeradora = $prods->firstWhere('codigo', 'REF-004');
        $lavadora    = $prods->firstWhere('codigo', 'LAV-005');
        $aire        = $prods->firstWhere('codigo', 'AC-006');
        $microondas  = $prods->firstWhere('codigo', 'MIC-007');

        // ── JUAN CARLOS FLORES: crédito de 6 meses completamente pagado (hace 8 meses)
        $this->crearVenta($clientes[0], $tasa6, [[$tv, 1]], Carbon::now()->subMonths(8), 'pagado_completo');

        // ── MARÍA ELENA RODRÍGUEZ: crédito de 12 meses completamente pagado (hace 14 meses)
        $this->crearVenta($clientes[1], $tasa12, [[$laptop, 1]], Carbon::now()->subMonths(14), 'pagado_completo');

        // ── CARLOS ALBERTO LÓPEZ: crédito al día — 4 meses, la mitad pagada
        $this->crearVenta($clientes[2], $tasa6, [[$celular, 2]], Carbon::now()->subMonths(4), 'mitad_pagado');

        // ── ANA LUCÍA MARTÍNEZ: crédito reciente — 2 de 3 meses pendientes
        $this->crearVenta($clientes[3], $tasa3, [[$microondas, 1]], Carbon::now()->subMonths(2), 'reciente_al_dia');

        // ── ROBERTO GARCÍA: crédito largo plazo — solo inicio pagado
        $this->crearVenta($clientes[4], $tasa12, [[$refrigeradora, 1]], Carbon::now()->subMonths(3), 'inicio_largo_plazo');

        // ── SANDRA PATRICIA DÍAZ: en mora — 2 cuotas vencidas sin pagar
        $this->crearVenta($clientes[5], $tasa6, [[$lavadora, 1]], Carbon::now()->subMonths(5), 'en_mora');

        // ── MIGUEL ÁNGEL TORRES: mora severa — 4 cuotas vencidas sin pagar
        $this->crearVenta($clientes[6], $tasa6, [[$aire, 1]], Carbon::now()->subMonths(7), 'mora_severa');

        // ── KARLA VÁSQUEZ: crédito corto ya pagado
        $this->crearVenta($clientes[7], $tasa3, [[$microondas, 1]], Carbon::now()->subMonths(5), 'pagado_completo');

        // ── KARLA VÁSQUEZ: segundo crédito en mora (cliente mixto)
        $this->crearVenta($clientes[7], $tasa12, [[$tv, 1]], Carbon::now()->subMonths(4), 'en_mora_largo');

        // ── PEDRO SANTOS: crédito este mes, aún sin pagos
        $this->crearVenta($clientes[8], $tasa6, [[$celular, 1], [$microondas, 1]], Carbon::now()->subMonths(1), 'nuevo_sin_pagar');

        // ── ROSA MEDINA: crédito de hace 2 meses al día
        $this->crearVenta($clientes[9], $tasa3, [[$laptop, 1]], Carbon::now()->subMonths(2), 'reciente_al_dia');

        Cliente::sincronizarEstados();
    }

    private function seedTasas(): void
    {
        $tasas = [
            ['plazo_meses' => 3,  'porcentaje' => 5.00],
            ['plazo_meses' => 6,  'porcentaje' => 10.00],
            ['plazo_meses' => 12, 'porcentaje' => 18.00],
        ];

        foreach ($tasas as $tasa) {
            TasaInteres::create($tasa);
        }
    }

    private function seedProductos(): void
    {
        $productos = [
            ['codigo' => 'TV-001',  'nombre' => 'Smart TV 55"',           'precio_venta' => 8500.00,  'stock_actual' => 12, 'stock_minimo' => 3],
            ['codigo' => 'LAP-002', 'nombre' => 'Laptop HP 15"',          'precio_venta' => 12000.00, 'stock_actual' => 7,  'stock_minimo' => 3],
            ['codigo' => 'CEL-003', 'nombre' => 'Samsung Galaxy A54',     'precio_venta' => 6800.00,  'stock_actual' => 16, 'stock_minimo' => 5],
            ['codigo' => 'REF-004', 'nombre' => 'Refrigeradora 18 pies',  'precio_venta' => 9500.00,  'stock_actual' => 4,  'stock_minimo' => 2],
            ['codigo' => 'LAV-005', 'nombre' => 'Lavadora 15 kg',         'precio_venta' => 7200.00,  'stock_actual' => 2,  'stock_minimo' => 2],
            ['codigo' => 'AC-006',  'nombre' => 'Aire Acondicionado 12K', 'precio_venta' => 11000.00, 'stock_actual' => 1,  'stock_minimo' => 3],
            ['codigo' => 'MIC-007', 'nombre' => 'Microondas 1.1 pies',   'precio_venta' => 2800.00,  'stock_actual' => 10, 'stock_minimo' => 4],
        ];

        foreach ($productos as $p) {
            Producto::create($p);
        }
    }

    private function seedClientes(): array
    {
        $data = [
            ['identidad' => '0801199001234', 'nombre' => 'Juan Carlos Flores',     'telefono' => '9901-2345', 'direccion' => 'Col. Kennedy, Tegucigalpa',        'estado' => 'Activo'],
            ['identidad' => '0101198502345', 'nombre' => 'María Elena Rodríguez',  'telefono' => '9912-3456', 'direccion' => 'Res. Las Lomas, San Pedro Sula',    'estado' => 'Activo'],
            ['identidad' => '0801200003456', 'nombre' => 'Carlos Alberto López',   'telefono' => '9923-4567', 'direccion' => 'Col. Alameda, Tegucigalpa',         'estado' => 'Activo'],
            ['identidad' => '0501199104567', 'nombre' => 'Ana Lucía Martínez',     'telefono' => '9934-5678', 'direccion' => 'Barrio El Olvido, La Ceiba',        'estado' => 'Activo'],
            ['identidad' => '0101200205678', 'nombre' => 'Roberto Enrique García', 'telefono' => '9945-6789', 'direccion' => 'Col. Los Andes, Tegucigalpa',       'estado' => 'Activo'],
            ['identidad' => '0301199306789', 'nombre' => 'Sandra Patricia Díaz',   'telefono' => '9956-7890', 'direccion' => 'Barrio Abajo, Tegucigalpa',         'estado' => 'Activo'],
            ['identidad' => '0801198807890', 'nombre' => 'Miguel Ángel Torres',    'telefono' => '9967-8901', 'direccion' => 'Col. Villa Nueva, Comayagua',       'estado' => 'Activo'],
            ['identidad' => '0101199508901', 'nombre' => 'Karla Fernanda Vásquez', 'telefono' => '9978-9012', 'direccion' => 'Col. Prado Alto, Tegucigalpa',      'estado' => 'Activo'],
            ['identidad' => '0801199709012', 'nombre' => 'Pedro Antonio Santos',   'telefono' => '9989-0123', 'direccion' => 'Res. El Sauce, San Pedro Sula',     'estado' => 'Activo'],
            ['identidad' => '0401200010123', 'nombre' => 'Rosa Isabel Medina',     'telefono' => '9990-1234', 'direccion' => 'Col. Miraflores, Tegucigalpa',      'estado' => 'Activo'],
        ];

        $clientes = [];
        foreach ($data as $c) {
            $clientes[] = Cliente::create($c);
        }
        return $clientes;
    }

    private function crearVenta(
        Cliente $cliente,
        TasaInteres $tasa,
        array $items,
        Carbon $fechaVenta,
        string $escenario
    ): void {
        $totalBruto = 0;
        foreach ($items as [$producto, $cantidad]) {
            $totalBruto += $producto->precio_venta * $cantidad;
        }

        $interes         = round($totalBruto * ($tasa->porcentaje / 100), 2);
        $totalConInteres = round($totalBruto + $interes, 2);
        $plazo           = $tasa->plazo_meses;
        $montoCuota      = round($totalConInteres / $plazo, 2);

        $venta = Venta::create([
            'cliente_id'         => $cliente->id,
            'tasa_interes_id'    => $tasa->id,
            'total_bruto'        => $totalBruto,
            'porcentaje_interes' => $tasa->porcentaje,
            'total_con_interes'  => $totalConInteres,
            'plazo_meses'        => $plazo,
            'fecha_venta'        => $fechaVenta->toDateString(),
        ]);

        foreach ($items as [$producto, $cantidad]) {
            DetalleVenta::create([
                'venta_id'        => $venta->id,
                'producto_id'     => $producto->id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $producto->precio_venta,
                'subtotal'        => $producto->precio_venta * $cantidad,
            ]);
        }

        for ($i = 1; $i <= $plazo; $i++) {
            $fechaVencimiento = $fechaVenta->copy()->addMonths($i);
            $estado           = $this->estado($escenario, $i, $plazo);
            $recargo          = ($estado === 'Mora') ? round($montoCuota * 0.05, 2) : 0;
            $totalAPagar      = round($montoCuota + $recargo, 2);
            $montoPagado      = ($estado === 'Pagada') ? $montoCuota : 0;
            $fechaPago        = ($estado === 'Pagada')
                ? $fechaVencimiento->copy()->subDays(rand(1, 5))->toDateString()
                : null;

            PlanCuota::create([
                'venta_id'          => $venta->id,
                'numero_cuota'      => $i,
                'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                'monto_cuota'       => $montoCuota,
                'saldo_pendiente'   => ($estado === 'Pagada') ? 0 : $totalAPagar,
                'recargo_mora'      => $recargo,
                'total_a_pagar'     => ($estado === 'Pagada') ? $montoCuota : $totalAPagar,
                'monto_pagado'      => $montoPagado,
                'tipo_pago'         => ($estado === 'Pagada') ? 'completo' : 'ninguno',
                'estado'            => $estado,
                'fecha_pago'        => $fechaPago,
            ]);
        }
    }

    private function estado(string $escenario, int $num, int $total): string
    {
        return match ($escenario) {
            'pagado_completo'    => 'Pagada',
            'mitad_pagado'       => $num <= intval($total / 2) ? 'Pagada' : 'Pendiente',
            'reciente_al_dia'    => $num === 1 ? 'Pagada' : 'Pendiente',
            'inicio_largo_plazo' => $num <= 3 ? 'Pagada' : 'Pendiente',
            'nuevo_sin_pagar'    => 'Pendiente',
            'en_mora' => match (true) {
                $num <= 2 => 'Pagada',
                $num <= 4 => 'Mora',
                default   => 'Pendiente',
            },
            'mora_severa' => match (true) {
                $num === 1 => 'Pagada',
                $num <= 5  => 'Mora',
                default    => 'Pendiente',
            },
            'en_mora_largo' => match (true) {
                $num <= 2 => 'Pagada',
                $num <= 5 => 'Mora',
                default   => 'Pendiente',
            },
            default => 'Pendiente',
        };
    }
}
