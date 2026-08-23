# Histórico de Versões — Prontu IF

> Histórico interno do sistema, da fundação até a versão atual.
> Cada versão é uma sprint fechada, com validação manual e suíte de testes verde.
> Documento mantido em paralelo ao changelog técnico detalhado (uso interno).

**Convenção de versionamento**
- `v0.X.Y` — sub-versões/sprints menores (correções e melhorias pontuais) → entram por **commit**.
- `v0.X` — versões completas → entram por **Pull Request** para a `master`, com atualização de README e desta documentação.

---

## Linha do tempo

| Versão | Data | Tema | Testes |
|---|---|---|---|
| **v0.11.x** | jul/2026 | Refino do fluxo agendamento↔atendimento (em andamento) + import CSV (ST-14) + export auditoria (ST-19) | 136 |
| v0.11.0 | 14/07/2026 | **DT-MOD-01** — Modelo A: elo estrutural agendamento↔atendimento↔consulta | 111 |
| v0.10.6 | 12/07/2026 | Dados clínicos sensíveis fora do log de auditoria (LGPD) | 98 |
| v0.10.3–v0.10.5 | jun–jul/2026 | Linha completa do Paciente: perfil, onboarding reordenado, Meu Prontuário, export LGPD | 96→97 |
| v0.10.2 | 06/06/2026 | Correções e UX do perfil Profissional | 93 |
| v0.10.1 | 31/05/2026 | Correções e UX do perfil Admin | 80 |
| v0.10.0 | 29/05/2026 | **ST-10** — Perfil do usuário (avatar, dados, senha) | 70 |
| v0.9.4 | 30/05/2026 | **ST-15** — Onboarding de primeiro acesso (wizard 7 passos) | 78 |
| v0.9.1a/b | 29/05/2026 | **ST-16** — Dashboard por papel (5 perfis) + widget de conformidade LGPD | 61 |
| v0.9.0 | 27/05/2026 | Rate limit em rotas AJAX (S-06) | 55 |
| v0.8.5 (+perf) | 25–26/05/2026 | Notificações in-app + migração de ambiente (Docker → Herd, −83% no tempo de testes) | 55 |
| v0.8.3 | 24/05/2026 | Modal de inatividade de sessão (27/30 min) | 51 |
| v0.8.2 | 23/05/2026 | Consentimento LGPD bloqueante para todos os perfis + Cache-Control | 51 |
| v0.8.1 | 22/05/2026 | Sprint LGPD: consentimento, privacidade, portabilidade, filtro de auditoria | 45 |
| v0.7.7 | 22/05/2026 | Página de erro 403 amigável + paleta de cores da logo (verde IFNMG) | 34 |
| v0.7.6 | 20/05/2026 | Sprint de Segurança: 13 itens (IDOR, CSRF, sessão, índices, locale) | 34 |
| v0.7.5 | 10/05/2026 | Redesign da página de disponibilidade (grade dia × turno) | 29 |
| v0.7.0 | 06/05/2026 | Correções visuais + autocomplete de paciente unificado | 29 |
| v0.6.0 | 03/05/2026 | **ST-09** — Módulo de Agendamentos (FullCalendar + wizard do aluno) | 29 |
| v0.5.x | 01–02/05/2026 | Padrão visual em 22 telas, especialidades em banco, RBAC com Policies | 34 |
| v0.4.x | abr/2026 | Sprint UX: perfil do paciente, histórico contextual, listagens refatoradas | — |
| v0.3.x | abr/2026 | Auditoria, módulo de atendimentos, autoria, paginação | — |
| v0.1–v0.2 | fev/2026 | Fundação: autenticação, schema relacional, RBAC, CRUD do prontuário | — |

---

## Detalhamento por versão

### v0.11.x — Fluxo agendamento↔atendimento + itens complementares
Depois de fechar o Modelo A (v0.11.0), a linha v0.11.x segue com o **refino do fluxo** entre agendamento, atendimento e consulta (em andamento). Em paralelo, chegaram dois itens antes represados no backlog: a **importação de pacientes via CSV** (ST-14 — upload, validação linha a linha sem abortar no erro, duplicata por matrícula/documento/e-mail ignorada) e a **exportação dos registros de auditoria em CSV** (ST-19 — respeita os mesmos filtros da listagem, streaming em lote para não estourar memória). Também foi corrigido um erro de console (idioma do painel de customização do template, sem relação com o idioma do sistema) e revisada uma dívida técnica de migrations (confirmada como já correta, sem ação necessária).

