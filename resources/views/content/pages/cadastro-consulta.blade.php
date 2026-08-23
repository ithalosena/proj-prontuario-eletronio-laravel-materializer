{{-- ================================================================
     VIEW APOSENTADA — E3b (v0.11.1, união atendimento↔consulta)

     O formulário de nova consulta agora vive DENTRO da tela do
     atendimento: detalhes_atendimento.blade.php + partials/_form_consulta.blade.php.

     ConsultaController::create() não renderiza mais esta view — a rota
     GET /cadastro-consulta?atendimento_id=X redireciona para
     /atendimentos/{id}?nova=1#nova-consulta (links antigos continuam funcionando).

     Este arquivo pode ser removido do repositório com segurança.
     ================================================================ --}}
