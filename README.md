<p align="center"><a href="" target="_blank"><img src="https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer/blob/main/public/assets/img/branding/logo-text.png?raw=true" width="400" alt="Prontu IF Logo"></a></p>

<h3 align="center">Sistema de Prontuário Eletrônico para o IFNMG</h3>

<p align="center">
  <strong>TCC</strong> — Análise e Desenvolvimento de Sistemas · IFNMG
  <br/>
  Laravel 10 · PHP 8.2 · MySQL 8.0 · Materialize (PixInvent)
  <br/><br/>
  <img src="https://img.shields.io/badge/versão-v0.9.4-3DAA4A" alt="versão"/>
  <img src="https://img.shields.io/badge/testes-78%20passando-3DAA4A" alt="testes"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4" alt="PHP"/>
  <img src="https://img.shields.io/badge/Laravel-10-FF2D20" alt="Laravel"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1" alt="MySQL"/>
  <img src="https://img.shields.io/badge/LGPD-compliant-success" alt="LGPD"/>
</p>

---

## Sobre o Projeto

O **Prontu IF** é um sistema web de prontuário eletrônico desenvolvido para o setor de saúde do Instituto Federal do Norte de Minas Gerais (IFNMG). O objetivo é simples: **centralizar e organizar os registros de saúde dos alunos**, dando aos profissionais (médicos, dentistas, psicólogos, nutricionistas, fisioterapeutas, assistentes sociais) uma ferramenta prática para registrar atendimentos, consultas, prescrever medicamentos, solicitar exames e acompanhar o histórico de cada paciente.

O projeto nasceu como TCC do curso de ADS, mas foi pensado para resolver um problema real — a gestão de informações de saúde dentro de uma instituição de ensino, onde o acompanhamento dos alunos é feito por múltiplos profissionais e precisa ser rastreável, seguro e acessível.

### Por que isso importa?

- Prontuários em papel se perdem, são difíceis de consultar e não permitem gerar relatórios.
- Profissionais diferentes precisam acessar o mesmo histórico do aluno de forma integrada.
- Dados de saúde são sensíveis e exigem segurança e conformidade legal (LGPD, Lei 13.709/2018).
- O IFNMG não possuía um sistema informatizado para essa finalidade.

---

## A Jornada do Projeto

O Prontu IF não foi construído de uma vez — ele **cresceu em camadas**, e cada camada resolveu um problema real antes de partir para a próxima. Essa narrativa de evolução é parte do valor do TCC:

> **Fundação → Core Clínico → UX → Agendamentos → Segurança → LGPD → Inteligência → Personalização.**

1. **A base (v0.1–v0.2).** Primeiro, o esqueleto: autenticação com bcrypt, um schema relacional com integridade referencial, e o controle de acesso por papéis (RBAC). Sem isso, nada de saúde faria sentido.

2. **O coração clínico (v0.3–v0.4).** O ciclo completo do prontuário: abrir um **atendimento**, registrar **consultas** no formato SOAP (Queixa, Anamnese, Diagnóstico, Conduta), **prescrever** medicamentos e **solicitar exames** — tudo com controle de autoria (cada profissional só edita o que criou) e auditoria de ações.

3. **A experiência (v0.5).** Com o core pronto, veio o polimento: padrão visual em 22 telas, breadcrumbs, busca dinâmica, e a migração de especialidades e tipos de consulta para o banco — agora **gerenciáveis pelo administrador**, não mais fixos no código.

4. **O agendamento (v0.6–v0.7.5).** Um calendário FullCalendar para o profissional e um **wizard mobile-first** para o aluno marcar a própria consulta, com ciclo de status (pendente → confirmado → realizado → cancelado).

5. **A blindagem (v0.7.6–v0.7.7).** Uma sprint inteira de segurança guiada por OWASP: correção de IDOR, CSRF no logout, criptografia de sessão. E a adoção da **identidade visual verde do IFNMG**.

6. **A conformidade legal (v0.8).** O diferencial acadêmico: consentimento LGPD **bloqueante** com termos distintos para titulares e operadores, política de privacidade pública, **portabilidade de dados** (exportação em JSON), e filtragem de dados sensíveis nos logs.

7. **A inteligência (v0.9).** Cada perfil ganhou um **dashboard contextual**, com destaque para o **widget de conformidade LGPD** — métricas agregadas que demonstram prestação de contas sem expor nenhum dado pessoal. E um **onboarding de primeiro acesso** que completa o cadastro do paciente em um wizard guiado.

8. **A personalização (v0.10).** O usuário passou a gerenciar o próprio perfil (avatar, dados, senha), e uma linha de correções/melhorias com base em testes manuais começou a polir cada detalhe.

