<p align="center"><a href="" target="_blank"><img src="https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer/blob/main/public/assets/img/branding/logo-text.png?raw=true" width="400" alt="Prontu IF Logo"></a></p>

<h3 align="center">Sistema de Prontuário Eletrônico para o IFNMG</h3>

<p align="center">
  <strong>TCC</strong> — Análise e Desenvolvimento de Sistemas · IFNMG
  <br/>
  Laravel 10 · PHP 8.2 · MySQL 8.0 · Materialize (PixInvent)
</p>

---

## Sobre o Projeto

O **Prontu IF** é um sistema web de prontuário eletrônico desenvolvido para o setor de saúde do Instituto Federal do Norte de Minas Gerais (IFNMG). O objetivo é simples e direto: **centralizar e organizar os registros de saúde dos alunos**, dando aos profissionais (médicos, dentistas, psicólogos, nutricionistas) uma ferramenta prática para registrar consultas, prescrever medicamentos, solicitar exames e acompanhar o histórico de cada paciente.

O projeto nasceu como Trabalho de Conclusão de Curso (TCC) do curso de ADS, mas foi pensado para resolver um problema real — a gestão de informações de saúde dentro de uma instituição de ensino, onde o acompanhamento dos alunos é feito por múltiplos profissionais e precisa ser rastreável, seguro e acessível.

### Por que isso importa?

- Prontuários em papel se perdem, são difíceis de consultar e não permitem gerar relatórios
- Profissionais diferentes precisam acessar o mesmo histórico do aluno de forma integrada
- Dados de saúde são sensíveis e exigem segurança (LGPD, Lei 13.709/2018)
- O IFNMG não possuía um sistema informatizado para essa finalidade

---

## Módulos e Funcionalidades

### 📋 Cadastros
| Funcionalidade | Status | Descrição |
|---|---|---|
| Usuários do sistema | ✅ Concluído | Administradores e profissionais de saúde com perfis distintos |
| Profissionais de Saúde | ✅ Concluído | Médicos, dentistas, psicólogos, nutricionistas, fisioterapeutas |
| Pacientes (Alunos) | ✅ Concluído | Dados pessoais, matrícula, curso, contato |

### 🩺 Prontuário Eletrônico
| Funcionalidade | Status | Descrição |
|---|---|---|
| Registro de consultas | 🔨 Em desenvolvimento | Módulo central: anamnese, diagnóstico, conduta |
| Prescrição de medicamentos | ✅ Concluído | Medicamento, dosagem, frequência, duração |
| Solicitação de exames | 🔨 Em desenvolvimento | Vinculação com consulta, registro de resultados |
| Encaminhamentos | 📌 Planejado | Encaminhamento entre profissionais |

### 📂 Histórico e Acompanhamento
| Funcionalidade | Status | Descrição |
|---|---|---|
| Histórico do paciente | 🔨 Em desenvolvimento | Timeline cronológica de todos os atendimentos |
| Detalhes da consulta | 🔨 Em desenvolvimento | Visualização completa com prescrições e exames associados |

### 📊 Relatórios e Dashboard
| Funcionalidade | Status | Descrição |
|---|---|---|
| Dashboard com indicadores | 🔨 Em desenvolvimento | Gráficos de atendimentos, distribuição por especialidade |
| Relatórios com filtros | 🔨 Em desenvolvimento | Filtros por período, profissional, tipo de atendimento |
| Exportação em PDF | 📌 Planejado | Geração de relatórios para impressão |

### 🔐 Segurança e Controle de Acesso
| Funcionalidade | Status | Descrição |
|---|---|---|
| Autenticação segura | 🔨 Em desenvolvimento | Login com bcrypt, sessões, middleware de proteção |
| Controle de acesso (RBAC) | 🔨 Em desenvolvimento | Perfis: administrador e profissional, com permissões distintas |
| Auditoria de ações | 📌 Planejado | Log de quem criou, editou ou excluiu cada registro |
| Conformidade LGPD | 🔨 Em desenvolvimento | Criptografia, controle de acesso, rastreabilidade |

> **Legenda:** ✅ Concluído · 🔨 Em desenvolvimento · 📌 Planejado

---

## Arquitetura e Stack

