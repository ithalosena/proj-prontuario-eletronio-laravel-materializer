<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatórios</title>
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <div class="container mt-5">
    <h2 class="text-center">Relatórios</h2>

    <a href="/" type="button" class="btn btn-warning">Voltar</a>

    <!-- Gráfico de Exames -->
    <h3 class="mt-5">Relatórios de Exames (por especialidade)</h3>
    <canvas id="examesChart"></canvas>

    <!-- Gráfico de Pacientes -->
    <h3 class="mt-5">Relatórios de Pacientes (por sexo)</h3>
    <canvas id="pacientesChart"></canvas>

    <!-- Gráfico de Prescrições -->
    <h3 class="mt-5">Relatórios de Prescrições (por nome do medicamento)</h3>
    <canvas id="prescricoesChart"></canvas>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Dados de exames
      const exames = ($exames);
      const examesLabels = [...new Set(exames.map(exame => exame.especialidade))];
      const examesData = examesLabels.map(label => exames.filter(exame => exame.especialidade === label).length);

      // Gráfico de Exames
      new Chart(document.getElementById('examesChart'), {
        type: 'bar',
        data: {
          labels: examesLabels,
          datasets: [{
            label: 'Exames por Especialidade',
            data: examesData,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      // Dados de pacientes
      const pacientes = ($pacientes);
      const pacientesSexoLabels = [...new Set(pacientes.map(paciente => paciente.sexo))];
      const pacientesSexoData = pacientesSexoLabels.map(label => pacientes.filter(paciente => paciente.sexo === label).length);
      const pacientesIdadeData = pacientes.map(paciente => paciente.idade);
      const pacientesMatriculaData = pacientes.map(paciente => paciente.matricula);

      // Gráfico de Pacientes por Sexo
      new Chart(document.getElementById('pacientesChart'), {
        type: 'pie',
        data: {
          labels: pacientesSexoLabels,
          datasets: [{
            label: 'Pacientes por Sexo',
            data: pacientesSexoData,
            backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
            borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'],
            borderWidth: 1
          }]
        }
      });

      // Dados de prescrições
      const prescricoes = ($prescricoes);
      const prescricoesLabels = [...new Set(prescricoes.map(prescricao => prescricao.nome_medicamento))];
      const prescricoesData = prescricoesLabels.map(label => prescricoes.filter(prescricao => prescricao.nome_medicamento === label).length);

      // Gráfico de Prescrições
      new Chart(document.getElementById('prescricoesChart'), {
        type: 'bar',
        data: {
          labels: prescricoesLabels,
          datasets: [{
            label: 'Prescrições por Medicamento',
            data: prescricoesData,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    });
  </script>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>