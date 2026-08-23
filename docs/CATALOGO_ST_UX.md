# Catálogo de Itens de Desenvolvimento — Prontu IF

> Índice central e rastreável dos itens que guiaram o desenvolvimento — STs (histórias/tarefas), itens de UX, dívidas técnicas, achados de segurança e itens de conformidade LGPD.
> Esses códigos aparecem espalhados em comentários de código, mensagens de commit e descrições de PR; este documento os reúne em um só lugar.

**Legenda de status:** ✅ Concluído · 🔨 Em andamento · 📌 Backlog/Planejado · ⏸️ Diferido

---

## 1. STs — Histórias e Tarefas

| ID | Item | Status | Versão |
|---|---|---|---|
| ST-08 | Controle de autoria (`criado_por_id`) em consultas/exames/prescrições | ✅ | v0.3.x |
| ST-09 | Módulo de Agendamentos (A: calendário · B: disponibilidade · C: wizard do aluno) | ✅ | v0.6.0 / v0.7.5 |
| ST-10 | Perfil do usuário (avatar, dados pessoais, senha) | ✅ | v0.10.0 |
| ST-11 | Especialidades e Tipos de Consulta gerenciáveis em banco | ✅ | v0.5.1 |
| ST-12 | Views especializadas por especialidade (degradação graciosa) | ✅ | v0.4.0 |
| ST-16 | Dashboard por papel (5 perfis) + widget de conformidade LGPD | ✅ | v0.9.1a/b |
| ST-15 | Onboarding de primeiro acesso (wizard 7 passos + operador) | ✅ | v0.9.4 |
| ST-07 | Governança — inativação lógica de pacientes/profissionais | 📌 | — |
| ST-14 | Importação de pacientes via CSV | ✅ | v0.11.x |
| ST-13 | Login via Google (somente domínio `@ifnmg.edu.br`) | 📌 | pós-TCC |
| ST-17 | Complemento do cadastro clínico pelo profissional na consulta | 📌 | pós-TCC (dep. v0.9.4) |
| ST-18 | Curso como cadastro gerenciável (hoje texto livre) | 📌 | sprint de Configurações |
| ST-19 | Exportação dos registros de auditoria (CSV) | ✅ | v0.11.x |

---

## 2. UX — Experiência e Interface

| ID | Item | Status | Versão |
|---|---|---|---|
| UX-13 | Fluxo guiado atendimento → consulta | ✅ | v0.4.0 |
| UX-14 | Histórico contextual do paciente | ✅ | v0.4.0 |
| UX-16 | Breadcrumbs globais (via `@stack`) | ✅ | v0.5.0 |
| UX-23 | Padrão visual em 22 telas (cards, busca dinâmica, kebab, indicadores) | ✅ | v0.5.0 |
| UX-24 | Edição de paciente por nível (campos sensíveis vs. complementares) | ✅ | v0.5.2 |
| UX-15 | Modal de perfil do paciente na listagem | 📌 | análise futura |
| UX-17 | Filtros rápidos em chips na listagem | 📌 | backlog |
| UX-18 | Filtro avançado + ordenação | 📌 | backlog |
| UX-19 | Status derivado do paciente | 📌 | backlog |
| UX-20 | Empty states com CTA, skeleton loading, tratamento de erro | 📌 | backlog |
| UX-21 | Avatar/iniciais nas listagens | 📌 | backlog |
| UX-22 | Mini-indicadores dependentes de agendamento | 📌 | backlog |
| UX-back | Revisar e padronizar todos os botões "Voltar" | 📌 | backlog |

### UX da linha v0.10.x (correções do teste Admin)

| ID | Item | Status | Versão |
|---|---|---|---|
| UX-01 | Página de erro 404 personalizada (timer → home) | ✅ | v0.10.1 |
| UX-02 | Item "Configurações" no menu lateral (nível ≤ 2) — fecha DT-10 | ✅ | v0.10.1 |
| UX-03 | `editar_paciente` reestruturada (hero + 7 cards + campos de onboarding) | ✅ | v0.10.1 |
| UX-04 | Mini-card "Atendimentos recentes" no perfil do paciente | ✅ | v0.10.1 |
| UX-05 | Filtros nas listagens de atendimentos e consultas | ✅ | v0.10.1 |
| UX-06 | Replanejamento da tela de auditoria (filtros, badges, IP) | ✅ | v0.10.1 |

