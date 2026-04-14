<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasa_interes', function (Blueprint $table) {
            $table->id();
            $table->integer('plazo_meses')->unique();
            $table->decimal('porcentaje', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasa_interes');
    }
};
