<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('tasa_interes_id')->constrained('tasa_interes')->onDelete('restrict');
            $table->decimal('total_bruto', 12, 2);
            $table->decimal('porcentaje_interes', 5, 2);
            $table->decimal('total_con_interes', 12, 2);
            $table->integer('plazo_meses');
            $table->date('fecha_venta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
