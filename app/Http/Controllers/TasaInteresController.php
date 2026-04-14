<?php

namespace App\Http\Controllers;

use App\Models\TasaInteres;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TasaInteresController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tasas/Index', [
            'tasas' => TasaInteres::orderBy('plazo_meses')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'plazo_meses' => 'required|integer|min:1|unique:tasa_interes,plazo_meses',
            'porcentaje'  => 'required|numeric|min:0.01|max:100',
        ], [
            'plazo_meses.unique' => 'Ya existe una tasa para ese plazo. Edítala en lugar de crear una nueva.',
        ]);

        TasaInteres::create($request->only('plazo_meses', 'porcentaje'));

        return back()->with('success', 'Tasa registrada correctamente.');
    }

    public function update(Request $request, TasaInteres $tasaIntere): RedirectResponse
    {
        $request->validate([
            'porcentaje' => 'required|numeric|min:0.01|max:100',
        ]);

        $tasaIntere->update(['porcentaje' => $request->porcentaje]);

        return back()->with('success', 'Tasa actualizada.');
    }

    public function destroy(TasaInteres $tasaIntere): RedirectResponse
    {
        if ($tasaIntere->ventas()->exists()) {
            return back()->with('error', 'No se puede eliminar una tasa que ya fue usada en ventas.');
        }

        $tasaIntere->delete();

        return back()->with('success', 'Tasa eliminada.');
    }
}
