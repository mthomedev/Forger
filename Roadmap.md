# Roadmap de Aprendizado — Clone do Instagram - Forger (Laravel + Vue + Docker)

> **Objetivo principal**
>
> Meu objetivo NÃO é apenas terminar este projeto.
>
> Meu objetivo é compreender profundamente cada decisão técnica, conseguir explicar qualquer parte da arquitetura e ser capaz de recriá-la sem copiar código.

---

# Filosofia

Cada etapa só será considerada concluída quando eu conseguir responder:

- O que foi feito?
- Por que isso existe?
- Como funciona internamente?
- O que acontece se remover essa parte?
- Como ela conversa com o restante do sistema?

Nunca avançar apenas porque "está funcionando".

---

# Estado Atual

## Backend

- Estrutura inicial criada
- Projeto Laravel instalado
- Pastas padrão existentes

## Frontend

- Projeto Vue criado
- Estrutura inicial criada

## Docker

- Estrutura inicial criada

## Banco

- Ainda sem modelagem

## Funcionalidades

Nenhuma implementada.

---

# FASE 0 — Entender completamente a arquitetura

Objetivo:
Aprender como todas as tecnologias conversam antes de escrever qualquer funcionalidade.

---

## 0.1 Estrutura do Backend

Aprender:

- estrutura do Laravel
- app/
- bootstrap/
- config/
- database/
- routes/
- storage/
- public/
- resources/

Entender:

- por que cada pasta existe
- quem usa
- quando ela é utilizada

Resultado esperado:

> Consigo explicar toda a estrutura do Laravel sem consultar documentação.

---

## 0.2 Estrutura do Frontend

Aprender:

- src/
- assets/
- components/
- views/
- router/
- stores/
- composables/

Resultado esperado:

> Consigo explicar toda a arquitetura do Vue.

---

## 0.3 Docker

Aprender:

- Dockerfile
- Dockerfile.dev
- compose.yaml
- volumes
- networks
- containers
- imagens

Resultado esperado:

> Consigo explicar exatamente como o projeto sobe.

---

## 0.4 Fluxo completo da aplicação

Entender:

```
Browser

↓

Vue

↓

Axios

↓

HTTP

↓

Laravel

↓

Route

↓

Controller

↓

Service

↓

Model

↓

Banco

↓

Model

↓

Service

↓

Controller

↓

JSON

↓

Axios

↓

Vue

↓

Tela
```

Resultado esperado:

Conseguir explicar esse fluxo inteiro sem olhar anotações.

---

# FASE 1 — Banco de Dados

Objetivo:

Aprender modelagem antes de criar qualquer tela.

---

## Estudar

Relacionamentos:

- 1:1
- 1:N
- N:N

Integridade

Foreign Keys

Índices

Constraints

Migrations

Seeders

Factories

---

## Modelar

Tabelas:

- users
- posts
- comments
- likes
- follows

(Opcional depois)

- stories
- highlights

Resultado esperado:

Conseguir desenhar o banco inteiro em um papel.

---

# FASE 2 — Laravel

Objetivo:

Entender profundamente o backend.

---

## Rotas

Aprender:

- api.php
- web.php
- Route::get
- Route::post
- Route::middleware

---

## Controllers

Aprender:

- responsabilidade
- ciclo de vida
- request
- response

---

## Services

Aprender:

- regra de negócio
- por que não colocar lógica no controller

---

## Models

Aprender:

- Eloquent
- ORM
- fillable
- hidden
- casts

---

## Relacionamentos

Implementar:

User

↓

Posts

↓

Comments

↓

Likes

↓

Follows

---

## Requests

Aprender:

Validação

Custom Request

Mensagens

---

## Resources

Aprender:

Transformação de JSON

---

## Exceptions

Aprender:

tratamento de erros

---

## Middleware

Aprender:

como uma requisição passa por eles

---

## Policies

Aprender:

autorização

---

## Sanctum

Aprender profundamente:

- login
- logout
- token
- autenticação

Resultado esperado:

Explicar exatamente como um usuário autenticado faz uma requisição.

---

# FASE 3 — Vue

Objetivo:

Aprender frontend antes de criar páginas.

---

## Vue básico

Aprender:

- template
- script setup
- props
- emits

---

## Reatividade

Aprender:

- ref
- reactive
- computed
- watch

---

## Diretivas

- v-if
- v-for
- v-model
- v-bind
- v-on

---

## Componentização

Aprender:

- componentes
- composição

---

## Vue Router

Aprender:

