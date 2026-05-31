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
        Schema::table('pacientes', function (Blueprint $table) {
            // Dados complementares de identidade (após nome)
            $table->string('nome_social', 255)->nullable()->after('nome');

            // Contatos adicionais (após contato existente)
            $table->string('telefone_alternativo', 20)->nullable()->after('contato');
            $table->string('email_alternativo', 255)->nullable()->after('telefone_alternativo');

            // Endereço estruturado — mantém coluna endereco como fallback para registros pré-onboarding
            $table->string('cep', 9)->nullable()->after('endereco');
            $table->string('logradouro', 255)->nullable()->after('cep');
            $table->string('numero', 20)->nullable()->after('logradouro');
            $table->string('complemento', 100)->nullable()->after('numero');
            $table->string('bairro', 100)->nullable()->after('complemento');
            $table->string('cidade', 100)->nullable()->after('bairro');
            $table->char('uf', 2)->nullable()->after('cidade');
            $table->string('ponto_referencia', 255)->nullable()->after('uf');

            // Dados complementares
            $table->string('naturalidade_cidade', 100)->nullable();
            $table->char('naturalidade_uf', 2)->nullable();
            $table->enum('raca_cor', ['branca','preta','parda','amarela','indigena','nao_declarado'])->nullable();
            $table->enum('estado_civil', ['solteiro','casado','divorciado','viuvo','uniao_estavel'])->nullable();
            $table->string('nome_mae', 255)->nullable();

            // Contato de emergência primário (obrigatório no wizard)
            $table->string('contato_emergencia_nome', 255)->nullable();
            $table->string('contato_emergencia_telefone', 20)->nullable();
            $table->string('contato_emergencia_parentesco', 50)->nullable();

            // Contato de emergência secundário (opcional)
            $table->string('contato_emergencia2_nome', 255)->nullable();
            $table->string('contato_emergencia2_telefone', 20)->nullable();
            $table->string('contato_emergencia2_parentesco', 50)->nullable();

            // Responsável legal (obrigatório se idade < 18)
            $table->string('responsavel_nome', 255)->nullable();
            $table->string('responsavel_cpf', 14)->nullable();
            $table->string('responsavel_telefone', 20)->nullable();
            $table->string('responsavel_email', 255)->nullable();
            $table->string('responsavel_parentesco', 50)->nullable();

            // Dados clínicos autorreferidos (todos opcionais)
            $table->enum('tipo_sanguineo', ['A+','A-','B+','B-','AB+','AB-','O+','O-','NS'])->nullable();
            $table->decimal('peso_kg', 5, 2)->nullable();
            $table->smallInteger('altura_cm')->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos_uso_continuo')->nullable();
            $table->text('condicoes_cronicas')->nullable();
            $table->text('cirurgias_previas')->nullable();
            $table->enum('tabagismo', ['nao','ex_fumante','sim'])->nullable();
            $table->enum('etilismo', ['nao','ocasional','frequente'])->nullable();
            $table->enum('atividade_fisica', ['sedentario','leve','moderada'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn([
                'nome_social', 'telefone_alternativo', 'email_alternativo',
                'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'ponto_referencia',
                'naturalidade_cidade', 'naturalidade_uf', 'raca_cor', 'estado_civil', 'nome_mae',
                'contato_emergencia_nome', 'contato_emergencia_telefone', 'contato_emergencia_parentesco',
                'contato_emergencia2_nome', 'contato_emergencia2_telefone', 'contato_emergencia2_parentesco',
                'responsavel_nome', 'responsavel_cpf', 'responsavel_telefone', 'responsavel_email', 'responsavel_parentesco',
                'tipo_sanguineo', 'peso_kg', 'altura_cm',
                'alergias', 'medicamentos_uso_continuo', 'condicoes_cronicas', 'cirurgias_previas',
                'tabagismo', 'etilismo', 'atividade_fisica',
            ]);
        });
    }
};
