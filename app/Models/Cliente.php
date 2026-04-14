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

    public static function sincronizarEstados(): void
    {
        static::whereHas('ventas', fn($q) =>
            $q->whereHas('planCuotas', fn($q2) => $q2->where('estado', 'Mora'))
        )
        ->where('estado', '!=', 'Mora')
        ->update(['estado' => 'Mora']);

        static::where('estado', 'Mora')
            ->whereDoesntHave('ventas', fn($q) =>
                $q->whereHas('planCuotas', fn($q2) => $q2->where('estado', 'Mora'))
            )
            ->update(['estado' => 'Activo']);
    }
}