📖 **Histórico completo, versão por versão:** [`docs/HISTORICO_VERSOES.md`](docs/HISTORICO_VERSOES.md)
🗂️ **Catálogo rastreável de itens (STs, UXs, segurança, LGPD, dívidas técnicas):** [`docs/CATALOGO_ST_UX.md`](docs/CATALOGO_ST_UX.md)

---

## Módulos e Funcionalidades

### Cadastros e Acesso

| Funcionalidade | Status | Descrição |
|---|---|---|
| Usuários do sistema | ✅ | 5 níveis: admin, coordenador, profissional, recepcionista, paciente |
| Profissionais de Saúde | ✅ | Clínico geral, odontologia, psicologia, nutrição, fisioterapia, serviço social |
| Pacientes (Alunos) | ✅ | Cadastro estruturado: endereço, contatos, emergência, responsável legal, dados clínicos |
| **Perfil do usuário** (ST-10) | ✅ | Edição de nome, e-mail, senha e foto de avatar — para qualquer perfil |
| **Onboarding de 1º acesso** (ST-15) | ✅ | Wizard guiado de 7 passos com ViaCEP e responsável condicional para menores |

### Prontuário Eletrônico

| Funcionalidade | Status | Descrição |
|---|---|---|
| Módulo de Atendimentos | ✅ | Ciclo aberto → fechado; profissional vê apenas os seus |
| Registro de consultas (SOAP) | ✅ | Queixa, Anamnese, Diagnóstico, Conduta com autoria e controle de edição |
| Prescrição de medicamentos | ✅ | Medicamento, dosagem, frequência, duração |
| Solicitação e resultado de exames | ✅ | Vinculação com consulta, registro de resultado |
| Visões por especialidade (ST-12) | ✅ | View selecionada dinamicamente por especialidade |

### Agendamentos (ST-09)

| Funcionalidade | Status | Descrição |
|---|---|---|
| Calendário do profissional | ✅ | FullCalendar com cores por status (pendente/confirmado/realizado/cancelado) |
| Disponibilidade | ✅ | Grade dia × turno, exceções, preview semanal |
| Auto-agendamento do aluno | ✅ | Wizard 4 passos mobile-first com slots via AJAX |

### Dashboard e Histórico

| Funcionalidade | Status | Descrição |
|---|---|---|
| **Dashboard por papel** (ST-16) | ✅ | Painel contextual para cada um dos 5 perfis |
| **Widget de conformidade LGPD** | ✅ | 4 indicadores agregados, sem dados pessoais (prestação de contas) |
| Perfil e histórico do paciente | ✅ | Stats + mini-card de atendimentos recentes + timeline completa |
| Meu Prontuário | ✅ | Paciente visualiza o próprio histórico (somente leitura) |

### Segurança e Conformidade

| Funcionalidade | Status | Descrição |
|---|---|---|
| Autenticação + RBAC (5 níveis) | ✅ | Bcrypt, sessão criptografada, middleware por nível |
| Controle de autoria | ✅ | Profissional só edita o que criou (Policies) |
| Auditoria de ações | ✅ | Log filtrado (sem dados sensíveis), com filtros e exportação planejada |
| **Consentimento LGPD bloqueante** | ✅ | Termos distintos por perfil, recusa e revogação |
| **Portabilidade de dados** | ✅ | Exportação do próprio prontuário em JSON (Art. 18, V) |
| Rate limiting | ✅ | Proteção contra abuso em rotas AJAX e de login |
| Modal de inatividade de sessão | ✅ | Aviso em 27 min, expiração em 30 min |
| Governança / Inativação (ST-07) | 📌 | Inativação lógica de pacientes e profissionais (próximo) |

> **Legenda:** ✅ Concluído · 🔨 Em andamento · 📌 Planejado

---

## Arquitetura e Stack

```
┌─────────────────────────────────────────────────────┐
│                    FRONTEND                          │
│   Materialize (PixInvent) · Bootstrap 5 · Blade     │
│   FullCalendar · ViaCEP · fetch() AJAX               │
├─────────────────────────────────────────────────────┤
│                    BACKEND                           │
│   Laravel 10 · PHP 8.2 · Eloquent ORM              │
│   Middleware (auth · consentimento · onboarding)     │
│   Policies · FormRequests · AuditObserver · Services │
├─────────────────────────────────────────────────────┤
│                  BANCO DE DADOS                      │
│   MySQL 8.0 · Migrations · Foreign Keys · Seeders   │
├─────────────────────────────────────────────────────┤
│                   INFRAESTRUTURA                     │
│   Laravel Herd (PHP nativo) · MySQL em Docker · Git │
└─────────────────────────────────────────────────────┘
```