```
┌─────────────────────────────────────────────────────┐
│                    FRONTEND                          │
│   Materialize (PixInvent) · Bootstrap 5 · Blade     │
│   ApexCharts · DataTables · Select2 · SweetAlert2   │
├─────────────────────────────────────────────────────┤
│                    BACKEND                           │
│   Laravel 10 · PHP 8.2 · Eloquent ORM              │
│   Auth Guards · Middleware · FormRequests · Policies │
├─────────────────────────────────────────────────────┤
│                  BANCO DE DADOS                      │
│   MySQL 8.0 · Migrations · Foreign Keys · Seeders   │
├─────────────────────────────────────────────────────┤
│                   INFRAESTRUTURA                     │
│   Docker · Laravel Sail · Git (branching strategy)   │
└─────────────────────────────────────────────────────┘
```

| Camada | Tecnologia | Papel |
|---|---|---|
| **Backend** | Laravel 10 (PHP 8.2) | Framework MVC, rotas, controllers, autenticação, ORM |
| **Frontend** | Materialize (PixInvent) | Template admin baseado em Material Design e Bootstrap 5 |
| **Banco de Dados** | MySQL 8.0 | Armazenamento relacional com integridade referencial |
| **Ambiente** | Docker + Laravel Sail | Containerização para desenvolvimento reprodutível |
| **Versionamento** | Git + GitHub | Branches `master` (estável) e `dev-stg1` (desenvolvimento) |

---

## Modelo de Dados (Simplificado)

```
Usuário (users)
 ├── Profissional (1:1) ─── especialidade, contato, registro
 │    └── Consultas (1:N) ── data, queixa, diagnóstico, conduta
 │         ├── Prescrições (1:N) ── medicamento, dosagem, frequência
 │         └── Exames (1:N) ─────── tipo, resultado, observação
 │
 └── Paciente ── nome, matrícula, curso, data_nascimento
      └── Consultas (1:N) ── histórico completo de atendimentos
```

---

## Instalação

### Com Docker (Recomendado)