---

## 3. Análises de RBAC/Dados (v0.10.1)

| ID | Item | Status |
|---|---|---|
| ANALISE-01 | Admin (nível 1) somente leitura — não cria atendimento/consulta | ✅ |
| ANALISE-03 | Tipos de consulta + genéricos (Primeira Consulta, Urgência, Avaliação) | ✅ |
| ANALISE-04 | Link do nome do paciente padronizado → perfil `/pacientes/{id}` | ✅ |
| ANALISE-02 | Restyle das telas de novo atendimento/consulta | ⏸️ diferido |

---

## 4. Segurança (S) — Auditoria OWASP

| ID | Achado | Status | Versão |
|---|---|---|---|
| S-01 | IDOR em consulta (Policy de ownership) | ✅ | v0.7.6 |
| S-02 | IDOR em agendamento (Policy view/update/cancelar) | ✅ | v0.7.6 |
| S-03 | Sessão sem criptografia → `encrypt = true` | ✅ | v0.7.6 |
| S-04 | Logout via GET → POST com CSRF | ✅ | v0.7.6 |
| S-06 | Rate limit ausente em rotas AJAX → `throttle:60,1` | ✅ | v0.9.0 |
| S-07 | Cookie de tema sem whitelist (XSS) | ✅ | v0.7.6 |
| S-08 | Null safety em cancelamento de agendamento | ✅ | v0.7.6 |
| S-10 | Echo não escapado desnecessário | ✅ | v0.7.6 |
| S-09 | `.env` versionado com `APP_DEBUG=true` (infra) | 📌 | pendente |

---

## 5. Conformidade LGPD (L)

| ID | Item | Status | Versão |
|---|---|---|---|
| L-01 | Consentimento bloqueante + tela de aceite | ✅ | v0.8.1 / v0.8.2 |
| L-03 | `AuditObserver` filtra dados sensíveis de saúde do log | ✅ | v0.8.1 |
| L-04 | Página pública de Política de Privacidade (Art. 9º) | ✅ | v0.8.1 |
| L-06 | Portabilidade — exportação dos próprios dados em JSON (Art. 18, V) | ✅ | v0.8.1 |
| — | Consentimento para todos os perfis + recusa + revogação + Cache-Control | ✅ | v0.8.2 |
| — | Widget de conformidade (4 buckets agregados, sem PII) | ✅ | v0.9.1a |
| L-02 | Criptografia em repouso (TDE no MySQL) | 📌 | documentado |
| L-05 | Direito ao esquecimento / anonimização | 📌 | pendente |

---

## 6. Dívida Técnica (DT / DTi)

| ID | Item | Status | Versão |
|---|---|---|---|
| DT-03 | RBAC com Policies/Gates | ✅ | v0.5.1 |
| DT-10 | Menu lateral sem link para `/configuracoes/` | ✅ | v0.10.1 (UX-02) |
| DT-02 | `Route::resource()` (URLs legadas) | 📌 | pendente |
| DT-06 | `CheckNivel` não respeita `expectsJson()` | 📌 | pendente |
| DT-07 | Migrations sem `down()` | ✅ | auditado — único `down()` vazio é intencional (LGPD) |
| DT-08 | `package.json`: script `build` recursivo | ✅ | v0.11.x |
| DTi-01 | CSS/JS inline nos Blades → assets compilados | 📌 | pendente |
| DTi-02 | Paleta definitiva via SCSS (todos os componentes) | 📌 | reforçada em v0.10.1 |
| DTi-03 | Wizard: botão PRÓXIMO visível no passo final | ✅ | resolvido em v0.10.3/v0.10.4 |
| DTi-04 | Wizard: hover/active do botão volta ao roxo | ✅ | resolvido em v0.10.3/v0.10.4 |
| DTi-05 | Wizard: ponto ativo (dot) roxo | ✅ | resolvido em v0.10.3/v0.10.4 |

---

## 7. Melhorias de Arquitetura (Arq)

| ID | Item | Status | Versão |
|---|---|---|---|
| Arq-05 | Índices em `pacientes` (documento, matrícula) | ✅ | v0.7.6 |
| Arq-06 | Locale `pt_BR` | ✅ | v0.7.6 |

---

*Histórico cronológico das versões em [HISTORICO_VERSOES.md](HISTORICO_VERSOES.md).*
