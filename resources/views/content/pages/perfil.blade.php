@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Meu Perfil')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',     'url' => '/'],
      ['label' => 'Meu Perfil', 'url' => null],
    ]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Meu Perfil</h4>
      <p class="text-muted small mb-0 mt-1">Gerencie suas informações pessoais e segurança da conta.</p>
    </div>
  </div>

  {{-- Flash messages --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <div class="row g-4">

    {{-- ============================================================ --}}
    {{-- COLUNA ESQUERDA — Avatar                                      --}}
    {{-- ============================================================ --}}
    <div class="col-md-4">

      {{-- Card avatar --}}
      <div class="card">
        <div class="card-body text-center py-4">
          {{-- Preview do avatar --}}
          <div class="mb-3">
            @if($user->avatar && Storage::disk('public')->exists($user->avatar))
              <img src="{{ Storage::url($user->avatar) }}"
                   alt="Avatar"
                   class="rounded-circle"
                   style="width:96px; height:96px; object-fit:cover; border:3px solid #3DAA4A;">
            @else
              <div class="avatar avatar-xl mx-auto">
                <span class="avatar-initial rounded-circle bg-label-primary fw-bold" style="font-size:2rem; width:96px; height:96px; line-height:96px; display:inline-flex; align-items:center; justify-content:center;">
                  {{ collect(explode(' ', $user->name))->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('') }}
                </span>
              </div>
            @endif
          </div>

          <h5 class="mb-0">{{ $user->name }}</h5>
          <p class="text-muted small mb-3">{{ $user->email }}</p>

          {{-- Upload de avatar --}}
          <form method="POST" action="/perfil/avatar" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div class="mb-2">
              <label for="avatar-input" class="form-label visually-hidden">Selecionar imagem</label>
              <input type="file" id="avatar-input" name="avatar"
                     class="form-control form-control-sm @error('avatar') is-invalid @enderror"
                     accept=".jpg,.jpeg,.png,.webp">
              @error('avatar')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">JPG, PNG ou WebP · máx. 2 MB</div>
            </div>
            <button type="submit" class="btn btn-sm btn-primary w-100">
              <i class="mdi mdi-camera-outline me-1"></i>Alterar foto
            </button>
          </form>

          {{-- Remover avatar --}}
          @if($user->avatar)
          <form method="POST" action="/perfil/avatar/remover" class="mt-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                    onclick="return confirm('Remover foto de perfil?')">
              <i class="mdi mdi-delete-outline me-1"></i>Remover foto
            </button>
          </form>
          @endif
        </div>
      </div>

    </div>

    {{-- ============================================================ --}}
    {{-- COLUNA DIREITA — Informações + Senha                         --}}
    {{-- ============================================================ --}}
    <div class="col-md-8 d-flex flex-column gap-4">

      {{-- Informações pessoais --}}
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="mdi mdi-account-outline text-primary"></i>
          <h5 class="card-title mb-0">Informações pessoais</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="/perfil">
            @csrf
            @method('PUT')

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="name">Nome completo</label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required>
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-content-save-outline me-1"></i>Salvar informações
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Alterar senha --}}
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="mdi mdi-lock-outline text-warning"></i>
          <h5 class="card-title mb-0">Alterar senha</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="/perfil">
            @csrf
            @method('PUT')
            {{-- Precisa reenviar nome e email para o FormRequest não reclamar --}}
            <input type="hidden" name="name"  value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label" for="current_password">Senha atual</label>
                <input type="password" id="current_password" name="current_password"
                       class="form-control @error('current_password') is-invalid @enderror"
                       autocomplete="current-password">
                @error('current_password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="new_password">Nova senha</label>
                <input type="password" id="new_password" name="new_password"
                       class="form-control @error('new_password') is-invalid @enderror"
                       autocomplete="new-password">
                @error('new_password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="new_password_confirmation">Confirmar senha</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                       class="form-control" autocomplete="new-password">
              </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-warning">
                <i class="mdi mdi-lock-reset me-1"></i>Alterar senha
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
