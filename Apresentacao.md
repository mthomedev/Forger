# Apresentação — Forger (Clone do Instagram)

**Tecnologias:** Laravel 13 (API REST, padrão MSC) · Vue 3 + Vite + Pinia · MySQL 8.4 · Docker Compose · Swagger UI

---

## Roteiro (25 min)

| # | Etapa | Tempo |
|---|-------|-------|
| 1 | Compose.yaml (explicar e rodar) | 3 min |
| 2 | Projeto rodando (demo) | 5 min |
| 3 | Swagger UI | 4 min |
| 4 | Fluxo de execução do código + função dos arquivos | 8 min |
| 5 | Sintaxe | 3 min |
| 6 | Dúvidas da banca | 2 min |

---

## 1. Compose.yaml (3 min)

**Fala:** "O projeto inteiro sobe com um único arquivo: `compose.yaml` na raiz. Ele orquestra 3 serviços na mesma rede Docker, com um volume nomeado para o banco."

```yaml
services:
  mysql:          # banco MySQL 8.4
    image: mysql:8.4
    ports: ["3306:3306"]
    environment: { MYSQL_DATABASE: forger, MYSQL_USER: forger, MYSQL_PASSWORD: forger }
    volumes: [mysql_data:/var/lib/mysql]   # volume nomeado = dados persistem
    healthcheck:                            # espera o MySQL aceitar conexão
      test: ["CMD", "mysqladmin", "ping", "-h", "127.0.0.1", "--protocol=tcp"]
      interval: 5s
      retries: 10

  backend:        # API Laravel (Dockerfile.dev)
    build: { context: ./backend, dockerfile: Dockerfile.dev }
    ports: ["8000:8000"]
    volumes: [./backend:/app]              # bind mount = código vive fora do container
    depends_on:
      mysql: { condition: service_healthy } # só sobe quando o MySQL estiver pronto

  frontend:       # Vue 3 (Dockerfile multi-stage)
    build:
      context: ./frontend
      args: { VITE_API_URL: ${VITE_API_URL:-http://localhost:8000/api} }
    ports: ["5173:80"]                     # nginx dentro do container escuta na 80
```

**Pontos-chave para dizer:**
- **`depends_on` + healthcheck**: evita que o Laravel suba antes do banco estar pronto (erro clássico de "connection refused").
- **Volume nomeado `mysql_data`**: se der `docker compose down`, o banco não perde dados.
- **Bind mount `./backend:/app`**: no dev, edita o código e o `artisan serve` já recarrega; o banco roda num container isolado (PHP puro não é instalado na máquina host).
- **Frontend é multi-stage**: o `Dockerfile` do frontend tem estágio 1 (Node 22 builda o Vue) e estágio 2 (nginx só serve os arquivos estáticos). Imagem final enxuta.
- **`VITE_API_URL` como build arg**: URL da API configurável por variável de ambiente (requisito 8.1 do Context.md).

**Comandos (rodar ao vivo):**
```bash
docker compose up --build
# em outro terminal, dentro do container do backend:
docker compose exec backend php artisan migrate --seed
# acessar:
#   http://localhost:5173        (app)
#   http://localhost:8000/docs/docs.html (Swagger)
```

---

## 2. Projeto rodando — demo (5 min)

**Sequência de tela (memorize a ordem):**

1. **Home deslogado** → tenta acessar, o guard de rotas joga para `/login`. (Frase: "nenhuma rota do app é acessível sem autenticação — requisito do Context.md").
2. **Registrar** com dados fictícios (ou logar com usuário do seeder — senha `password`).
3. **Home** → feed com posts, curtir/descurtir um post, abrir post, comentar.
4. **Criar post** → botão "Criar" (modal), enviar imagem + legenda, ver ele aparecer no feed.
5. **Perfil** → editar bio/nome, trocar avatar (mudança visual imediata).
6. **Buscar** → digitar um nome com debounce, abrir perfil de outro usuário.
7. **Seguir** → botão de seguir/deixar de seguir, contador de seguidores atualizando.
8. **Deletar post próprio** → obrigatório no critério de aceite.
9. **Logout** → token revogado no backend.

