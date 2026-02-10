<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->onDelete('cascade');
            $table->string('tipo')->nullable();
            $table->text('observacao')->nullable();
            $table->date('data_solicitacao');
            $table->date('data_resultado')->nullable();
            $table->text('resultado')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('consulta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exames');
    }
};
