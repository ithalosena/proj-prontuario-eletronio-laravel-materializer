<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Migra dados de `disponibilidades` → `disponibilidade_blocos` e dropa a tabela antiga.
 * Apenas registros `ativo = true` viram blocos — inativo era "sem horário naquele dia".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('disponibilidades')) {
            return;
        }

        $registros = DB::table('disponibilidades')->where('ativo', true)->get();

        foreach ($registros as $r) {
            DB::table('disponibilidade_blocos')->insert([
                'profissional_id' => $r->profissional_id,
                'dia_semana'      => $r->dia_semana,
                'hora_inicio'     => $r->hora_inicio,
                'hora_fim'        => $r->hora_fim,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        Schema::drop('disponibilidades');
    }

    public function down(): void
    {
        // Recria a tabela original vazia (dados não são recuperáveis no rollback)
        Schema::create('disponibilidades', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->unique(['profissional_id', 'dia_semana']);
        });
    }
};
