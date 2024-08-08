<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Exames</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Listagem de Exames</h2>
        <a href="/cadastro-exame" class="btn btn-primary">Adicionar Exame</a>
        <a href="/" type="button" class="btn btn-warning">Voltar</a>
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Observação</th>
                    <th>Nome Paciente</th>
                    <th>Nome Profissional</th>
                    <th>Data</th>
                    <th>Especialidade</th>     
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($exames as $exame)
                    <tr>
                        <td>{{ $exame->observacao }}</td>
                        <td>{{ $exame->nome_paciente }}</td>
                        <td>{{ $exame->nome_profissional }}</td>
                        <td>{{ $exame->data }}</td>
                        <td>{{ $exame->especialidade }}</td>
                  
                        <td>
                            <a href="/editar-exame/{{ $exame->id }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="/deletar-exame/{{ $exame->id }}" method="GET" style="display:inline;">
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
