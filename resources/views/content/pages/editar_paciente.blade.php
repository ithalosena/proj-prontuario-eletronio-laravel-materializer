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
        <h2 class="text-center">Edição de Usuário</h2>
        <form action="/atualizar-paciente/{{ $paciente->id }}" method="POST">
            @csrf
            @method("PUT")
            <div class="form-group">
                <label for="nome">Nome</label>
                <input value="{{ $paciente->nome }}"name="nome" type="text" class="form-control" id="nome" placeholder="Digite seu nome">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input value="{{ $paciente->email }}" name="email" type="email" class="form-control" id="email" placeholder="Digite seu email">
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input value="{{ $paciente->senha }}" name="senha" type="password" class="form-control" id="senha" placeholder="Digite sua senha">
            </div>
            <div class="form-group">
                <label for="contato">Contato</label>
                <input value="{{ $paciente->contato }}" name="contato" type="text" class="form-control" id="contato" placeholder="Digite seu contato">
            </div>
            <div class="form-group">
                <label for="idade">Idade</label>
                <input value="{{ $paciente->idade }}" name="idade" type="text" class="form-control" id="idade" placeholder="Digite sua idade">
            </div>
            <div class="form-group">
                <label for="sexo">Sexo</label>
                <input value="{{ $paciente->sexo }}" name="sexo" type="text" class="form-control" id="sexo" placeholder="Digite seu sexo">
            </div>
            <div class="form-group">
                <label for="documento">Documento</label>
                <input value="{{ $paciente->documento }}" name="documento" type="text" class="form-control" id="documento" placeholder="Digite seu documento">
            </div>
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input value="{{ $paciente->endereco }}" name="endereco" type="text" class="form-control" id="endereco" placeholder="Digite seu endereço">
            </div>
            <div class="form-group">
                <label for="matricula">Matrícula</label>
                <input value="{{ $paciente->matricula }}" name="matricula" type="text" class="form-control" id="matricula" placeholder="Digite sua matrícula">
            </div>
            <div class="form-group">
                <label for="curso">Curso</label>
                <input value="{{ $paciente->curso }}" name="curso" type="text" class="form-control" id="curso" placeholder="Digite seu curso">
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="/pacientes" type="button" class="btn btn-warning">Voltar</a>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