**Dica:** deixe 2 abas abertas (dois usuários) para mostrar interação entre usuários.

---

## 3. Swagger UI (4 min)

**URL:** `http://localhost:8000/docs/docs.html`

**Como está implementado:** não é um pacote (l5-swagger/scribe) — é um arquivo estático:
- `backend/public/docs/docs.html` → a interface do Swagger UI (CSS/JS via CDN).
- `backend/public/docs/openapi.yaml` → a especificação OpenAPI 3.0.3 escrita à mão (931 linhas, 7 tags).

```yaml
# openapi.yaml (estrutura)
openapi: 3.0.3
info: { title: Forger API, version: "1.0.0" }
servers: [{ url: /api }]
security: [{ bearerAuth: [] }]          # todas as rotas exigem token por padrão
tags: [Auth, Profile, Posts, Likes, Comments, Follow, Users]
paths:
  /register:
    post:
      security: []                       # exceção: pública
      requestBody: { ... schema de validação ... }
      responses: { "201": ..., "422": ... }
components:
  securitySchemes:
    bearerAuth: { type: http, scheme: bearer }
  schemas:
    AuthResponse: { ... }
    ValidationError: { ... }
```

**Demo (ao vivo):**
1. Abrir `POST /register`, clicar em "Try it out", preencher `{name, email, password, password_confirmation}`, executar → resposta `201` com `access_token`.
2. Copiar o token, clicar em **Authorize** (cadeado) e colar como `Bearer <token>`.
3. Abrir `GET /posts` → executar → mostrar o `200` com `data`, `current_page`, `last_page` (paginação).

**Frase pronta:** "Toda a API está documentada e testável pela interface: cada rota tem corpo de requisição, esquemas de resposta e códigos HTTP coerentes — 201 ao criar, 422 em validação, 401 sem token, 403 ao tentar excluir post de outro usuário."

---

## 4. Fluxo de execução do código (8 min)

### O diagrama mental para desenhar no quadro:

```
Navegador (Vue 3)
   │  fetch com Authorization: Bearer <token>
   ▼
/api  (routes/api.php, prefixo automático)
   │  middleware: auth:sanctum
   ▼
Controller  →  só recebe a request, VALIDA e delega
   ▼
Service     →  regra de negócio (upload, feed, likes, follow...)
   ▼
Model (Eloquent)  →  entidade + relacionamentos + banco
   ▼
MySQL 8.4
```

### Função de cada arquivo (andar pela árvore):

**`backend/routes/api.php` (50 linhas)** — o mapa de entrada:
- Linha 13-14: rotas públicas `POST /register`, `POST /login`.
- Linha 16: `Route::middleware('auth:sanctum')->group(...)` → tudo dentro exige token.
- Linhas 26-45: posts, likes, comments, follow, users.
- Cada rota aponta para `[Controller::class, 'metodo']`.

**Controllers** (`app/Http/Controllers/`) — "finos":
- Fazem `$request->validate([...])` (ex.: `PostController.php` valida `image|max:5120` + `caption`).
- Recebem o `Service` por **injeção de dependência no construtor** (o container do Laravel resolve sozinho).
- Retornam `response()->json(...)` com status coerente.
- Ex.: `PostController::store` → valida → `$this->postService->create(...)` → `201`.

**Services** (`app/Services/`) — a regra de negócio:
- `AuthService` — registro (cria usuário + token), login (`Hash::check`), logout (revoga token atual).
- `PostService::getFeed` (linha 31-52) — feed: pega os IDs de quem você segue, junta com o seu, e **se não segue ninguém, mostra todos os posts** (fallback); paginação + flag `is_liked` em cada post.
- `PostService::delete` (linha 64-75) — **autorização por ownership**: se `$post->user_id !== $user->id`, lança `AuthorizationException` (não há Policies — é manual).
- `FollowService` — toggle de seguir com `DB::table` (não segue a si mesmo, não duplica).
- `LikeService::toggle` — curte/descurte; o banco garante no máximo 1 like por usuário via `unique(user_id, post_id)` na migration.
- `ProfileService::uploadAvatar` — valida imagem, **deleta o avatar antigo do disco**, salva o novo em `avatars/`.
- `CommentService::store/destroy` — cria comentário, e delete com ownership check.

