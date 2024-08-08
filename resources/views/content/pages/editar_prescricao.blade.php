<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Atualização de Prescrição</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Atualização de Prescrição</h2>
        <form action="/atualizar-prescricao/{{ $prescricao->id }}" method="POST">
            @csrf
            @method("PUT")
            <div class="form-group">
                <label for="nome_medicamento">Nome do Medicamento</label>
                <input value="{{ $prescricao->nome_medicamento }}" name="nome_medicamento" type="text" class="form-control" id="nome_medicamento" placeholder="Digite o nome do medicamento">
            </div>
            <div class="form-group">
                <label for="dosagem">Dosagem</label>
                <input  value="{{ $prescricao->dosagem }}" name="dosagem" type="text" class="form-control" id="dosagem" placeholder="Digite a dosagem">
            </div>
            <div class="form-group">
                <label for="frequencia">Frequência</label>
                <input value="{{ $prescricao->frequencia }}" name="frequencia" type="text" class="form-control" id="frequencia" placeholder="Digite a frequência">
            </div>
            <div class="form-group">
                <label for="duracao">Duração</label>
                <input value="{{ $prescricao->duracao }}" name="duracao" type="text" class="form-control" id="duracao" placeholder="Digite a duração">
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="/prescricoes" type="button" class="btn btn-warning">Voltar</a>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
