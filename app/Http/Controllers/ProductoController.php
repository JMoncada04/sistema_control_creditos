<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(Request $request): Response
    {
        $productos = Producto::when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('codigo', 'like', "%$s%")
            )
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($p) => [
                'id'          => $p->id,
                'codigo'      => $p->codigo,
                'nombre'      => $p->nombre,
                'precio_venta'=> $p->precio_venta,
                'stock_actual'=> $p->stock_actual,
                'stock_minimo'=> $p->stock_minimo,
                'stock_bajo'  => $p->tieneStockBajo(),
            ]);

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'filters'   => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'codigo'       => 'required|string|max:30|unique:productos,codigo',
            'nombre'       => 'required|string|max:150',
            'precio_venta' => 'required|numeric|min:0.01',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ]);

        Producto::create($request->only('codigo', 'nombre', 'precio_venta', 'stock_actual', 'stock_minimo'));

        return back()->with('success', 'Producto registrado correctamente.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $request->validate([
            'nombre'       => 'required|string|max:150',
            'precio_venta' => 'required|numeric|min:0.01',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ]);

        $producto->update($request->only('nombre', 'precio_venta', 'stock_actual', 'stock_minimo'));

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->detalleVentas()->exists()) {
            return back()->with('error', 'No se puede eliminar un producto que ya tiene ventas registradas.');
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado.');
    }
}
