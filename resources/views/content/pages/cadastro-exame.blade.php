<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Cadastro</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Cadastro de Exame</h2>
        <form action="/cadastrar-exame" method="POST">
            @csrf
            <div class="form-group">
                <label for="observacao">Observação</label>
                <textarea name="observacao" class="form-control" id="observacao" placeholder="Digite a observação"></textarea>
            </div>
            <div class="form-group">
                <label for="nome_paciente">Nome do Paciente</label>
                <input name="nome_paciente" type="text" class="form-control" id="nome_paciente" placeholder="Digite o nome do paciente">
            </div>
            <div class="form-group">
                <label for="nome_profissional">Nome do Profissional</label>
                <input name="nome_profissional" type="text" class="form-control" id="nome_profissional" placeholder="Digite o nome do profissional">
            </div>
            <div class="form-group">
                <label for="data">Data</label>
                <input name="data" type="date" class="form-control" id="data">
            </div>
            <div class="form-group">
                <label for="especialidade">Especialidade</label>
                <input name="especialidade" type="text" class="form-control" id="especialidade" placeholder="Digite a especialidade">
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
            <a href="/exames" type="button" class="btn btn-warning">Voltar</a>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
