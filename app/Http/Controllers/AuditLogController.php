<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // UX-06 (v0.10.1): filtros por ação, usuário, entidade e intervalo de datas (GET preservável)
        $filtroAction = $request->input('action');
        $filtroUser   = $request->input('user_id');
        $filtroModel  = $request->input('model_type');
        $dataDe       = $request->input('data_de');
        $dataAte      = $request->input('data_ate');

        $logs = $this->queryFiltrada($request)
            ->paginate(20)
            ->withQueryString();

        $totalLogs = AuditLog::count();

        // Opções dos selects de filtro — distintos do próprio audit_logs
        $acoes     = AuditLog::distinct()->orderBy('action')->pluck('action');
        $entidades = AuditLog::whereNotNull('model_type')->distinct()->orderBy('model_type')->pluck('model_type');
        $usuarios  = User::whereIn('id', AuditLog::whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->orderBy('name')->get();

        return view('content.pages.listagem_audit_logs', compact(
            'logs', 'totalLogs', 'acoes', 'entidades', 'usuarios',
            'filtroAction', 'filtroUser', 'filtroModel', 'dataDe', 'dataAte'
        ));
    }

    /*
     * ST-19: monta a MESMA query filtrada do index() (ação/usuário/entidade/datas),
     * sem paginação — reaproveitada pelo export para os resultados baterem com a tela.
     */
    private function queryFiltrada(Request $request)
    {
        $filtroAction = $request->input('action');
        $filtroUser   = $request->input('user_id');
        $filtroModel  = $request->input('model_type');
        $dataDe       = $request->input('data_de');
        $dataAte      = $request->input('data_ate');

        return AuditLog::with('user')
            ->when($filtroAction, fn($q) => $q->where('action', $filtroAction))
            ->when($filtroUser,   fn($q) => $q->where('user_id', $filtroUser))
            ->when($filtroModel,  fn($q) => $q->where('model_type', $filtroModel))
            ->when($dataDe,  fn($q) => $q->whereDate('created_at', '>=', $dataDe))
            ->when($dataAte, fn($q) => $q->whereDate('created_at', '<=', $dataAte))
            ->orderBy('created_at', 'desc');
    }

    /*
     * ST-19: exporta os registros de auditoria em CSV, respeitando os MESMOS filtros
     * aplicados na tela (ação/usuário/entidade/datas). LGPD Art. 6º VIII/X — responsabilização.
     * Streaming (chunk) para não estourar memória em bases grandes. old_values/new_values já
     * chegam sanitizados pelo AuditObserver (queixa/anamnese/diagnostico/conduta/campos ST-15
     * nunca entram em audit_logs) — o export não expõe nada que a tela/"Ver" já não mostrasse.
     */
    public function exportar(Request $request): StreamedResponse
    {
        $query = $this->queryFiltrada($request);

        $nomeArquivo = 'audit-logs-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 (abre correto no Excel)
            fputcsv($out, ['Data/Hora', 'Ação', 'Usuário', 'Entidade', 'ID do Registro', 'IP', 'Dados Anteriores', 'Dados Novos']);

            $query->chunk(500, function ($logs) use ($out) {
                foreach ($logs as $log) {
                    fputcsv($out, [
                        $log->created_at?->format('d/m/Y H:i:s'),
                        $log->action,
                        $log->user?->name ?? '—',
                        $log->model_type ?? '—',
                        $log->model_id ?? '—',
                        $log->ip_address ?? '—',
                        $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '',
                        $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '',
                    ]);
                }
            });

            fclose($out);
        }, $nomeArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
