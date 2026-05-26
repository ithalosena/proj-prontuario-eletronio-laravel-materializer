@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="{{$containerNav}}">
    @endif

    <!--  Brand demo (display only for navbar-full and hide on below xl) -->
    @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
        <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
      </a>
      @if(isset($menuHorizontal))
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
          <i class="mdi mdi-close align-middle"></i>
        </a>
      @endif
    </div>
    @endif

    <!-- ! Not required for layout-without-menu -->
    @if(!isset($navbarHideToggle))
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
      <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
        <i class="mdi mdi-menu mdi-24px"></i>
      </a>
    </div>
    @endif

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

      @if($configData['hasCustomizer'] == true)
      <!-- Style Switcher -->
      <div class="navbar-nav align-items-center">
        <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
          <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class='mdi mdi-24px'></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-start dropdown-styles">
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                <span class="align-middle"><i class='mdi mdi-weather-sunny me-2'></i>Light</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                <span class="align-middle"><i class="mdi mdi-weather-night me-2"></i>Dark</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                <span class="align-middle"><i class="mdi mdi-monitor me-2"></i>System</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <!--/ Style Switcher -->
      @endif

      <ul class="navbar-nav flex-row align-items-center ms-auto">

        {{-- Sino de notificações com badge e dropdown (Sprint v0.8.5) --}}
        <li class="nav-item me-2 dropdown">
          <a href="/notificacoes"
             class="nav-link btn btn-text-secondary rounded-pill btn-icon position-relative"
             data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
            <i class="mdi mdi-bell-outline mdi-24px"></i>
            @if($notificacoesCount > 0)
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size:0.65rem">
                {{ $notificacoesCount > 99 ? '99+' : $notificacoesCount }}
              </span>
            @endif
          </a>
          <div class="dropdown-menu dropdown-menu-end py-0" style="min-width:320px">
            <div class="d-flex align-items-center px-3 py-2 border-bottom">
              <span class="fw-semibold me-auto">Notificações</span>
              @if($notificacoesCount > 0)
                <form method="POST" action="/notificacoes/ler-todas" class="d-inline">
                  @csrf @method('PATCH')
                  <button type="submit" class="btn btn-sm btn-text-secondary p-0 small">Marcar todas como lidas</button>
                </form>
              @endif
            </div>
            <ul class="list-group list-group-flush" style="max-height:320px;overflow-y:auto">
              @forelse($notificacoesRecentes as $n)
                <li class="list-group-item list-group-item-action px-3 py-2">
                  <div class="d-flex align-items-start gap-2">
                    <a href="{{ $n->data['url'] ?? '#' }}"
                       class="d-flex align-items-start gap-2 flex-grow-1 text-decoration-none text-body">
                      <i class="mdi {{ $n->data['icone'] }} text-{{ $n->data['cor'] }} mt-1"></i>
                      <div>
                        <div class="fw-semibold small">{{ $n->data['titulo'] }}</div>
                        <div class="text-muted small">{{ $n->data['mensagem'] }}</div>
                        <div class="text-muted" style="font-size:0.7rem">{{ $n->created_at->diffForHumans() }}</div>
                      </div>
                    </a>
                    <form method="POST" action="/notificacoes/{{ $n->id }}/ler" class="d-inline">
                      @csrf @method('PATCH')
                      <button type="submit" class="btn btn-sm btn-icon btn-text-secondary p-0" title="Marcar como lida">
                        <i class="mdi mdi-check-circle-outline mdi-18px"></i>
                      </button>
                    </form>
                  </div>
                </li>
              @empty
                <li class="list-group-item text-center text-muted small py-3">Nenhuma notificação não lida.</li>
              @endforelse
            </ul>
            <div class="border-top text-center py-2">
              <button type="button"
                      class="btn btn-sm btn-text-primary small border-0 bg-transparent"
                      data-bs-toggle="offcanvas"
                      data-bs-target="#offcanvas-notificacoes"
                      aria-controls="offcanvas-notificacoes">
                Ver todas
              </button>
            </div>
          </div>
        </li>

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
          <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="dropdown">
            {{-- Nome e badge de papel (visível só em telas xl+) --}}
            @if(Auth::check())
            @php
              $nivelNav = Auth::user()->nivelAcesso();
              [$papelLabel, $papelClass] = match($nivelNav) {
                1 => ['Administrador',         'bg-label-danger'],
                2 => ['Coordenador',           'bg-label-warning'],
                3 => ['Profissional de Saúde', 'bg-label-primary'],
                4 => ['Recepcionista',         'bg-label-info'],
                default => ['Paciente',        'bg-label-success'],
              };
            @endphp
            <div class="d-none d-xl-flex flex-column align-items-end me-2">
              <span class="fw-semibold lh-1 small">{{ Auth::user()->name }}</span>
              <span class="badge {{ $papelClass }} mt-1" style="font-size:0.65rem">{{ $papelLabel }}</span>
            </div>
            {{-- Avatar com iniciais --}}
            <div class="avatar avatar-online">
              <span class="avatar-initial rounded-circle bg-label-primary">
                {{ collect(explode(' ', Auth::user()->name))->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('') }}
              </span>
            </div>
            @endif
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="javascript:void(0);">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                      <span class="avatar-initial rounded-circle bg-label-primary">
                        @if(Auth::check())
                          {{ collect(explode(' ', Auth::user()->name))->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('') }}
                        @endif
                      </span>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <span class="fw-medium d-block">{{ Auth::check() ? Auth::user()->name : '' }}</span>
                    <small class="text-muted">{{ Auth::check() ? Auth::user()->email : '' }}</small>
                  </div>
                </div>
              </a>
            </li>
            <li><div class="dropdown-divider"></div></li>
            <li>
              {{-- S-04: logout via POST com CSRF para evitar CSRF logout attack --}}
              <form method="POST" action="/logout" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item">
                  <i class='mdi mdi-logout me-2'></i>
                  <span class="align-middle">Sair</span>
                </button>
              </form>
            </li>
          </ul>
        </li>
        <!--/ User -->
      </ul>
    </div>
    @if(!isset($navbarDetached))
  </div>
  @endif
</nav>
<!-- / Navbar -->
