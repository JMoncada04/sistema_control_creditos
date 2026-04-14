<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'notificaciones' => function () use ($request) {
                if (! $request->user()) return [];
                return \App\Models\Producto::where('created_at', '>=', now()->subDays(7))
                    ->orderByDesc('created_at')
                    ->take(10)
                    ->get(['id', 'nombre', 'codigo', 'created_at'])
                    ->map(fn ($p) => [
                        'id'      => $p->id,
                        'mensaje' => 'Nuevo producto: ' . $p->nombre,
                        'codigo'  => $p->codigo,
                        'tiempo'  => $p->created_at->diffForHumans(),
                    ])
                    ->values()
                    ->toArray();
            },
        ];
    }
}
