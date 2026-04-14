<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Venta;
use App\Models\PlanCuota;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'identidad',
        'nombre',
        'telefono',
        'direccion',
        'estado',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    public function enMora(): bool
    {
        return $this->cuotasEnMora()->exists();
    }

    public function cuotasEnMora()
    {
        return PlanCuota::whereHas('venta', fn($q) => $q->where('cliente_id', $this->id))
            ->where('estado', 'Mora');
    }

    /**
     * Sincroniza el estado Activo/Mora de todos los clientes según sus cuotas.
     * Llamar antes de mostrar listados de clientes o historial.
     */
    public static function sincronizarEstados(): void
    {
        // Poner en Mora a clientes que tengan al menos una cuota vencida
        static::whereHas('ventas', fn($q) =>
            $q->whereHas('planCuotas', fn($q2) => $q2->where('estado', 'Mora'))
        )
        ->where('estado', '!=', 'Mora')
        ->update(['estado' => 'Mora']);

        // Volver a Activo si ya no queda ninguna cuota en mora
        static::where('estado', 'Mora')
            ->whereDoesntHave('ventas', fn($q) =>
                $q->whereHas('planCuotas', fn($q2) => $q2->where('estado', 'Mora'))
            )
            ->update(['estado' => 'Activo']);
    }
}