| Camada | Tecnologia | Papel |
|---|---|---|
| **Backend** | Laravel 10 (PHP 8.2) | MVC, rotas, controllers, autenticação, ORM |
| **Frontend** | Materialize (PixInvent) | Template admin Material Design + Bootstrap 5 |
| **Banco de Dados** | MySQL 8.0 | Armazenamento relacional com integridade referencial |
| **Ambiente** | Laravel Herd + MySQL (Docker) | PHP/nginx nativos para dev rápido; MySQL containerizado |
| **Versionamento** | Git + GitHub | `master` (estável) e `dev-stg1` (desenvolvimento) |

> **Nota de ambiente:** o projeto migrou do Docker completo para o **Laravel Herd** (PHP 8.2 nativo) na v0.8.5, mantendo apenas o MySQL em container — o que reduziu o tempo da suíte de testes em ~83%.

---

## Modelo de Dados

```
Usuário (users) ──N:N── Roles (5 níveis)
 │   └── onboarding_completo, avatar
 │
 ├── Profissional (1:1) ── especialidade, registro, contato
 │    ├── Atendimentos (1:N) ── paciente, status (aberto/fechado)
 │    │    └── Consultas (1:N) ── SOAP, tipo, criado_por_id
 │    │         ├── Prescrições (1:N) ── medicamento, dosagem
 │    │         └── Exames (1:N) ─────── tipo, resultado
 │    ├── Disponibilidade (blocos / exceções / config)
 │    └── Agendamentos (paciente, status, consulta_id)
 │
 └── Paciente (1:1) ── matrícula, curso, endereço estruturado,
      │                contatos de emergência, responsável legal, dados clínicos
      └── Consentimentos (LGPD) ── tipo_termo, versao, revogado_em

AuditLog ── user_id, action, model, model_id, old/new_values (filtrados), ip
Especialidades · Tipos de Consulta (gerenciáveis pelo admin)
```

---

## Instalação

### Ambiente recomendado (Laravel Herd + MySQL em Docker)

```bash
# 1. Clone o repositório
git clone https://github.com/ithalosena/proj-prontuario-eletronio-laravel-materializer.git
cd proj-prontuario-eletronio-laravel-materializer

# 2. Configure o .env (DB_HOST=127.0.0.1, banco MySQL)
cp .env.example .env

# 3. Dependências
composer install
npm install

# 4. Suba o MySQL (container) e gere a chave
docker-compose up -d mysql
php artisan key:generate

# 5. Migrations + dados de demonstração
php artisan migrate:fresh --seed

# 6. Symlink de storage (necessário para avatares) e assets
php artisan storage:link
npm run production

# 7. Acesse via Herd: https://proj-prontuario-eletronio-laravel-materializer.test
```

### Alternativa simples (sem Herd)

```bash
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run production
php artisan serve   # http://localhost:8000
```

> A compilação de assets (`npm run production`) pode demorar na primeira execução.

### Rodando os testes

```bash
php artisan test    # 78 testes (Feature) com SQLite in-memory
```

---

## Credenciais de Demonstração

Após `migrate:fresh --seed`, o banco é populado com 17 usuários, 55 pacientes e ~400 registros clínicos cobrindo todos os cenários de teste.

| Usuário | E-mail | Senha | Nível |
|---|---|---|---|
| Admin | admin@prontuif.com | senha123 | Administrador |
| Coordenador | coordenador@ifnmg.edu.br | senha123 | Coordenador |
| Recepcionista | recepcao@ifnmg.edu.br | senha123 | Recepcionista |
| Dr. Carlos Silva | dr.silva@ifnmg.edu.br | senha123 | Clínico Geral |
| Dra. Ana Oliveira | dra.ana@ifnmg.edu.br | senha123 | Odontologia |
| Dr. Pedro Santos | dr.pedro@ifnmg.edu.br | senha123 | Psicologia |
| Maria Fernanda Costa | maria.costa@aluno.ifnmg.edu.br | senha123 | Paciente |

---

## O que cada perfil pode fazer

| Perfil | Capacidades |
|---|---|
| **Administrador** | Visão total (somente leitura no clínico), gestão de usuários/profissionais/pacientes, auditoria, configurações, widget de conformidade LGPD |
| **Coordenador** | Dashboard gerencial (produtividade, ocupação), gestão e relatórios |
| **Profissional de Saúde** | Abrir atendimentos, registrar consultas SOAP, prescrever, solicitar exames, agenda e disponibilidade |
| **Recepcionista** | Cadastro de pacientes, agendamentos, check-ins do dia |
| **Paciente** | Auto-agendamento, "Meu Prontuário", exportar os próprios dados (LGPD) |

