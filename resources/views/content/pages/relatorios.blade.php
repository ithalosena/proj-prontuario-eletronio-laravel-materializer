@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Relatorios')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Relatorios</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Grafico de Exames por Tipo -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Exames por Tipo</h5>
        </div>
        <div class="card-body">
          <canvas id="examesChart" height="250"></canvas>
        </div>
      </div>
    </div>

    <!-- Grafico de Pacientes por Sexo -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Pacientes por Sexo</h5>
        </div>
        <div class="card-body">
          <canvas id="pacientesChart" height="250"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Grafico de Prescricoes por Medicamento -->
    <div class="col-md-12 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Prescricoes por Medicamento</h5>
        </div>
        <div class="card-body">
          <canvas id="prescricoesChart" height="150"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Dados de exames
    const exames = {!! json_encode($exames) !!};
    const examesLabels = [...new Set(exames.map(e => e.tipo))];
    const examesData = examesLabels.map(label => exames.filter(e => e.tipo === label).length);

    new Chart(document.getElementById('examesChart'), {
      type: 'bar',
      data: {
        labels: examesLabels,
        datasets: [{
          label: 'Quantidade',
          data: examesData,
          backgroundColor: 'rgba(105, 108, 255, 0.2)',
          borderColor: 'rgba(105, 108, 255, 1)',
          borderWidth: 1
        }]
      },
      options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // Dados de pacientes
    const pacientes = {!! json_encode($pacientes) !!};
    const sexoLabels = [...new Set(pacientes.map(p => p.sexo))];
    const sexoData = sexoLabels.map(label => pacientes.filter(p => p.sexo === label).length);

    new Chart(document.getElementById('pacientesChart'), {
      type: 'pie',
      data: {
        labels: sexoLabels.map(s => s === 'F' ? 'Feminino' : s === 'M' ? 'Masculino' : 'Outro'),
        datasets: [{
          data: sexoData,
          backgroundColor: ['rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)', 'rgba(75, 192, 192, 0.5)'],
          borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)'],
          borderWidth: 1
        }]
      }
    });

    // Dados de prescricoes
    const prescricoes = {!! json_encode($prescricoes) !!};
    const medLabels = [...new Set(prescricoes.map(p => p.nome_medicamento))];
    const medData = medLabels.map(label => prescricoes.filter(p => p.nome_medicamento === label).length);

    new Chart(document.getElementById('prescricoesChart'), {
      type: 'bar',
      data: {
        labels: medLabels,
        datasets: [{
          label: 'Quantidade',
          data: medData,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
  });
</script>
@endsection
