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
| **v0.10.x** | 31/05/2026 | Linha de **correções e melhorias** com base em testes manuais (em andamento) | 80 |
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

### v0.10.x — Correções e UX (linha em andamento)
Primeira linha focada em **polir o que já existe** com base em testes manuais por perfil. A **v0.10.1** nasceu do teste do Admin e entregou: correção do erro 419 (CSRF), legibilidade da paginação, logo em rotas profundas, paleta verde consistente (hover/menu) com cache-busting de CSS; página 404 personalizada, item "Configurações" no menu, tela de edição de paciente reestruturada com os campos de onboarding, mini-card de atendimentos recentes, filtros nas listagens de atendimentos e consultas, e replanejamento da tela de auditoria. Mais três análises de RBAC/dados (Admin somente leitura, tipos de consulta genéricos, padronização de links).

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
