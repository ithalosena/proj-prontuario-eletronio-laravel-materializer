<p align="center"><a href="" target="_blank"><img src="https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer/blob/main/public/assets/img/branding/logo-text.png?raw=true" width="400" alt="Prontu IF Logo"></a></p>

<h3 align="center">Sistema de Prontuário Eletrônico para o IFNMG</h3>

<p align="center">
  <strong>TCC</strong> — Análise e Desenvolvimento de Sistemas · IFNMG
  <br/>
  Laravel 10 · PHP 8.2 · MySQL 8.0 · Materialize (PixInvent)
  <br/><br/>
  <img src="https://img.shields.io/badge/versão-v0.4.2-blue" alt="versão"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4" alt="PHP"/>
  <img src="https://img.shields.io/badge/Laravel-10-FF2D20" alt="Laravel"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1" alt="MySQL"/>
  <img src="https://img.shields.io/badge/ambiente-Docker-2496ED" alt="Docker"/>
</p>

---

## Sobre o Projeto

O **Prontu IF** é um sistema web de prontuário eletrônico desenvolvido para o setor de saúde do Instituto Federal do Norte de Minas Gerais (IFNMG). O objetivo é simples: **centralizar e organizar os registros de saúde dos alunos**, dando aos profissionais (médicos, dentistas, psicólogos, nutricionistas, fisioterapeutas) uma ferramenta prática para registrar atendimentos, consultas, prescrever medicamentos, solicitar exames e acompanhar o histórico de cada paciente.

O projeto nasceu como TCC do curso de ADS, mas foi pensado para resolver um problema real — a gestão de informações de saúde dentro de uma instituição de ensino, onde o acompanhamento dos alunos é feito por múltiplos profissionais e precisa ser rastreável, seguro e acessível.

### Por que isso importa?

- Prontuários em papel se perdem, são difíceis de consultar e não permitem gerar relatórios
- Profissionais diferentes precisam acessar o mesmo histórico do aluno de forma integrada
- Dados de saúde são sensíveis e exigem segurança (LGPD, Lei 13.709/2018)
- O IFNMG não possuía um sistema informatizado para essa finalidade

---

## Módulos e Funcionalidades

### Cadastros

| Funcionalidade | Status | Descrição |
|---|---|---|
| Usuários do sistema | ✅ Concluído | 5 níveis de acesso: admin, coordenador, profissional, recepcionista, paciente |
| Profissionais de Saúde | ✅ Concluído | Médicos, dentistas, psicólogos, nutricionistas, fisioterapeutas |
| Pacientes (Alunos) | ✅ Concluído | Dados pessoais, matrícula, curso, contato, endereço |

### Prontuário Eletrônico

| Funcionalidade | Status | Descrição |
|---|---|---|
| Módulo de Atendimentos | ✅ Concluído | Ciclo de vida aberto → fechado; profissional vê apenas os seus |
| Registro de consultas (SOAP) | ✅ Concluído | Queixa, Anamnese, Diagnóstico, Conduta com autoria e controle de edição |
| Prescrição de medicamentos | ✅ Concluído | Medicamento, dosagem, frequência, duração |
| Solicitação e resultado de exames | ✅ Concluído | Vinculação com consulta, registro de resultado |
| Visões por especialidade | ✅ Concluído | View selecionada dinamicamente por especialidade (degradação graciosa) |
| Encaminhamentos | 📌 Planejado | Encaminhamento entre profissionais |

### Histórico e Acompanhamento

| Funcionalidade | Status | Descrição |
|---|---|---|
| Perfil do paciente | ✅ Concluído | Stats de consultas, exames e prescrições + dados pessoais |
| Histórico completo | ✅ Concluído | Timeline cronológica de todos os atendimentos e consultas |
| Histórico recente inline | ✅ Concluído | Mini-card com os 5 atendimentos anteriores dentro do contexto do atendimento atual |
| Meu Prontuário | ✅ Concluído | Paciente visualiza o próprio histórico (acesso restrito) |

### Relatórios e Dashboard

| Funcionalidade | Status | Descrição |
|---|---|---|
| Dashboard com indicadores | ✅ Concluído | Contadores reais (consultas, pacientes, profissionais) por role |
| Relatórios com filtros | 🔨 Em desenvolvimento | Filtragem por período, profissional, tipo |
| Exportação em PDF | 📌 Planejado | Geração de relatórios para impressão |

### Segurança e Controle de Acesso

| Funcionalidade | Status | Descrição |
|---|---|---|
| Autenticação segura | ✅ Concluído | Bcrypt, sessões, middleware `auth` em todas as rotas protegidas |
| RBAC — 5 níveis de acesso | ✅ Concluído | Admin → Coordenador → Profissional → Recepcionista → Paciente |
| Controle de autoria | ✅ Concluído | Profissional só edita/exclui consultas, exames e prescrições que criou |
| Auditoria de ações | ✅ Concluído | Log de criação, edição, exclusão, login e logout — acessível ao admin |
| Conformidade LGPD | 🔨 Em desenvolvimento | Acesso segmentado, rastreabilidade, inativação lógica (em andamento) |

