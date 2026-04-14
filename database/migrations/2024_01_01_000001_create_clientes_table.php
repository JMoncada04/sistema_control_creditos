<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('identidad', 13)->unique();
            $table->string('nombre');
            $table->string('telefono', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->enum('estado', ['Activo', 'Mora'])->default('Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
