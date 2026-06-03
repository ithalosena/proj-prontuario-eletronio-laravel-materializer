@php
$containerFooter = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column">
      <div class="mb-2 mb-md-0">
        © <script>document.write(new Date().getFullYear())</script>
        <strong>Prontu IF</strong> — Sistema de Prontuário Eletrônico · IFNMG
      </div>
      {{-- Links institucionais: Política de Privacidade obrigatório por LGPD Art. 9º --}}
      <div class="d-none d-lg-inline-block">
        <a href="{{ url('/privacidade') }}" class="footer-link me-4">
          <i class="mdi mdi-shield-check-outline me-1"></i>Política de Privacidade
        </a>
        <a href="mailto:saude@ifnmg.edu.br" class="footer-link d-none d-sm-inline-block">
          <i class="mdi mdi-email-outline me-1"></i>Contato
        </a>
      </div>
    </div>
  </div>
</footer>
<!--/ Footer-->