- rotas
- navegação
- parâmetros

---

## Pinia

Aprender:

- store
- estado global

---

## Axios

Aprender:

- GET
- POST
- PUT
- DELETE

---

Resultado esperado

Conseguir explicar exatamente como um botão dispara uma requisição até o backend.

---

# FASE 4 — Autenticação

Primeira funcionalidade.

## Backend

Cadastro

Login

Logout

Sanctum

Middleware

Validação

---

## Frontend

Tela Login

Tela Registro

Store usuário

Persistência

Proteção de rotas

Resultado esperado

Nenhuma página acessível sem login.

---

# FASE 5 — Perfil

Implementar:

- visualizar perfil
- editar perfil
- trocar foto
- bio
- username
- nome

Aprender:

Upload

Storage

Validação

Resultado esperado

Explicar todo o fluxo de upload.

---

# FASE 6 — Sistema de Posts

Implementar

Criar

Editar

Excluir

Listar

Visualizar

Aprender:

Upload de imagens

Relacionamentos

Paginação

Resultado esperado

Explicar completamente o ciclo de vida de um post.

---

# FASE 7 — Likes

Aprender

Relacionamento N:N

Pivot

Toggle Like

Resultado esperado

Explicar exatamente como funciona uma tabela pivot.

---

# FASE 8 — Comentários

Implementar

Criar

Excluir

Listar

Resultado esperado

Explicar relacionamento entre:

User

↓

Comment

↓

Post

---

# FASE 9 — Follow

Implementar

Seguir

Deixar de seguir

Contadores

Sugestões

Resultado esperado

Explicar relacionamento autorreferente.

---

# FASE 10 — Home

Implementar

Feed

Paginação

Posts

Likes

Comentários

Sugestões

Resultado esperado

Entender como montar um feed.

---

# FASE 11 — Busca

Implementar

Pesquisar

Nome

Username

Paginação

Resultado esperado

Explicar consultas.

---

# FASE 12 — Swagger

Aprender

OpenAPI

Documentação

Schemas

Endpoints

Resultado esperado

Toda API documentada.

---

# FASE 13 — Docker

Aprender profundamente

Dockerfile

Dockerfile.dev

Compose

Volumes

Networks

Variáveis

Resultado esperado

Conseguir subir o projeto do zero.

---

# FASE 14 — Deploy

Aprender

Produção

Variáveis

Banco

Uploads

CORS

Resultado esperado

Projeto funcionando online.

---

# FASE 15 — Extras

Stories

Highlights

Expiração

Hospedagem

---

# FASE 16 — Revisão Geral

Agora revisar TODO o projeto.

Arquivo por arquivo.

Para cada arquivo responder:

- por que existe?
- quem chama?
- quem ele chama?
- qual responsabilidade?
- como poderia melhorar?

---

# Checklist de Domínio

Só considerar o projeto concluído quando eu conseguir explicar sem consultar nada:

- Arquitetura Laravel
- Arquitetura Vue
- Docker
- HTTP
- REST
- JSON
- Axios
- Sanctum
- Middleware
- Policies
- Controllers
- Services
- Models
- Eloquent
- Migrations
- Seeders
- Factories
- Upload
- Storage
- Banco de Dados
- Relacionamentos
- Swagger
- Vue Router
- Pinia
- Reatividade
- Componentização
- Fluxo completo de uma requisição

---

# Método de Estudo (para cada etapa)

Sempre seguir esta sequência:

1. **Conceito** — O que é e por que existe.
2. **Arquitetura** — Onde essa parte se encaixa no sistema.
3. **Fluxo** — Como ela conversa com os outros componentes.
4. **Implementação** — Escrever o código gradualmente.
5. **Explicação linha por linha** — Entender cada instrução.
6. **Boas práticas** — Como seria feito em um projeto profissional.
7. **Erros comuns** — O que evitar.
8. **Exercício** — Reescrever ou modificar a funcionalidade sem copiar.
9. **Revisão** — Explicar a funcionalidade com as próprias palavras.
10. **Perguntas** — Só avançar quando todas forem respondidas corretamente.

---

# Meta Final

Ao terminar este roadmap, devo ser capaz de:

- Criar uma API Laravel do zero.
- Criar um frontend Vue do zero.
- Integrar frontend e backend sem auxílio.
- Explicar toda a arquitetura do projeto.
- Defender todas as decisões técnicas em uma apresentação.
- Refatorar qualquer parte do código com segurança.
- Desenvolver projetos semelhantes sem depender de copiar tutoriais.
