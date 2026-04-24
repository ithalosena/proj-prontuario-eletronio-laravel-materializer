@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Atendimentos')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ============================================================
       Cabeçalho da página
       O aviso "exibindo seus atendimentos" aparece apenas para
       profissional_saude (nivel 3), que tem visão filtrada pelo
       controller (só vê os atendimentos em que ele é responsável).
       ============================================================ --}}
  <div class="card mb-3">
    <div class="card-header header-elements">
      <h3 class="align-text-bottom-2">
        Atendimentos
        @if(Auth::user()->nivelAcesso() == 3)
          <small class="text-muted fw-normal fs-6 ms-2">— exibindo seus atendimentos</small>
        @endif
      </h3>
      <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
        <a href="/cadastro-atendimento" class="btn btn-primary">
          <i class="mdi mdi-plus-circle-outline mdi-24px me-2"></i>Novo Atendimento
        </a>
      </div>
    </div>
  </div>

  {{-- Alertas de sessão: aparecem após redirecionamento com ->with('success'/'error') --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible mb-3" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- ============================================================
       Tabela de atendimentos com paginação
       Os dados já chegam paginados do controller (paginate(15)),
       então o @forelse itera apenas sobre a página atual.
       ============================================================ --}}
  <div class="card mt-1">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Paciente</th>
              <th>Profissional</th>
              <th>Aberto por</th>
              <th>Status</th>
              <th>Data</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">

            @forelse($atendimentos as $atendimento)
            <tr>
              {{-- Paciente: nome em destaque e matrícula como subtexto --}}
              <td>
                <span class="fw-medium">{{ $atendimento->paciente->nome ?? '-' }}</span>
                @if($atendimento->paciente->matricula)
                  <br><small class="text-muted">{{ $atendimento->paciente->matricula }}</small>
                @endif
              </td>

              {{-- Profissional: nome e especialidade --}}
              <td>
                {{ $atendimento->profissional->nome ?? '-' }}
                @if($atendimento->profissional->especialidade)
                  <br><small class="text-muted">{{ $atendimento->profissional->especialidade }}</small>
                @endif
              </td>

              {{-- "Aberto por": exibe o nome de quem criou o atendimento,
                   mas só quando é diferente do próprio profissional responsável.
                   Evita exibir informação redundante quando o profissional abriu o próprio atendimento. --}}
              <td>
                @if($atendimento->criadoPor && $atendimento->criadoPor->id !== $atendimento->profissional?->user_id)
                  <span class="text-muted small">{{ $atendimento->criadoPor->name }}</span>
                @else
                  <span class="text-muted small">—</span>
                @endif
              </td>

              {{-- Status: badge verde para aberto, cinza para fechado --}}
              <td>
                @if($atendimento->status === 'aberto')
                  <span class="badge rounded-pill bg-label-success">Aberto</span>
                @else
                  <span class="badge rounded-pill bg-label-secondary">Fechado</span>
                @endif
              </td>

              {{-- Data de abertura: dia/mês/ano + hora separados para melhor leitura --}}
              <td>
                <span class="text-nowrap">{{ $atendimento->created_at->format('d/m/Y') }}</span>
                <br><small class="text-muted">{{ $atendimento->created_at->format('H:i') }}</small>
              </td>

              <td>
                <a href="/atendimentos/{{ $atendimento->id }}" class="btn btn-sm btn-outline-secondary">
                  <i class="mdi mdi-eye-outline me-1"></i>Detalhes
                </a>
              </td>
            </tr>

            {{-- Estado vazio: mostrado quando não há atendimentos no banco (ou filtro sem resultado) --}}
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-5">
                <i class="mdi mdi-folder-open-outline mdi-36px d-block mb-2 opacity-50"></i>
                Nenhum atendimento encontrado.
              </td>
            </tr>
            @endforelse

          </tbody>
        </table>
      </div>

      {{-- Links de paginação gerados automaticamente pelo Laravel com Bootstrap 5 --}}
      <div class="mt-3">
        {{ $atendimentos->links() }}
      </div>
    </div>
  </div>
</div>

@endsection
