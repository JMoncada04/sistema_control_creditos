<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    public function index(Request $request): Response
    {
        Cliente::sincronizarEstados();

        $clientes = Cliente::withCount('ventas')
            ->when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('identidad', 'like', "%$s%")
            )
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($c) => [
                'id'        => $c->id,
                'identidad' => $c->identidad,
                'nombre'    => $c->nombre,
                'telefono'  => $c->telefono,
                'direccion' => $c->direccion,
                'estado'    => $c->estado,
                'creditos'  => $c->ventas_count,
            ]);

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters'  => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'identidad' => 'required|string|max:13|unique:clientes,identidad',
            'nombre'    => 'required|string|max:150',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ], [
            'identidad.unique' => 'Ya existe un cliente con ese número de identidad.',
        ]);

        Cliente::create([
            'identidad' => $request->identidad,
            'nombre'    => $request->nombre,
            'telefono'  => $request->telefono,
            'direccion' => $request->direccion,
            'estado'    => 'Activo',
        ]);

        return back()->with('success', 'Cliente registrado correctamente.');
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $request->validate([
            'nombre'    => 'required|string|max:150',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        $cliente->update($request->only('nombre', 'telefono', 'direccion'));

        return back()->with('success', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        if ($cliente->ventas()->exists()) {
            $tieneActivos = $cliente->ventas()
                ->whereHas('planCuotas', fn($q) => $q->whereIn('estado', ['Pendiente', 'Mora']))
                ->exists();

            $msg = $tieneActivos
                ? 'No se puede eliminar a "'.$cliente->nombre.'" porque tiene créditos activos pendientes de pago.'
                : 'No se puede eliminar a "'.$cliente->nombre.'" porque tiene historial de créditos registrado en el sistema.';

            return back()->with('error', $msg);
        }

        $cliente->delete();

        return back()->with('success', 'Cliente eliminado correctamente.');
    }
}
