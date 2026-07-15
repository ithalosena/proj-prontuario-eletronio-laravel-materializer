@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Importar Pacientes')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',              'url' => '/'],
      ['label' => 'Configurações',       'url' => null],
      ['label' => 'Importar Pacientes',  'url' => null],
    ]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-0">Importar Pacientes</h4>
      <p class="text-muted small mb-0 mt-1">Cadastro em lote via arquivo CSV (ST-14)</p>
    </div>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible mb-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible mb-3" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row">
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Selecionar arquivo</h5></div>
        <div class="card-body">
          <p class="text-muted small">
            O arquivo deve ser <strong>CSV</strong> com cabeçalho e as colunas:
            <code>nome, matricula, curso, email, data_nascimento, sexo, documento</code>.
            Linhas com matrícula, documento ou e-mail já cadastrados são ignoradas; linhas com dados
            inválidos são reportadas como erro — o restante do arquivo continua sendo processado.
          </p>

          <a href="{{ asset('exemplos/pacientes_exemplo.csv') }}" download class="d-inline-flex align-items-center mb-3 small">
            <i class="mdi mdi-file-download-outline me-1"></i> Baixar CSV de exemplo
          </a>

          <form action="/pacientes/importar" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="arquivo" class="form-label">Arquivo CSV</label>
              <input type="file" name="arquivo" id="arquivo" accept=".csv,.txt"
                     class="form-control @error('arquivo') is-invalid @enderror" required>
              @error('arquivo')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="mdi mdi-upload me-1"></i> Importar
            </button>
          </form>
        </div>
      </div>
    </div>

    @if(isset($resultado))
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Resultado da importação</h5></div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <span class="badge bg-label-success">{{ $resultado['criados'] }} criados</span>
            <span class="badge bg-label-warning">{{ $resultado['ignorados'] }} ignorados</span>
            <span class="badge bg-label-danger">{{ $resultado['erros'] }} com erro</span>
          </div>

          @if(count($resultado['detalhes']))
          <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Linha</th>
                  <th>Identificador</th>
                  <th>Status</th>
                  <th>Motivo</th>
                </tr>
              </thead>
              <tbody>
                @foreach($resultado['detalhes'] as $d)
                <tr>
                  <td>{{ $d['linha'] }}</td>
                  <td>{{ $d['identificador'] }}</td>
                  <td>
                    @if($d['status'] === 'criado')
                      <span class="badge bg-label-success">Criado</span>
                    @elseif($d['status'] === 'ignorado')
                      <span class="badge bg-label-warning">Ignorado</span>
                    @else
                      <span class="badge bg-label-danger">Erro</span>
                    @endif
                  </td>
                  <td class="small text-muted">{{ $d['motivo'] ?? '—' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif
  </div>

</div>
@endsection