### v0.11.0 — DT-MOD-01: Modelo A
O agendamento e o atendimento sempre existiram como tabelas separadas, mas o elo entre eles nunca tinha sido de fato costurado na aplicação — nenhuma tela levava a informação de um para o outro. O Modelo A resolve isso: o atendimento nasce automaticamente ao confirmar o primeiro registro (consulta) de um agendamento, numa única transação, ou existe avulso (paciente chegou sem agendamento prévio — atendimento espontâneo). As telas de atendimento passaram a exibir a origem ("Agendado" ou "Espontâneo") com um badge.

### v0.10.1–v0.10.6 — Correções e UX por perfil
Uma linha inteira dedicada a **polir o que já existe**, com uma sprint por perfil, cada uma nascida de uma rodada de teste manual. A **v0.10.1** (Admin) corrigiu o erro 419 (CSRF), a legibilidade da paginação, a paleta verde consistente (hover/menu) com cache-busting de CSS, trouxe página 404 personalizada, item "Configurações" no menu, e reestruturou a edição de paciente com os campos de onboarding. A **v0.10.2** (Profissional) ajustou o controle de autoria em exame/prescrição e documentou duas limitações da fonte de ícones do template. As **v0.10.3–v0.10.5** (Paciente, três rodadas de teste) entregaram o perfil como central da conta, o onboarding reordenado e mobile-first, a aba "Meu Prontuário" com resumo de saúde, e a exportação LGPD completa. A **v0.10.6** fechou o achado de segurança mais urgente da auditoria: dados clínicos sensíveis deixaram de ser gravados no log de auditoria (LGPD, Art. 11).

### v0.10.0 — Perfil do Usuário (ST-10)
Qualquer usuário passa a editar os próprios dados: nome, e-mail, senha (com verificação da senha atual) e foto de avatar (upload/remoção). A navbar exibe a foto quando existe, com fallback para as iniciais.

### v0.9.4 — Onboarding de Primeiro Acesso (ST-15)
Um terceiro passo obrigatório no primeiro login (depois de autenticação e consentimento): um **wizard em tela cheia**. Para o paciente, 7 passos com integração ViaCEP não-bloqueante, contatos de emergência e bloco de responsável legal que aparece automaticamente para menores de 18. Para operadores, um wizard mínimo de confirmação. Trouxe 30 novos campos estruturados ao cadastro do paciente.

### v0.9.1a/b — Dashboard por Papel (ST-16)
A tela inicial deixou de ser genérica: cada um dos 5 perfis recebe um painel contextual. O destaque é o **widget de Conformidade LGPD** no painel do administrador — quatro indicadores agregados (sem expor dados pessoais) que demonstram prestação de contas.

### v0.9.0 — Rate Limit AJAX (S-06)
Último item da auditoria de segurança: limite de 60 requisições por minuto nas rotas de busca e de calendário, protegendo contra scraping.

### v0.8.5 — Notificações In-App + Migração para Herd
Sino de notificações na navbar com badge e painel lateral; disparos automáticos em confirmação, cancelamento e novo agendamento. Em paralelo, o ambiente migrou de Docker para Laravel Herd, cortando o tempo da suíte de testes de ~36s para ~6s.

### v0.8.1–v0.8.3 — Conformidade LGPD
O coração da adequação legal: consentimento bloqueante (com termos distintos para titular e operador), página pública de privacidade, exportação dos próprios dados em JSON (portabilidade — Art. 18, V), filtragem de dados sensíveis nos logs de auditoria, e controle de cache nas rotas clínicas. O modal de inatividade de sessão (27/30 min) fechou a frente de segurança da sessão.

### v0.7.x — Segurança e Identidade Visual
Sprint de segurança com 13 correções (IDOR, CSRF no logout, criptografia de sessão, índices, locale pt_BR), página de erro 403 amigável e a adoção da **paleta verde da logo do IFNMG** sobre o tema base.

### v0.5.x–v0.7.5 — UX, Agendamentos e Padronização
Padronização visual de 22 telas (breadcrumbs, busca dinâmica, listagens), especialidades e tipos de consulta migrados para o banco (gerenciáveis pelo admin), RBAC reforçado com Policies, e o módulo completo de **Agendamentos** (calendário FullCalendar para o profissional + wizard mobile-first para o aluno se auto-agendar).

### v0.1–v0.4 — Fundação e Core
Autenticação com bcrypt, schema relacional com integridade referencial, RBAC por níveis, o ciclo completo do prontuário (atendimento → consulta SOAP → exames/prescrições) com controle de autoria, e a primeira leva de melhorias de UX (perfil do paciente, histórico contextual).

---

*Para a lista completa e rastreável de itens técnicos (STs, UXs, dívidas técnicas, achados de segurança e itens LGPD), veja o [Catálogo de Itens de Desenvolvimento](CATALOGO_ST_UX.md).*