> **Legenda:** ✅ Concluído · 🔨 Em desenvolvimento · 📌 Planejado

---

## Arquitetura e Stack

```
┌─────────────────────────────────────────────────────┐
│                    FRONTEND                          │
│   Materialize (PixInvent) · Bootstrap 5 · Blade     │
│   ApexCharts · Select2 · fetch() AJAX               │
├─────────────────────────────────────────────────────┤
│                    BACKEND                           │
│   Laravel 10 · PHP 8.2 · Eloquent ORM              │
│   Auth Guards · Middleware · FormRequests            │
│   AuditObserver · SearchService · CheckNivel         │
├─────────────────────────────────────────────────────┤
│                  BANCO DE DADOS                      │
│   MySQL 8.0 · Migrations · Foreign Keys · Seeders   │
├─────────────────────────────────────────────────────┤
│                   INFRAESTRUTURA                     │
│   Docker · Laravel Sail · Git (branching strategy)  │
└─────────────────────────────────────────────────────┘
```

| Camada | Tecnologia | Papel |
|---|---|---|
| **Backend** | Laravel 10 (PHP 8.2) | Framework MVC, rotas, controllers, autenticação, ORM |
| **Frontend** | Materialize (PixInvent) | Template admin baseado em Material Design e Bootstrap 5 |
| **Banco de Dados** | MySQL 8.0 | Armazenamento relacional com integridade referencial |
| **Ambiente** | Docker + Laravel Sail | Containerização para desenvolvimento reprodutível |
| **Versionamento** | Git + GitHub | Branch `main` (estável) e `dev-stg1` (desenvolvimento) |

---

## Modelo de Dados

```
Usuário (users) ──N:N── Roles (nivel 0–5)
 │
 ├── Profissional (1:1) ── especialidade, registro, contato
 │    └── Atendimentos (1:N) ── paciente, status (aberto/fechado), datas
 │         └── Consultas (1:N) ── SOAP, tipo, criado_por_id
 │              ├── Prescrições (1:N) ── medicamento, dosagem, frequência
 │              └── Exames (1:N) ──────── tipo, resultado, observação
 │
 └── Paciente (1:1) ── matrícula, curso, data_nascimento, endereço
      └── (consultado via atendimentos e consultas)

AuditLog ── user_id, action, model, model_id, old_values, new_values, ip
```

---

## Instalação

### Com Docker (Recomendado)

