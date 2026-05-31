<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Consentimento;
use App\Models\User;

/**
 * Serviço de conformidade LGPD para o widget do dashboard admin.
 * Calcula 4 buckets agregados de consentimento — apenas COUNT(DISTINCT user_id),
 * sem expor nomes, e-mails ou qualquer dado pessoal.
 *
 * Base legal: Art. 6º VIII e X da LGPD (prestação de contas e responsabilização).
 *
 * TODO v0.9.6: substituir VERSAO_VIGENTE hardcoded por leitura de termos_consentimento:
 *   Cache::remember('versao_termo_titular', 300, fn() => TermoConsentimento::vigente()->value('versao'))
 */
class RelatorioConformidadeService
{
    // Versão do termo vigente — trocar aqui quando v0.9.6 implementar CRUD de termos
    private const VERSAO_VIGENTE = '1.0';

    /**
     * Retorna os 4 contadores de conformidade.
     *
     * @return array{aceitaram: int, recusaram: int, nunca_acessaram: int, versao_antiga: int}
     */
    public function calcular(): array
    {
        return [
            'aceitaram'       => $this->bucketAceitaram(),
            'recusaram'       => $this->bucketRecusaram(),
            'nunca_acessaram' => $this->bucketNuncaAcessaram(),
            'versao_antiga'   => $this->bucketVersaoAntiga(),
        ];
    }

    // Bucket 1 — pacientes com aceite vigente (não revogado) da versão atual
    private function bucketAceitaram(): int
    {
        return Consentimento::where('versao_termo', self::VERSAO_VIGENTE)
            ->where('tipo_termo', 'titular')
            ->whereNull('revogado_em')
            ->whereHas('user.paciente')
            ->distinct('user_id')
            ->count('user_id');
    }

    // Bucket 2 — pacientes que recusaram e não aceitaram depois
    private function bucketRecusaram(): int
    {
        return AuditLog::where('action', 'consentimento_recusado')
            ->whereHas('user.paciente')
            ->whereNotIn('user_id', function ($q) {
                $q->from('consentimentos')
                  ->where('versao_termo', self::VERSAO_VIGENTE)
                  ->where('tipo_termo', 'titular')
                  ->whereNull('revogado_em')
                  ->select('user_id');
            })
            ->distinct('user_id')
            ->count('user_id');
    }

    // Bucket 3 — pacientes cadastrados que nunca acessaram o sistema (sem login em audit_logs)
    private function bucketNuncaAcessaram(): int
    {
        // TODO produção: audit_logs.action sem índice — adicionar $table->index('action') em migration
        return User::whereHas('paciente')
            ->whereNotIn('id', function ($q) {
                $q->from('audit_logs')
                  ->where('action', 'login')
                  ->select('user_id');
            })
            ->count();
    }

    // Bucket 4 — pacientes com aceite de versão desatualizada (não revogado)
    private function bucketVersaoAntiga(): int
    {
        return Consentimento::where('versao_termo', '!=', self::VERSAO_VIGENTE)
            ->whereNull('revogado_em')
            ->whereHas('user.paciente')
            ->distinct('user_id')
            ->count('user_id');
    }
}
