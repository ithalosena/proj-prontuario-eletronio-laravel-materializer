<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listagem de Prescricoes</title>
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h2 class="text-center">Listagem de Prescricoes</h2>
    <a href="/cadastro-prescricao" class="btn btn-primary">Adicionar Prescricao</a>
    <a href="/" type="button" class="btn btn-warning">Voltar</a>
    <table class="table table-striped mt-4">
      <thead>
        <tr>
          <th>Medicamento</th>
          <th>Dosagem</th>
          <th>Frquência</th>
          <th>Duracao</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach($prescricoes as $prescricao)
        <tr>
          <td>{{ $prescricao->nome_medicamento }}</td>
          <td>{{ $prescricao->dosagem }}</td>
          <td>{{ $prescricao->frequencia }}</td>
          <td>{{ $prescricao->duracao }}</td>
          <td>{{ $prescricao->especialidade }}</td>

          <td>
            <a href="/editar-prescricao/{{ $prescricao->id }}" class="btn btn-sm btn-warning">Editar</a>
            <form action="/deletar-prescricao/{{ $prescricao->id }}" method="GET" style="display:inline;">
              @csrf
              <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>