O jeito mais rápido de rodar o projeto. Você precisa ter o [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado.

```bash
# 1. Clone o repositório
git clone https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer.git
cd proj-prontuario-eletronio-laravel-materializer

# 2. Crie o arquivo .env
cp .env.example .env
# Configure: DB_HOST=mysql, DB_USERNAME=sail, DB_PASSWORD=password, DB_DATABASE=prontu_if
# Adicione: WWWUSER=1000 e WWWGROUP=1000

# 3. Instale as dependências PHP (via container temporário)
MSYS_NO_PATHCONV=1 docker run --rm -v $(pwd):/var/www/html -w /var/www/html laravelsail/php82-composer:latest composer install --ignore-platform-reqs

# 4. Suba os containers
docker-compose up -d

# 5. Gere a chave da aplicação
docker-compose exec laravel.test php artisan key:generate

# 6. Execute migrations e popule o banco com dados de demonstração
docker-compose exec laravel.test php artisan migrate:fresh --seed

# 7. Instale e compile os assets
docker-compose exec laravel.test npm install
docker-compose exec laravel.test npm run production

# 8. Acesse: http://localhost
```

> **Nota:** A compilação dos assets (`npm run production`) pode demorar ~15 minutos no Docker na primeira execução.

### Sem Docker (Manual)

Pré-requisitos: PHP >= 8.2, Composer, Node.js, npm, MySQL 8.0.

```bash
git clone https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer.git
cd proj-prontuario-eletronio-laravel-materializer
composer install
npm install
cp .env.example .env     # Configure o banco de dados
php artisan key:generate
php artisan migrate:fresh --seed
npm run production
php artisan serve         # Acesse: http://localhost:8000
```

---

## Credenciais de Demonstração

Após executar `migrate:fresh --seed`, o banco é populado com 16 usuários, 55 pacientes e ~400 registros clínicos para cobrir todos os cenários de teste.

| Usuário | E-mail | Senha | Nível |
|---|---|---|---|
| Admin | admin@prontuif.com | senha123 | Administrador |
| Recepcionista | recepcao@ifnmg.edu.br | senha123 | Recepcionista |
| Dr. Carlos Silva | dr.silva@ifnmg.edu.br | senha123 | Clínico Geral |
| Dra. Ana Oliveira | dra.ana@ifnmg.edu.br | senha123 | Odontologia |
| Dr. Pedro Santos | dr.pedro@ifnmg.edu.br | senha123 | Psicologia |
| Maria Fernanda Costa | maria.costa@aluno.ifnmg.edu.br | senha123 | Paciente |

---

## Utilização

1. Acesse `http://localhost` (Docker) ou `http://localhost:8000` (manual)
2. Faça login com as credenciais de demonstração acima
3. Navegue pelos módulos de acordo com o perfil de acesso:

| Perfil | O que pode fazer |
|---|---|
| **Administrador** | Acesso total — gerenciar usuários, profissionais, pacientes, ver logs de auditoria |
| **Recepcionista** | Cadastrar e atualizar dados de pacientes |
| **Profissional de Saúde** | Abrir atendimentos, registrar consultas com SOAP, prescrever, solicitar exames, encerrar atendimentos |
| **Paciente** | Visualizar o próprio prontuário em "Meu Prontuário" |

---

## Estrutura do Projeto

```
prontu-if/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # 9 controllers (Login, Consulta, Atendimento, Paciente...)
│   │   ├── Middleware/      # CheckNivel (RBAC por nível numérico)
│   │   └── Requests/        # 12 FormRequests com validação em português
│   ├── Models/              # Eloquent (User, Profissional, Paciente, Atendimento, Consulta...)
│   ├── Observers/           # AuditObserver (log automático em todos os models)
│   └── Services/            # SearchService (autocomplete AJAX reutilizável)
├── database/
│   ├── migrations/          # 17 migrations com FK e índices
│   └── seeders/             # 5 sub-seeders orquestrados pelo DatabaseSeeder
├── resources/views/
│   ├── content/pages/       # Todas as views Blade do sistema
│   │   └── partials/        # Partials reutilizáveis (_breadcrumb, _consulta_header)
│   └── layouts/             # Layouts base (contentNavbar, blank)
├── routes/web.php            # 35+ rotas protegidas por auth + CheckNivel
└── docs_desenvolvimento/     # Documentação técnica, roteiros de teste e roadmap
```

---

## Roadmap de Desenvolvimento

O desenvolvimento segue o [ROADMAP_MASTER.md](docs_desenvolvimento/ROADMAP_MASTER.md) com versionamento incremental.

| Fase | Status | Descrição |
|---|---|---|
| Fase 1 — Fundação | ✅ Concluída (v0.1.0) | Autenticação, schema, MVC, validação, relacionamentos |
| Fase 2 — Core Funcional | ✅ Concluída (v0.2.x) | Consultas, exames, prescrições, RBAC, Meu Prontuário |
| Fase 3 — Qualidade e Auditoria | ✅ Concluída (v0.3.x) | Audit logs, módulo de atendimentos, autoria, correções de fluxo |
| Sprint UX (v0.4.x) | ✅ Concluída (v0.4.2) | Perfil do paciente, histórico inline, listagem refatorada, seeders de teste |
| Agendamentos | 📌 Backlog (ST-09) | Calendário FullCalendar, ciclo pendente→confirmado→realizado |
| Perfil do usuário | 📌 Backlog (ST-10) | Upload de avatar, edição de dados pessoais |
| Governança / Inativação | 📌 Backlog (ST-07) | Inativação lógica de pacientes e profissionais |

---

## Contexto Acadêmico

Este projeto foi desenvolvido por **Ithalo Aquino** como Trabalho de Conclusão de Curso (TCC) do curso de **Análise e Desenvolvimento de Sistemas (ADS)** no **Instituto Federal do Norte de Minas Gerais (IFNMG)**.

O Prontu IF não é apenas um exercício acadêmico — foi pensado para resolver uma necessidade real do campus, contribuindo para a melhoria da gestão de saúde dos alunos. O desenvolvimento envolveu decisões técnicas fundamentadas em Engenharia de Software, Banco de Dados, Segurança da Informação e legislação (LGPD), documentadas na monografia que acompanha este repositório.

### Referências técnicas

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
| Materialize (PixInvent) | [pixinvent.com/materialize-material-design-admin-template](https://pixinvent.com/materialize-material-design-admin-template/) |
| Docker | [docs.docker.com](https://docs.docker.com/) |
| OWASP Top 10 | [owasp.org/Top10](https://owasp.org/Top10/) |
| LGPD | [planalto.gov.br — Lei 13.709/2018](http://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm) |

---

## Contato

### Ithalo Aquino

- Email: ithalosena@gmail.com
- GitHub: [@ithalosena](https://github.com/ithalosena)

---

## Licença

Este projeto está licenciado sob a [Licença MIT](LICENSE).
