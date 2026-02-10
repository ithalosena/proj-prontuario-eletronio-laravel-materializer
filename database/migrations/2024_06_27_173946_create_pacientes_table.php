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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nome');
            $table->string('contato')->nullable();
            $table->string('documento')->unique();
            $table->date('data_nascimento');
            $table->enum('sexo', ['M', 'F', 'outro']);
            $table->string('endereco')->nullable();
            $table->string('matricula')->unique();
            $table->string('curso');
            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
            $table->index('curso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
