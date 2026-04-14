<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->integer('numero_cuota');
            $table->date('fecha_vencimiento');
            $table->decimal('monto_cuota', 12, 2);
            $table->decimal('saldo_pendiente', 12, 2)->default(0);
            $table->decimal('recargo_mora', 12, 2)->default(0);
            $table->decimal('total_a_pagar', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->enum('tipo_pago', ['ninguno', 'completo', 'parcial', 'anticipado'])->default('ninguno');
            $table->enum('estado', ['Pendiente', 'Pagada', 'Mora'])->default('Pendiente');
            $table->date('fecha_pago')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_cuotas');
    }
};
