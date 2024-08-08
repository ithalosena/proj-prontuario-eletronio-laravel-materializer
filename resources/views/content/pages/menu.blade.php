<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de CRUDs</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Gerenciamento de Dados</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <a href="/profissionais" type="button" class="btn btn-primary btn-block">CRUD Profissional</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="/pacientes" type="button" class="btn btn-primary btn-block">CRUD Paciente</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="/exames" type="button" class="btn btn-primary btn-block">CRUD Exames</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="/prescricoes" type="button" class="btn btn-primary btn-block">CRUD Prescrição</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="relatorios" type="button" class="btn btn-primary btn-block">Gerar Relatórios</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="/logout" class="btn btn-danger btn-block">Logout</a>
            </div>
            
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