**Models** (`app/Models/`):
- `User` — `#[Fillable]`, `#[Hidden]` (atributos PHP 8.3+ do Laravel 13); `HasApiTokens` (Sanctum); relacionamentos `posts`, `likes`, `comments`, `followers`, `following`; accessor `getAvatarUrlAttribute` → `avatar_url`.
- `Post` — `user`, `likes`, `comments`; accessor `getImageUrlAttribute` → `image_url` (aceita URL absoluta p/ o seeder com picsum.photos).
- `Like`, `Comment`, `Follow` — modelos simples com `$fillable` + `belongsTo`.

**Migrations** (`database/migrations/`) — o schema:
- `..._create_users_table` (padrão) + `2026_08_04_141355_add_profile_fields_to_users_table` (username único, bio, avatar_path).
- `..._create_likes_table` → `unique(['user_id', 'post_id'])` (regra de negócio "máximo 1 like por post" aplicada no banco).
- `..._create_follows_table` → `unique(['follower_id', 'followed_id'])`.

**Seeder** (`database/seeders/DatabaseSeeder.php`) — 10 usuários, 3-5 posts cada, follows, likes, comentários (dados de teste via `UserFactory` + picsum.photos).

**Frontend (`frontend/src/`):**
- `main.js` → bootstrap: cria app Vue, instala Pinia e o Router.
- `router/index.js` → rotas com `meta.requiresAuth`/`meta.guestOnly` + **guard global** `beforeEach`: se tem token mas não tem user, chama `fetchUser()` (valida o token); redireciona deslogados para `/login` e logados longe de `/login`. Lazy loading de todas as views.
- `stores/auth.js` (Pinia) → `user`, `token`, `isAuthenticated`, ações `register/login/fetchUser/logout`. `stores/ui.js` → controla o modal de criar post e propaga o post criado para as views.
- `services/api.js` → **wrapper próprio de `fetch`** (o axios está no package.json mas não é usado): injeta `Authorization: Bearer` do `localStorage`, serializa JSON ou FormData, extrai `message`/`errors` do padrão Laravel.
- `services/*` → um service por domínio (auth, post, profile, user).
- `views/` → 7 páginas (Home, Login, Register, Post, Profile, Search, UserProfile) + `layouts/` (MainLayout autenticado com NavBar/Footer/Modal; AuthLayout para login/registro).
- `components/common/` → design system próprio (BaseButton, BaseInput, BaseModal com Teleport, BaseAvatar) + design tokens em `App.vue` (CSS variables, tema dark).

**Exemplo de um request completo (para narrar):**
> "O usuário curte um post. O Vue em `PostCard.vue` chama `postService.toggleLike(postId)`. O `api.js` monta um `fetch POST /api/posts/5/like` com o Bearer token. A rota em `api.php` exige `auth:sanctum` → o middleware valida o token na tabela `personal_access_tokens`. O `LikeController::toggle` delega ao `LikeService::toggle`, que faz `firstOrCreate`/`delete`. Retorna `200` com o novo estado. O Vue atualiza o ícone e o contador reativamente."

---

## 5. Sintaxe (3 min)

Mostrar 3-4 trechos curtos e explicar. Sugestão de exemplos:

**Backend (PHP 8.3 / Laravel):**
```php
// 1. Atributos PHP (Laravel 13) no model — antes eram $fillable/$hidden
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable { ... }

// 2. Accessor — expõe URL calculada como campo JSON
protected function getImageUrlAttribute(): string
{
    return $this->image_path ? Storage::url($this->image_path) : '';
}

// 3. Null coalescing + arrow function no feed
'caption' => $data['caption'] ?? null,
$paginator->getCollection()->transform(fn ($post) => $post->is_liked = ...);
```

