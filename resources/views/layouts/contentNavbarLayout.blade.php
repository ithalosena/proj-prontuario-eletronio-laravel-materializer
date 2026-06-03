@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();
@endphp
@extends('layouts/commonMaster' )

@php
/* Display elements */
$contentNavbar = ($contentNavbar ?? true);
$containerNav = ($containerNav ?? 'container-xxl');
$isNavbar = ($isNavbar ?? true);
$isMenu = ($isMenu ?? true);
$isFlex = ($isFlex ?? false);
$isFooter = ($isFooter ?? true);
$customizerHidden = ($customizerHidden ?? '');

/* HTML Classes */
$navbarDetached = 'navbar-detached';
$menuFixed = (isset($configData['menuFixed']) ? $configData['menuFixed'] : '');
if(isset($navbarType)) {
  $configData['navbarType'] = $navbarType;
}
$navbarType = (isset($configData['navbarType']) ? $configData['navbarType'] : '');
$footerFixed = (isset($configData['footerFixed']) ? $configData['footerFixed'] : '');
$menuCollapsed = (isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '');

/* Content classes */
$container = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';

@endphp

@section('layoutContent')
<div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
  <div class="layout-container">

    @if ($isMenu)
    @include('layouts/sections/menu/verticalMenu')
    @endif


    <!-- Layout page -->
    <div class="layout-page">

      {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
      {{-- <x-banner /> --}}

      <!-- BEGIN: Navbar-->
      @if ($isNavbar)
      @include('layouts/sections/navbar/navbar')
      @endif
      <!-- END: Navbar-->

      {{-- Slot de breadcrumbs: cada view empurra seu breadcrumb via @push('breadcrumbs') --}}
      @stack('breadcrumbs')

      <!-- Content wrapper -->
      <div class="content-wrapper">

        <!-- Content -->
        @if ($isFlex)
        <div class="{{$container}} d-flex align-items-stretch flex-grow-1 p-0">
          @else
          <div class="{{$container}} flex-grow-1 container-p-y">
            @endif

            @yield('content')

          </div>
          <!-- / Content -->

          <!-- Footer -->
          @if ($isFooter)
          @include('layouts/sections/footer/footer')
          @endif
          <!-- / Footer -->
          <div class="content-backdrop fade"></div>
        </div>
        <!--/ Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    @if ($isMenu)
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->

  {{-- ================================================================
       MODAIS DE SESSÃO — inatividade e expiração
       Gerados apenas para usuários autenticados (dentro do layoutContent)
       ================================================================ --}}
  @auth

  {{-- Form oculto para logout via POST com CSRF (reutilizado pelo JS) --}}
  <form id="form-logout-sessao" method="POST" action="{{ url('/logout') }}" class="d-none">
    @csrf
  </form>

  {{-- Modal 1: AVISO de inatividade — aparece antes de expirar --}}
  <div class="modal fade" id="modal-sessao-aviso" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal-sessao-aviso-titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="modal-sessao-aviso-titulo">
            <i class="mdi mdi-clock-alert-outline text-warning me-2"></i>Sessão prestes a expirar
          </h5>
        </div>
        <div class="modal-body py-3">
          <p class="mb-1">Sua sessão expirará em <strong><span id="sessao-countdown"></span> segundos</strong> por inatividade.</p>
          <p class="text-muted small mb-0">Clique em <strong>Continuar</strong> para permanecer conectado.</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-sessao-sair">
            <i class="mdi mdi-logout me-1"></i>Sair agora
          </button>
          <button type="button" class="btn btn-primary btn-sm" id="btn-sessao-continuar">
            <i class="mdi mdi-refresh me-1"></i>Continuar
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal 2: SESSÃO EXPIRADA — aparece após o tempo esgotar --}}
  <div class="modal fade" id="modal-sessao-expirada" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal-sessao-expirada-titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="modal-sessao-expirada-titulo">
            <i class="mdi mdi-lock-outline text-danger me-2"></i>Sessão encerrada
          </h5>
        </div>
        <div class="modal-body py-3">
          <p class="mb-0">Sua sessão expirou por inatividade. Você será redirecionado para o login.</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-primary btn-sm" id="btn-sessao-ok">
            <i class="mdi mdi-login me-1"></i>OK
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- DOMContentLoaded garante que Bootstrap já foi carregado (scripts.blade.php vem depois no HTML) --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // ----------------------------------------------------------------
    // CONFIGURAÇÃO DE TEMPO
    // TESTE: valores pequenos para validação manual
    // PRODUÇÃO: trocar para 27 * 60 * 1000 e 30 * 60 * 1000
    // ----------------------------------------------------------------
    var AVISO_MS   = 27 * 60 * 1000;  // 27 minutos — exibe aviso
    var EXPIRA_MS  = 30 * 60 * 1000;  // 30 minutos — encerra sessão

    var ultimaAtividade = Date.now();
    var avisoAberto     = false;
    var expiradoAberto  = false;

    var modalAviso    = new bootstrap.Modal(document.getElementById('modal-sessao-aviso'));
    var modalExpirada = new bootstrap.Modal(document.getElementById('modal-sessao-expirada'));

    // Registra atividade do usuário — reseta o contador
    function registrarAtividade() {
      if (avisoAberto || expiradoAberto) return;
      ultimaAtividade = Date.now();
    }

    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function (ev) {
      document.addEventListener(ev, registrarAtividade, { passive: true });
    });

    // Atualiza o countdown no modal de aviso
    function atualizarCountdown() {
      var restante = Math.ceil((EXPIRA_MS - (Date.now() - ultimaAtividade)) / 1000);
      var el = document.getElementById('sessao-countdown');
      if (el) el.textContent = Math.max(0, restante);
    }

    // Verifica inatividade a cada segundo
    setInterval(function () {
      var inativo = Date.now() - ultimaAtividade;

      if (expiradoAberto) return;

      if (inativo >= EXPIRA_MS) {
        // Sessão expirada — fecha aviso e abre expirado
        if (avisoAberto) { modalAviso.hide(); avisoAberto = false; }
        if (!expiradoAberto) { modalExpirada.show(); expiradoAberto = true; }
        return;
      }

      if (inativo >= AVISO_MS && !avisoAberto) {
        // Aproximando do limite — exibe aviso
        modalAviso.show();
        avisoAberto = true;
      }

      if (avisoAberto) atualizarCountdown();
    }, 1000);

    // Botão "Continuar" — faz ping no servidor para renovar sessão
    document.getElementById('btn-sessao-continuar').addEventListener('click', function () {
      fetch('{{ url('/session/ping') }}', {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
      }).then(function () {
        ultimaAtividade = Date.now();
        avisoAberto = false;
        modalAviso.hide();
      }).catch(function () {
        // Se o ping falhar, sessão já expirou no servidor — redireciona
        window.location.href = '{{ url('/login') }}';
      });
    });

    // Botão "Sair agora" — submete o form de logout
    document.getElementById('btn-sessao-sair').addEventListener('click', function () {
      document.getElementById('form-logout-sessao').submit();
    });

    // Botão "OK" no modal de sessão expirada — redireciona para login
    document.getElementById('btn-sessao-ok').addEventListener('click', function () {
      window.location.href = '{{ url('/login') }}';
    });
  });
  </script>

  {{-- Offcanvas lateral de notificações --}}
  @include('content.pages.partials._offcanvas_notificacoes')

  @endauth
  @endsection