O jeito mais rápido de rodar o projeto. Você só precisa ter o [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado.

```bash
# 1. Clone o repositório
git clone https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer.git
cd proj-prontuario-eletronio-laravel-materializer

# 2. Crie o arquivo .env
cp .env.example .env
# Configure DB_HOST=mysql, DB_USERNAME=sail, DB_PASSWORD=password, DB_DATABASE=prontu_if
# Adicione: WWWUSER=1000 e WWWGROUP=1000

# 3. Instale as dependências PHP (via container temporário)
docker run --rm -v $(pwd):/var/www/html -w /var/www/html laravelsail/php82-composer:latest composer install --ignore-platform-reqs

# 4. Suba os containers
docker-compose up -d

# 5. Gere a chave da aplicação
docker-compose exec laravel.test php artisan key:generate

# 6. Instale as dependências do frontend
docker-compose exec laravel.test npm install

# 7. Execute as migrations
docker-compose exec laravel.test php artisan migrate

# 8. Compile os assets (demora ~15 minutos)
docker-compose exec laravel.test npm run production

# 9. Acesse: http://localhost
```

> **Nota para Windows (Git Bash):** Use `MSYS_NO_PATHCONV=1` antes do comando `docker run` no passo 3 para evitar conversão de paths.

### Sem Docker (Manual)

Pré-requisitos: PHP >= 8.1, Composer, Node.js, npm, MySQL.

```bash
git clone https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer.git
cd proj-prontuario-eletronio-laravel-materializer
composer install
npm install
cp .env.example .env     # Configure o banco de dados
php artisan key:generate
php artisan migrate
npm run production
php artisan serve         # Acesse: http://localhost:8000
```

---

## Roadmap de Desenvolvimento

O desenvolvimento do Prontu IF segue um plano incremental em 4 fases, priorizando o que é crítico para a segurança e integridade do sistema antes de expandir funcionalidades.

### Fase 1: Fundação 🏗️
> Corrigir a base técnica do projeto

- [x] Configuração do ambiente Docker (Sail)
- [ ] Autenticação segura com Auth facade do Laravel (bcrypt + sessões)
- [ ] Proteção de rotas com middleware
- [ ] Redesenho do banco de dados com foreign keys e tipos corretos
- [ ] Refatoração: lógica de rotas movida para Resource Controllers
- [ ] Validação de dados com FormRequests
- [ ] Definição de relacionamentos Eloquent nos Models

### Fase 2: Funcionalidades Core 🩺
> Implementar o coração do prontuário

- [ ] Módulo de Consultas (atendimentos) — entidade central
- [ ] Vinculação de prescrições e exames a consultas
- [ ] Controle de acesso por perfil (RBAC com Gates/Policies)

### Fase 3: Complementos 📊
> Funcionalidades que agregam valor

- [ ] Histórico completo do paciente (timeline)
- [ ] Dashboard com gráficos e indicadores (ApexCharts)
- [ ] Relatórios com filtros e exportação em PDF
- [ ] Auditoria de ações (log de quem fez o quê)

### Fase 4: Polimento ✨
> Qualidade e apresentação

- [ ] Testes automatizados (PHPUnit — Feature e Unit)
- [ ] Seeders e Factories para dados de demonstração
- [ ] Mensagens de feedback (sucesso, erro, validação)
- [ ] Revisão de responsividade e consistência visual

---

## Utilização

1. Acesse `http://localhost` (Docker) ou `http://localhost:8000` (manual)
2. Faça login com suas credenciais
3. Navegue pelos módulos de acordo com seu perfil de acesso:
   - **Administrador:** acesso total — gerenciar usuários, profissionais, relatórios e logs
   - **Profissional de Saúde:** registrar consultas, prescrever medicamentos, solicitar exames, consultar histórico

---

## Estrutura do Projeto

```
prontu-if/
├── app/
│   ├── Http/Controllers/    # Controllers (lógica de cada módulo)
│   ├── Models/              # Models Eloquent (Paciente, Profissional, Consulta...)
│   └── Helpers/             # Helpers do template Materialize
├── database/
│   ├── migrations/          # Versionamento do schema do banco
│   ├── factories/           # Factories para testes
│   └── seeders/             # Dados iniciais
├── resources/
│   ├── views/               # Templates Blade
│   └── menu/                # Configuração do menu lateral (JSON)
├── routes/
│   └── web.php              # Definição de rotas da aplicação
├── docker/                  # Configuração Docker (PHP 8.2)
├── docker-compose.yml       # Orquestração dos containers
└── .env                     # Variáveis de ambiente (não versionado)
```

---

## Contexto Acadêmico

Este projeto foi desenvolvido por **Ithalo Sena** e **Raissa Alves** como Trabalho de Conclusão de Curso (TCC) do curso de **Análise e Desenvolvimento de Sistemas (ADS)** no **Instituto Federal do Norte de Minas Gerais (IFNMG)**.

O Prontu IF não é apenas um exercício acadêmico — foi pensado para resolver uma necessidade real do campus, contribuindo para a melhoria da gestão de saúde dos alunos. O desenvolvimento envolveu decisões técnicas fundamentadas em conceitos de Engenharia de Software, Banco de Dados, Segurança da Informação e legislação (LGPD), documentadas na monografia que acompanha este repositório.

### Referências técnicas que guiam o projeto
- **Padrão MVC** — Separação de responsabilidades (Gamma et al., 1994)
- **OWASP Top 10** — Boas práticas de segurança web
- **LGPD (Lei 13.709/2018)** — Proteção de dados pessoais sensíveis
- **Resolução CFM 1.638/2002** — Definição de prontuário médico
- **SBIS/CFM** — Requisitos de segurança para sistemas de saúde

---

## Documentação de Referência

| Recurso | Link |
|---|---|
| Laravel 10 | [laravel.com/docs/10.x](https://laravel.com/docs/10.x) |
| Materialize (PixInvent) | [pixinvent.com/materialize-material-design-bootstrap-admin-template](https://pixinvent.com/materialize-material-design-bootstrap-admin-template/) |
| Docker | [docs.docker.com](https://docs.docker.com/) |
| OWASP Top 10 | [owasp.org/Top10](https://owasp.org/Top10/) |
| LGPD | [planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm](http://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm) |

---

## Contribuição

Contribuições são bem-vindas! Se quiser sugerir melhorias ou reportar bugs:

1. Abra uma [issue](https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer/issues)
2. Ou envie um pull request

---

## Contato

**Ithalo Silva Sena Aquino**
- Email: ithalosena@gmail.com
- GitHub: [@ithalosena](https://github.com/ithalosena)

**Raissa Alves**

---

## Licença

Este projeto está licenciado sob a [Licença MIT](LICENSE).