**Frontend (Vue 3 Composition API):**
```html
<script setup>
const likes = ref(0)
const isLiked = ref(false)
async function toggle() {
  const res = await postService.toggleLike(props.post.id)
  isLiked.value = res.liked
  likes.value = res.liked ? likes.value + 1 : likes.value - 1
}
</script>
<template>
  <button @click="toggle" :class="{ active: isLiked }">
    <v-if> ... </v-if>  <!-- condicional -->
    <v-for="c in comments"> ... </v-for>  <!-- lista -->
  </button>
</template>
```

**Frase pronta:** "Aplico os requisitos de sintaxe da Context.md: interpolation, `v-bind`/`v-on`, `v-if`/`v-else`, `v-for`, `v-model`, `ref()`/`computed()`/`watch` — todos presentes nas views."

---

## 6. Dúvidas da banca (previsões + respostas)

| Pergunta provável | Resposta curta |
|---|---|
| Por que MSC e não MVC? | API JSON pura: não existe View no backend (o Vue é a view). MSC separa Controller (request/resposta) de Service (regra de negócio), facilitando teste e reuso. |
| Como funciona a autenticação? | Sanctum com Personal Access Tokens (Bearer). Registro/login geram token via `createToken()`, frontend guarda em `localStorage`, envia em `Authorization: Bearer`. Logout revoga o token atual. |
| Quem garante que só o autor apaga o post? | `PostService::delete` compara `$post->user_id` com o usuário autenticado e lança `AuthorizationException` → 403. Não usei Policies porque o critério é simples (1 linha). |
| Como evita like duplicado? | Dupla proteção: constraint `unique(user_id, post_id)` no banco + `LikeService::toggle` com `firstOrCreate`. |
| O que acontece se o usuário não segue ninguém? | Fallback no `PostService::getFeed`: se `followingIds` é vazio, remove o `whereIn` e mostra todos os posts. |
| Por que feed usa paginação? | Evita carregar tudo de uma vez; Laravel devolve `current_page`/`last_page`/`data` e o HomeView faz "Ver mais" com `page + 1`. |
| Por que axios instalado e não usado? | A camada `services/api.js` foi implementada com `fetch` (nativo, sem dependência) e centraliza token + erros. O axios sobra no package.json — pode ser removido. |
| Como funciona o debounce da busca? | `SearchView` só chama a API 400ms depois da última tecla; evita uma request por caractere digitado. |
| Onde os arquivos (avatar/posts) ficam? | Disk `public` (`storage/app/public`), servidos via `Storage::url()`. Em produção há S3/R2 configurável (`FILESYSTEM_DISK=s3`). O avatar antigo é deletado ao trocar. |
| Por que dois compose.yaml? | Context.md exige um compose por projeto. O projeto tem 1 na raiz (orquestra tudo). Os requisitos falam de compose do backend e do frontend; documentar isso na fala. |
| Como valida os dados? | `$request->validate()` inline nos controllers (rules do Laravel). Erros retornam 422 com `{message, errors}` automaticamente. |
| Por que tokens sem expiração? | Simplicidade; logout revoga. Em produção real, daria para configurar expiração no `config/sanctum.php`. |
| Como o guard de rotas restaura a sessão? | No `beforeEach`: token existe + user vazio → `fetchUser()`; se falhar (token inválido), `logout()` e redireciona. |
| Testes? | PHPUnit com SQLite `:memory:` (`AuthTest` cobre registro/login). Vitest no frontend (`App.spec.js`). |

---

## Check-list final antes de apresentar

- [ ] `docker compose up --build` rodando na hora da demo (não rode a demo pela primeira vez ao vivo).
- [ ] `php artisan migrate --seed` executado (usuários de teste criados).
- [ ] Testar o fluxo completo 1x antes (criar post, like, comentário, follow, logout).
- [ ] Saber a senha dos usuários do seeder de cor (ou logar criando um usuário novo).
- [ ] Fechar outras abas; deixar o terminal do compose visível para mostrar os logs.
- [ ] Timers mentais: não passar de 3 min no compose, 5 na demo.
