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
        <h2 class="text-center">Cadastro de Usuário</h2>
        <form action="/cadastrar-paciente" method="POST">
            @csrf
            <div class="form-group">
                <label for="nome">Nome</label>
                <input name="nome" type="text" class="form-control" id="nome" placeholder="Digite seu nome">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input name="email" type="email" class="form-control" id="email" placeholder="Digite seu email">
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input name="senha" type="password" class="form-control" id="senha" placeholder="Digite sua senha">
            </div>
            <div class="form-group">
                <label for="contato">Contato</label>
                <input name="contato" type="text" class="form-control" id="contato" placeholder="Digite seu contato">
            </div>
            <div class="form-group">
                <label for="idade">Idade</label>
                <input name="idade" type="text" class="form-control" id="idade" placeholder="Digite sua idade">
            </div>
            <div class="form-group">
                <label for="sexo">Sexo</label>
                <input name="sexo" type="text" class="form-control" id="sexo" placeholder="Digite seu sexo">
            </div>
            <div class="form-group">
                <label for="documento">Documento</label>
                <input name="documento" type="text" class="form-control" id="documento" placeholder="Digite seu documento">
            </div>
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input name="endereco" type="text" class="form-control" id="endereco" placeholder="Digite seu endereço">
            </div>
            <div class="form-group">
                <label for="matricula">Matrícula</label>
                <input name="matricula" type="text" class="form-control" id="matricula" placeholder="Digite sua matrícula">
            </div>
            <div class="form-group">
                <label for="curso">Curso</label>
                <input name="curso" type="text" class="form-control" id="curso" placeholder="Digite seu curso">
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
            <a href="/pacientes" type="button" class="btn btn-warning">Voltar</a>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
