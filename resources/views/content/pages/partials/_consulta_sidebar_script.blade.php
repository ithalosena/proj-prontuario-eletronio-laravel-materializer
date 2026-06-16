<script>
// UX-P09 (v0.10.2): preenche o painel lateral com os dados da consulta selecionada (data-* da opção).
// Compartilhado pelas telas de cadastro/edição de exame e prescrição.
(function () {
  var sel    = document.getElementById('consulta_id');
  var vazio  = document.getElementById('resumo-vazio');
  var pronto = document.getElementById('resumo-conteudo');
  if (!sel || !vazio || !pronto) return;

  function atualizar() {
    var opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
      vazio.classList.remove('d-none');
      pronto.classList.add('d-none');
      return;
    }
    document.getElementById('resumo-paciente').textContent     = opt.dataset.paciente || '';
    document.getElementById('resumo-data').textContent         = opt.dataset.data || '';
    document.getElementById('resumo-profissional').textContent = opt.dataset.profissional || '';
    document.getElementById('resumo-tipo').textContent         = opt.dataset.tipo || '';
    vazio.classList.add('d-none');
    pronto.classList.remove('d-none');
  }

  sel.addEventListener('change', atualizar);
  atualizar(); // estado inicial (consulta pré-selecionada ao vir de uma consulta)
})();
</script>
