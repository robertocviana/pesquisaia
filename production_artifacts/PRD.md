# PRD - Plataforma de Pesquisas Conversacionais com IA

## Visão Geral

Sistema SaaS para criação de pesquisas conversacionais através de IA.

O usuário informa o objetivo da pesquisa através de um chat. A IA auxilia na construção das perguntas, gera um link compartilhável e posteriormente analisa as respostas gerando insights.

---

# FASE 1 - AUTENTICAÇÃO E DASHBOARD

## Objetivo

Permitir acesso ao sistema e gerenciamento das pesquisas.

---

## Tela: Login

### Campos

* E-mail
* Senha

### Regras de Negócio

* E-mail obrigatório
* Senha obrigatória
* Exibir mensagem de erro para credenciais inválidas
* Usuário autenticado é direcionado para Dashboard

---

## Tela: Cadastro

### Campos

* Nome
* E-mail
* Senha
* Confirmar senha

### Regras de Negócio

* E-mail deve ser único
* Senha mínima de 8 caracteres
* Confirmar senha deve coincidir
* Após cadastro, usuário é autenticado automaticamente

---

## Tela: Dashboard

### Objetivo

Exibir pesquisas do usuário.

### Regras de Negócio

* Mostrar apenas pesquisas do usuário logado
* Ordenar pela data mais recente
* Exibir quantidade de respostas
* Exibir status da pesquisa

### Status possíveis

* Rascunho
* Ativa
* Encerrada

---

# FASE 2 - CRIAÇÃO DA PESQUISA

## Objetivo

Permitir criar uma pesquisa utilizando IA.

---

## Tela: Nova Pesquisa

### Formato

Chat conversacional.

### Fluxo

1. Usuário informa objetivo da pesquisa
2. IA faz perguntas complementares
3. IA gera proposta da pesquisa

### Dados coletados

* Nome da pesquisa
* Objetivo
* Público-alvo
* Quantidade desejada de respostas
* Data limite (opcional)

### Regras de Negócio

* Objetivo é obrigatório
* Pesquisa inicia como Rascunho
* Conversa deve ser salva automaticamente

---

# FASE 3 - REVISÃO DA PESQUISA

## Objetivo

Permitir aprovação da pesquisa antes da publicação.

---

## Tela: Revisão

### Exibir

* Nome da pesquisa
* Objetivo
* Público-alvo
* Perguntas geradas

### Ações

* Editar pergunta
* Excluir pergunta
* Adicionar pergunta
* Reordenar perguntas

### Regras de Negócio

* Deve existir pelo menos 1 pergunta
* Não permitir publicação sem perguntas
* Alterações devem ser salvas automaticamente

---

# FASE 4 - PUBLICAÇÃO

## Objetivo

Disponibilizar a pesquisa para respostas.

---

## Tela: Pesquisa Publicada

### Exibir

* Link público
* QR Code
* Status
* Quantidade de respostas

### Regras de Negócio

Ao publicar:

* Gerar identificador único
* Gerar URL pública
* Alterar status para Ativa

Exemplo:

https://sistema.com/pesquisa/abc123

---

# FASE 5 - EXPERIÊNCIA DO RESPONDENTE

## Objetivo

Permitir que participantes respondam a pesquisa.

---

## Tela: Boas-vindas

### Exibir

* Nome da pesquisa
* Descrição
* Botão iniciar

### Regras de Negócio

* Participante não precisa criar conta
* Link deve ser acessível publicamente

---

## Tela: Conversa da Pesquisa

### Fluxo

* Exibir pergunta
* Receber resposta
* Avançar para próxima pergunta

### Regras de Negócio

* Respostas são salvas automaticamente
* Usuário pode interromper e continuar depois
* Registrar data e horário de cada resposta

---

## Tela: Finalização

### Regras de Negócio

Ao finalizar:

* Marcar entrevista como concluída
* Atualizar contador de respostas da pesquisa

---

# FASE 6 - GERENCIAMENTO DAS RESPOSTAS

## Objetivo

Permitir visualização das respostas recebidas.

---

## Tela: Lista de Respostas

### Exibir

* Respondente
* Data
* Status

### Regras de Negócio

Status possíveis:

* Em andamento
* Concluída

---

## Tela: Detalhe da Resposta

### Exibir

Pergunta e resposta em formato de conversa.

### Regras de Negócio

* Somente proprietário da pesquisa pode visualizar

---

# FASE 7 - ENCERRAMENTO DA PESQUISA

## Objetivo

Controlar quando uma pesquisa deixa de receber respostas.

---

## Critérios de Encerramento

### Manual

Usuário clica em Encerrar Pesquisa.

### Por Quantidade

Quando atingir quantidade mínima definida.

### Por Data

Quando atingir data limite.

### Regras de Negócio

Ao encerrar:

* Alterar status para Encerrada
* Bloquear novas respostas
* Manter acesso aos relatórios

---

# FASE 8 - RELATÓRIOS

## Objetivo

Transformar respostas em insights.

---

## Tela: Relatório

### Exibir

* Total de respostas
* Resumo executivo
* Principais insights
* Comentários relevantes

### Regras de Negócio

* Relatório só pode ser gerado para pesquisas encerradas
* Deve ser possível regenerar relatório

---

# FASE 9 - EXPORTAÇÃO

## Objetivo

Permitir compartilhamento dos resultados.

---

## Exportações

### PDF

Conteúdo:

* Resumo
* Insights
* Respostas

### CSV

Conteúdo:

* Todas as respostas

### Regras de Negócio

* Apenas proprietário da pesquisa pode exportar

---

# MODELO DE DADOS

## Pesquisa

```
id
usuario_id
nome
objetivo
publico_alvo
status
meta_respostas
data_limite
url_publica
created_at
updated_at
```

---

## Pergunta

```
id
pesquisa_id
ordem
texto
tipo
created_at
```

---

## Respondente

```
id
pesquisa_id
identificador
created_at
```

---

## Resposta

```
id
respondente_id
pergunta_id
texto_resposta
created_at
```

---

# ROADMAP FUTURO

## V2

* Templates de pesquisa
* Compartilhamento via WhatsApp
* Pesquisa por QR Code
* Dashboard executivo

## V3

* Benchmark de mercado
* Personas automáticas
* Plano de ação gerado por IA
* Comparação entre pesquisas

## V4

* Agente entrevistador por voz
* Ligações automáticas
* Entrevistas via WhatsApp com IA
* Análise multimodal (áudio, vídeo e texto)