---

## Roadmap de Desenvolvimento

### Concluído

| Fase / Item | Versão | Entrega |
|---|---|---|
| Fundação | v0.1–v0.2 | Auth, schema, RBAC, CRUD do prontuário |
| Core clínico + UX | v0.3–v0.5 | Atendimentos, SOAP, autoria, auditoria, padrão visual, especialidades em banco |
| Agendamentos (ST-09) | v0.6–v0.7.5 | Calendário, disponibilidade, wizard do aluno |
| Segurança (S-01…S-10) | v0.7.6–v0.7.7 | Auditoria OWASP, 403 amigável, paleta IFNMG |
| LGPD (L-01…L-06) | v0.8.x | Consentimento, privacidade, portabilidade, sessão |
| Notificações + Herd | v0.8.5 | Sino in-app, migração de ambiente |
| Dashboard por papel (ST-16) | v0.9.1 | 5 painéis + widget de conformidade LGPD |
| Onboarding (ST-15) | v0.9.4 | Wizard de primeiro acesso |
| Perfil do usuário (ST-10) | v0.10.0 | Avatar, dados, senha |

### Em andamento / Próximos

| Item | Alvo | Descrição |
|---|---|---|
| Linha de correções v0.10.x | em andamento | Polimento e correções com base em testes manuais por perfil |
| Governança / Inativação (ST-07) | próximo | Inativação lógica de pacientes e profissionais |
| Importação CSV (ST-14) | próximo | Importação de pacientes em massa |
| CRUD de termos LGPD (v0.9.6) | planejado | Versionamento de termos pelo admin |
| Curso gerenciável (ST-18) | backlog | Curso como cadastro, não texto livre |
| Exportação de auditoria (ST-19) | backlog | Export de logs em CSV/PDF |

> Catálogo completo e rastreável: [`docs/CATALOGO_ST_UX.md`](docs/CATALOGO_ST_UX.md)

---

## Documentação do Projeto

Para entender não só **o que** o sistema faz, mas **como ele chegou até aqui**, dois documentos contam essa história:

| Documento | O que você encontra |
|---|---|
| 📖 [**Histórico de Versões**](docs/HISTORICO_VERSOES.md) | A linha do tempo completa, versão por versão — de fevereiro a hoje. Cada sprint, seu tema e o número de testes. É a "biografia" do sistema. |
| 🗂️ [**Catálogo de Itens de Desenvolvimento**](docs/CATALOGO_ST_UX.md) | O índice rastreável de tudo que foi planejado e construído: histórias (ST), melhorias de UX, achados de segurança (S), itens LGPD (L) e dívidas técnicas (DT). Os mesmos códigos que aparecem nos commits e comentários do código, reunidos em um só lugar. |

Durante o desenvolvimento, vários itens **não previstos originalmente** surgiram de testes manuais e auditorias — como a migração para o Herd (performance), o widget de conformidade LGPD (prestação de contas), o cache-busting de CSS e a credencial de coordenador. Eles foram incorporados à narrativa e estão devidamente catalogados.

---

## Contexto Acadêmico

Este projeto foi desenvolvido por **Ithalo Aquino** como Trabalho de Conclusão de Curso (TCC) do curso de **Análise e Desenvolvimento de Sistemas (ADS)** no **Instituto Federal do Norte de Minas Gerais (IFNMG)**.

O Prontu IF não é apenas um exercício acadêmico — foi pensado para resolver uma necessidade real do campus, contribuindo para a melhoria da gestão de saúde dos alunos. O desenvolvimento envolveu decisões fundamentadas em Engenharia de Software, Banco de Dados, Segurança da Informação e legislação (LGPD).

### Referências técnicas

- **Padrão MVC** — Separação de responsabilidades
- **OWASP Top 10** — Boas práticas de segurança web
- **LGPD (Lei 13.709/2018)** — Proteção de dados pessoais sensíveis
- **Resolução CFM 1.821/2007** — Prontuário eletrônico e prazo de guarda
- **SBIS/CFM** — Requisitos de segurança para sistemas de saúde

---

## Documentação de Referência

| Recurso | Link |
|---|---|
| Laravel 10 | [laravel.com/docs/10.x](https://laravel.com/docs/10.x) |
| Laravel Herd | [herd.laravel.com](https://herd.laravel.com/) |
| Materialize (PixInvent) | [pixinvent.com](https://pixinvent.com/materialize-material-design-admin-template/) |
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
