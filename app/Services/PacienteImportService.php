<?php

namespace App\Services;

use App\Models\User;
use App\Models\Paciente;
use App\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/*
 * PacienteImportService — ST-14: importação de pacientes via CSV.
 *
 * Processa linha a linha (não aborta no 1º erro); duplicata de matrícula/documento/e-mail
 * é ignorada; erros de validação por linha são coletados no relatório final. Senha gerada
 * aleatoriamente e NUNCA exposta no retorno (LGPD); paciente completa acesso via onboarding.
 */
class PacienteImportService
{
    /**
     * Importa pacientes de um arquivo CSV.
     *
     * @param UploadedFile $file
     * @return array
     * @throws InvalidArgumentException|RuntimeException
     */
    public function importar(UploadedFile $file): array
    {
        $filePath = $file->getRealPath();
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("O arquivo de importação não existe ou não pode ser lido.");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new RuntimeException("Não foi possível abrir o arquivo para importação.");
        }

        $criados = 0;
        $ignorados = 0;
        $erros = 0;
        $detalhes = [];
        $lineCount = 1;

        try {
            // Ler a primeira linha (cabeçalho)
            $header = fgetcsv($handle, 0, ',');
            if ($header === false) {
                throw new InvalidArgumentException("O arquivo CSV está vazio ou é inválido.");
            }

            // Tratar BOM de UTF-8 no início do arquivo se houver (comum em Excel)
            if (isset($header[0]) && strpos($header[0], "\xEF\xBB\xBF") === 0) {
                $header[0] = substr($header[0], 3);
            }

            // Limpar espaços em branco dos nomes das colunas
            $header = array_map('trim', $header);

            // Colunas obrigatórias conforme especificação
            $requiredColumns = ['nome', 'matricula', 'curso', 'email', 'data_nascimento', 'sexo', 'documento'];
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $header)) {
                    throw new InvalidArgumentException("Coluna obrigatória ausente no cabeçalho: {$col}");
                }
            }

            // Mapear índices para suportar qualquer ordem de colunas
            $indices = [];
            foreach ($requiredColumns as $col) {
                $indices[$col] = array_search($col, $header);
            }

            // Buscar a Role de Paciente para associar aos usuários
            $rolePaciente = Role::where('slug', 'paciente')->first();
            if (!$rolePaciente) {
                throw new RuntimeException("Perfil (Role) de 'paciente' não encontrado no sistema.");
            }
            $rolePacienteId = $rolePaciente->id;

            // Processamento linha a linha
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $lineCount++;

                // Pular linhas vazias
                if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) {
                    continue;
                }

                // Extração segura dos campos
                $nome = isset($row[$indices['nome']]) ? trim($row[$indices['nome']]) : '';
                $matricula = isset($row[$indices['matricula']]) ? trim($row[$indices['matricula']]) : '';
                $curso = isset($row[$indices['curso']]) ? trim($row[$indices['curso']]) : '';
                $email = isset($row[$indices['email']]) ? trim($row[$indices['email']]) : '';
                $dataNascimentoRaw = isset($row[$indices['data_nascimento']]) ? trim($row[$indices['data_nascimento']]) : '';
                $sexo = isset($row[$indices['sexo']]) ? trim($row[$indices['sexo']]) : '';
                $documento = isset($row[$indices['documento']]) ? trim($row[$indices['documento']]) : '';

                // Identificador para o log de retorno
                $identificador = !empty($email) ? $email : (!empty($nome) ? $nome : "Linha {$lineCount}");

                try {
                    // 1. Validação dos dados da linha
                    $errosValidacao = [];

                    if ($nome === '') $errosValidacao[] = "O campo 'nome' é obrigatório.";
                    if ($matricula === '') $errosValidacao[] = "O campo 'matricula' é obrigatório.";
                    if ($curso === '') $errosValidacao[] = "O campo 'curso' é obrigatório.";
                    
                    if ($email === '') {
                        $errosValidacao[] = "O campo 'email' é obrigatório.";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errosValidacao[] = "O e-mail informado possui formato inválido.";
                    }

                    if ($documento === '') $errosValidacao[] = "O campo 'documento' é obrigatório.";

                    if (!in_array($sexo, ['M', 'F', 'outro'])) {
                        $errosValidacao[] = "O campo 'sexo' deve ser 'M', 'F' ou 'outro'.";
                    }

                    $dataNascimento = null;
                    if ($dataNascimentoRaw === '') {
                        $errosValidacao[] = "O campo 'data_nascimento' é obrigatório.";
                    } else {
                        try {
                            $dataNascimento = Carbon::parse($dataNascimentoRaw);
                            if ($dataNascimento->isToday() || $dataNascimento->isFuture()) {
                                $errosValidacao[] = "A data de nascimento deve ser anterior a hoje.";
                            }
                        } catch (Exception $e) {
                            $errosValidacao[] = "A data de nascimento informada é inválida.";
                        }
                    }

                    if (count($errosValidacao) > 0) {
                        $erros++;
                        $detalhes[] = [
                            'linha' => $lineCount,
                            'status' => 'erro',
                            'motivo' => implode(' ', $errosValidacao),
                            'identificador' => $identificador,
                        ];
                        continue;
                    }

                    // 2. Verificação de duplicados (Matrícula, Documento ou E-mail)
                    $pacienteDuplicado = Paciente::where('matricula', $matricula)
                        ->orWhere('documento', $documento)
                        ->exists();

                    $userDuplicado = User::where('email', $email)->exists();

                    if ($pacienteDuplicado || $userDuplicado) {
                        $ignorados++;
                        $detalhes[] = [
                            'linha' => $lineCount,
                            'status' => 'ignorado',
                            'motivo' => 'Paciente com esta matrícula ou documento, ou usuário com este e-mail já cadastrado.',
                            'identificador' => $identificador,
                        ];
                        continue;
                    }

                    // 3. Criação dos registros (transação por linha para robustez)
                    DB::transaction(function () use ($nome, $email, $rolePacienteId, $matricula, $curso, $documento, $dataNascimento, $sexo) {
                        $user = User::create([
                            'name'     => $nome,
                            'email'    => $email,
                            'password' => Str::random(16),
                        ]);

                        $user->roles()->syncWithoutDetaching([$rolePacienteId]);

                        $user->update(['onboarding_completo' => false]);

                        Paciente::create([
                            'user_id'         => $user->id,
                            'nome'            => $nome,
                            'documento'       => $documento,
                            'data_nascimento' => $dataNascimento,
                            'sexo'            => $sexo,
                            'matricula'       => $matricula,
                            'curso'           => $curso,
                        ]);
                    });

                    $criados++;
                    $detalhes[] = [
                        'linha' => $lineCount,
                        'status' => 'criado',
                        'motivo' => null,
                        'identificador' => $identificador,
                    ];

                } catch (Exception $lineException) {
                    $erros++;
                    $detalhes[] = [
                        'linha' => $lineCount,
                        'status' => 'erro',
                        'motivo' => 'Erro interno ao processar a linha: ' . $lineException->getMessage(),
                        'identificador' => $identificador,
                    ];
                }
            }

        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        return [
            'criados' => $criados,
            'ignorados' => $ignorados,
            'erros' => $erros,
            'detalhes' => $detalhes,
        ];
    }
}
