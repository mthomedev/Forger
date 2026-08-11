# Apresentação — Forger (Clone do Instagram)

**Stack:** Laravel 13 (API REST, padrão MSC) · Vue 3 + Vite + Pinia · MySQL 8.4 (dev) / TiDB Serverless (prod) · Supabase Storage (prod) · Docker Compose · Swagger UI · Render

**Frase de abertura:** "O projeto inteiro sobe com um único comando. A apresentação segue o fluxo real: primeiro como o projeto roda (Docker), depois o que ele faz (demo), o que há dentro do código, e por fim como está em produção."

---

## Roteiro (25 min)

| #   | Etapa                                | Tempo |
| --- | ------------------------------------ | ----- |
| 1   | Infra dev: compose.yaml              | 3 min |
| 2   | Dockerfiles (dev vs prod)            | 3 min |
| 3   | Demo do app (tela + editor)          | 5 min |
| 4   | Swagger UI                           | 2 min |
| 5   | Fluxo do código (backend → frontend) | 6 min |
| 6   | Produção (Render + TiDB + Supabase)  | 4 min |
| 7   | Dúvidas da banca                     | 2 min |

---

## 1. Infra dev — `compose.yaml` (3 min)

**Frase:** "O projeto inteiro sobe com um único arquivo: ele descreve 3 containers — banco, backend e frontend — numa rede privada, com um volume nomeado para o banco."

| Serviço      | Imagem / Build                   | Porta       | Papel                      |
| ------------ | -------------------------------- | ----------- | -------------------------- |
| **mysql**    | imagem oficial `mysql:8.4` (LTS) | 3306 (host) | Banco de dados             |
| **backend**  | build `Dockerfile.dev`           | 8000        | API Laravel                |
| **frontend** | build multi-stage (Node → nginx) | 5173 → 80   | SPA Vue servida pelo nginx |

**O que cada peça mostra (frases prontas):**

- **`environment` do MySQL:** a imagem exige `MYSQL_ROOT_PASSWORD` e, com `MYSQL_DATABASE`/`MYSQL_USER`/`MYSQL_PASSWORD`, cria o banco e o usuário de aplicação no primeiro boot — o Laravel não usa root (boa prática).
- **Volume `mysql_data`:** a pasta de dados do MySQL (`/var/lib/mysql`) vive fora do container → os dados **sobrevivem ao `down`** e ao rebuild.
- **Rede `forger-network`:** containers se enxergam **pelo nome do serviço** (`backend` chama `mysql:3306`), sem IP fixo; isolados de outros projetos.
- **`healthcheck` + `depends_on`:** o backend só sobe quando o MySQL responder "vivo" — sem isso, o Laravel quebra com connection refused.
- **Bind mount `./backend:/app`:** o container roda o SEU código (edita = vê na hora, sem rebuild).
- **`VITE_API_URL` como build arg:** o Vite "congela" a URL da API dentro do JS na hora do build — por isso é passada no build, não em runtime.

**Comandos (rodar ao vivo):**

```bash
docker compose up --build
docker compose exec backend php artisan migrate --seed
# app: http://localhost:5173 · Swagger: http://localhost:8000/docs/docs.html
```

---

## 2. Dockerfiles — dev vs prod (3 min)

**Frase pronta:** "O Dockerfile de dev é a oficina — ferramentas na mesa, ajuste fino; o de produção é a vitrine — pronta, segura e enxuta. Dois arquivos porque os objetivos são opostos e o Dockerfile não tem condicional."

|                  | Dev (`Dockerfile.dev`)               | Prod (`Dockerfile`)                               |
| ---------------- | ------------------------------------ | ------------------------------------------------- |
| **Objetivo**     | Velocidade de iteração               | Performance + segurança + tamanho                 |
| **Código**       | Bind mount (vivo, edita sem rebuild) | `COPY` (imutável na imagem)                       |
| **Dependências** | Com dev (PHPUnit/Faker)              | `--no-dev` (menos superfície de ataque)           |
| **Cache do PHP** | Sem opcache (mudança imediata)       | `opcache` (bytecode em memória, 2–3× mais rápido) |
| **Boot**         | `artisan serve`                      | `migrate --force` + `serve` na porta `$PORT`      |
| **Usuário**      | `www-data` com UID/GID do host       | `www-data` padrão (nunca root)                    |
| **Quem escolhe** | `compose.yaml`                       | `render.yaml`                                     |

**Frontend multi-stage (por que existe):** o nginx não roda Node. Estágio 1 (Node) builda o Vue → estágio 2 (nginx) só serve o `dist/` pronto. Imagem final ~50MB em vez de ~1GB. `npm ci` + lock file = build reproduzível; COPYs em ordem = layer caching (rebuild em segundos).

**`nginx.conf` — os 2 detalhes que valem a pena dizer:**

- `try_files $uri /index.html` — o Vue Router usa URLs limpas (`/profile`). Sem isso, recarregar uma rota direta daria 404.
- `daemon off` — o nginx roda em foreground para ser o PID 1 do container (senão o container "morre" na hora).

**Se perguntarem:** layer caching (cada instrução = camada imutável; deps antes do código), Alpine (~5MB de base), `.dockerignore` (node_modules/vendor/.env não entram na imagem), `COPY --from=composer` (puxa o Composer da imagem oficial).

---

## 3. Demo do app (5 min)

**Sequência de tela (memorize a ordem):**

1. **Home deslogado** → o guard de rotas joga para `/login`. (Frase: "nenhuma rota do app é acessível sem autenticação — requisito do Context.md".)
2. **Logar** com usuário do seeder (senha `password`) — ou registrar um novo.
3. **Feed** → curtir/descurtir, abrir post, comentar.
4. **Criar post** → modal, imagem + legenda, ver aparecer no feed.
5. **Perfil** → editar bio/avatar (mudança imediata na tela).
6. **Buscar** → digitar nome, abrir perfil de outro usuário.
7. **Seguir** → botão alterna, contador atualiza.
8. **Deletar post próprio** (critério de aceite).
9. **Logout** → token revogado no backend.

**Dica:** 2 abas abertas (dois usuários) para mostrar interação.

**Roteiro do editor (quando pedirem código — abra só estes 4):**

| Arquivo                          | Aponte                                                        | Diga                                                                            |
| -------------------------------- | ------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| `views/LoginView.vue`            | `v-model` (~55), `@submit.prevent` (47), `v-if` de erro (~72) | "Input ligado à variável; no submit chama o store; erro só aparece se falhar."  |
| `views/HomeView.vue`             | `onMounted` (152), `v-for` (63)                               | "Quando a tela abre, busco os posts da API; o v-for repete um cartão por post." |
| `components/PostCard.vue`        | `defineProps` (99), `defineEmits` (106)                       | "Pai dá o dado (props), filho avisa (emit): props desce, emit sobe."            |
| `components/CreatePostModal.vue` | `v-model` (36), `emit('created')` (90)                        | "O mesmo padrão: v-model pra digitar, emit pra avisar que publicou."            |

**Frase final do editor:** "Os mesmos 3 conceitos — v-model, v-for, emit — se repetem em todas as telas."

---

## 4. Swagger UI (2 min)

**URLs:** dev `http://localhost:8000/docs/docs.html` · prod `https://forger-api.onrender.com/docs/docs.html`

**Como foi feito:** não é pacote — é um arquivo estático: `docs.html` (interface) + `openapi.yaml` (especificação OpenAPI 3.0.3 escrita à mão, 7 tags, todas as rotas exigem Bearer por padrão, exceto register/login).

**Demo:** `POST /register` → Try it out → 201 com `access_token` → Authorize (Bearer) → `GET /posts` → 200 com paginação.

**Frase pronta:** "Toda a API está documentada e testável pela interface: códigos HTTP coerentes — 201 ao criar, 422 em validação, 401 sem token, 403 ao excluir post de outro usuário."

---

## 5. Fluxo de execução do código (6 min)

### O diagrama mental (desenhar no quadro)

```
Navegador (Vue 3)
   │  fetch + Authorization: Bearer <token>
   ▼
/api (routes/api.php)  →  middleware auth:sanctum (valida o token, 401 se inválido)
   ▼
Controller  →  recebe a request, VALIDA (422) e delega. "Porteiro" (fino, sem regra).
   ▼
Service     →  regra de negócio. "Cérebro". Reusável por outras portas de entrada.
   ▼
Model (Eloquent)  →  tradutor: tabela SQL ↔ objeto PHP. $post->user dispara um JOIN.
   ▼
MySQL 8.4 (dev) / TiDB (prod)
```

**Por que cada camada existe (1 linha):** rota = mapa URL→função · middleware = segurança num lugar só · controller = porteiro que valida e delega · service = onde mora a regra · model = traduz SQL para objeto.

### Backend — os arquivos que importam

- **`routes/api.php` (50 linhas):** `register`/`login` públicos (linha 13–14); todo o resto dentro de `middleware('auth:sanctum')` (linha 16).
- **Controllers:** fazem `$request->validate()` (ex.: imagem `max:5120`), recebem o Service por injeção no construtor, devolvem `response()->json(...)`.
- **`bootstrap/app.php`:** `shouldRenderJsonWhen($request->is('api/*'))` → **todo erro da API vira JSON**, nunca HTML (contrato consistente pro frontend).
- **Services (a regra de negócio):**
  - `AuthService` — registro (gera username automático: `Str::slug($name).rand(100,999)`), login (`Hash::check`), logout (revoga o token atual). Senha criptografada pelo cast `hashed` do model.
  - `PostService::getFeed` — posts de quem você segue + os seus; **se não segue ninguém, mostra todos** (fallback); paginado + flag `is_liked`.
  - `PostService::delete` — ownership: `$post->user_id !== $user->id` → 403; limpa likes/comments e apaga a imagem.
  - `LikeService::toggle` — curte/descurte; o banco garante 1 like por usuário (`unique(user_id, post_id)`).
  - `FollowService` — toggle sem seguir a si mesmo; `CommentService` — criar/deletar com ownership.
- **Models:** `User` (HasApiTokens, relacionamentos, accessor `avatar_url`), `Post` (accessor `image_url` aceita URL absoluta — o seeder usa picsum.photos).
- **Migrations:** schema versionado; constraints `unique` em likes e follows (regra no banco).
- **Seeder:** 10 usuários (senha `password`), 3–5 posts cada, follows, likes, comentários — dados de teste completos para a demo.

### Frontend — a anatomia (`frontend/src/`)

| Peça                  | Papel                                                                                                                                                                      |
| --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `main.js`             | bootstrap: cria o app, instala Pinia e Router                                                                                                                              |
| `router/index.js`     | 8 telas + guard `beforeEach`: sem token → `/login`; com token, `fetchUser()` valida; lazy loading; título da aba                                                           |
| `stores/auth.js`      | Pinia: `user`, `token`, `isAuthenticated`, ações login/register/fetchUser/logout — o "armário compartilhado"                                                               |
| `stores/ui.js`        | controla o modal de criar post e propaga o post criado para as views                                                                                                       |
| `services/api.js`     | **wrapper próprio de `fetch`** (o axios está no package.json mas não é usado): injeta o Bearer token, serializa JSON/FormData, extrai `message`/`errors` do padrão Laravel |
| `services/*`          | um service por domínio (auth, post, profile, user)                                                                                                                         |
| `views/` + `layouts/` | 8 telas; MainLayout (autenticado, com NavBar/modal) e AuthLayout (login/registro)                                                                                          |
| `components/common/`  | design system próprio: BaseButton, BaseInput, BaseModal (Teleport), BaseAvatar; tema dark via CSS variables no `App.vue`                                                   |
| `utils/`              | `media.js` (monta URL de imagem), `time.js` (formata "5m ago")                                                                                                             |

**Os 4 padrões Vue que se repetem em tudo (apontar no editor):** `v-model` (input ↔ variável) · `v-for` (lista) · `@click` (evento) · `props`/`emits` (pai ↔ filho).

### Request completa narrada (o like)

> "O usuário clica no coração no `PostCard.vue`. O Vue chama `postService.toggleLike(id)`; o `api.js` monta um `fetch POST /api/posts/5/like` com o Bearer token. A rota exige `auth:sanctum` → o middleware valida o token na tabela `personal_access_tokens`. O `LikeController::toggle` delega ao `LikeService::toggle` (firstOrCreate/delete). Retorna `200`; o Vue atualiza o ícone e o contador reativamente, sem recarregar."

### Ciclo de vida de uma requisição Laravel (se a banca for de arquitetura)

```
public/index.php → bootstrap/app.php → middleware global (CORS) → roteador (api.php)
→ auth:sanctum → Controller (validate) → Service (regra) → Model → SQL → banco
→ response()->json() → volta pelo mesmo caminho
```

**Ponto de qualidade a citar:** sobras de desenvolvimento (`/test-user`, `/profile/{username}`) foram **removidas** e `/users/{userId}/posts` foi movida para dentro do grupo `auth:sanctum` — hoje só register/login são públicas, conforme o Context.md.

---

## 6. Produção — Render + TiDB + Supabase (4 min)

### Por que 4 serviços em 3 plataformas?

**Regra de ouro:** "só o que é efêmero fica no Render; tudo que precisa sobreviver (banco, arquivos) mora fora."

| Serviço                   | O que faz                        | Por que mora ali                                                                           |
| ------------------------- | -------------------------------- | ------------------------------------------------------------------------------------------ |
| **Render — forger-web**   | SPA Vue via nginx                | Precisa de site público; o multi-stage já gera isso                                        |
| **Render — forger-api**   | API Laravel + Swagger            | Precisa de runtime PHP público                                                             |
| **TiDB Cloud Serverless** | Banco MySQL-compatível           | Render **não tem MySQL gerenciado**; TiDB é grátis, não dorme, aceita 100% do código MySQL |
| **Supabase Storage**      | Imagens (posts/avatares), API S3 | Filesystem do Render free é **efêmero** — toda imagem sumiria no redeploy                  |

Tudo com plano grátis: **o app não custa nada por mês**.

### Fluxo de comunicação

```
Navegador → forger-web (nginx + SPA)
             │ fetch HTTPS + Bearer
             ▼
           forger-api (Laravel)
             ├── MySQL (porta 4000, TLS) → TiDB Serverless
             └── S3 API (credenciais AWS_*) → Supabase Storage
             │
             └── <img> carrega DIRETO do Supabase (não passa pela API)
```

### Decisões que valem pontos

1. **Sem `preDeployCommand`** (free): migrations rodam no `CMD` do Dockerfile — `migrate --force` é idempotente (roda a cada boot sem apagar dados).
2. **`DB_PORT=4000`** — porta do TiDB (MySQL local usa 3306).
3. **`MYSQL_ATTR_SSL_CA`** — TiDB **exige TLS**; o Laravel lê a env var no `config/database.php`.
4. **Supabase no lugar do R2** — R2 pedia cartão; troca = **só env vars `AWS_*`**, zero linha de código (mesmo driver S3).
5. **Segredos:** no `render.yaml`, `sync: false` = preenche no painel, **não vai pro GitHub**; `generateValue: true` = Render gera (APP_KEY); `value:` = público.
6. **Cold start:** free dorme após 15 min e demora 30–60s pra acordar — pingue com cron-job.org a cada 10 min antes da demo.
7. **CORS:** Laravel já libera `api/*` para qualquer origem (token Bearer, sem cookies).

**Verdade honesta:** num projeto real tudo moraria num provedor pago (Fly.io + RDS + S3). Aqui a meta era custo zero — o preço: 4 painéis para gerenciar e cold start.

**Verificação pós-deploy (citar):** register → 201 com token (TiDB + migrations OK); profile com token (leitura OK); upload → arquivo no Supabase; Swagger no ar; auto-deploy confirmado (após push, `/api/test-user` passou de 200 → 404 sozinho).

---

## 7. Dúvidas prováveis (2 min)

| Pergunta                              | Resposta curta                                                                                                                                |
| ------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| Por que MSC e não MVC?                | API JSON pura: o Vue é a view. Service separa a regra de negócio do controller (teste e reuso).                                               |
| Como funciona a autenticação?         | Sanctum com Personal Access Tokens (Bearer): login gera token, frontend guarda no localStorage, envia no header; logout revoga o token atual. |
| Quem garante que só o autor apaga?    | `PostService::delete` compara `user_id` e lança `AuthorizationException` → 403.                                                               |
| Como evita like duplicado?            | Dupla proteção: `unique(user_id, post_id)` no banco + `firstOrCreate`.                                                                        |
| E se o usuário não segue ninguém?     | Fallback no `getFeed`: sem `whereIn`, mostra todos os posts.                                                                                  |
| Por que axios instalado e não usado?  | `api.js` usa `fetch` nativo e centraliza token + erros. O axios sobra — pode ser removido.                                                    |
| Onde ficam os arquivos?               | Dev: disco `storage/app/public`. Prod: Supabase (driver S3) — filesystem do Render é efêmero.                                                 |
| Como valida os dados?                 | `$request->validate()` nos controllers → 422 com `{message, errors}`.                                                                         |
| Como o guard restaura a sessão?       | `beforeEach`: token sem user → `fetchUser()`; se falhar, logout e redireciona.                                                                |
| Como as migrations rodam em prod?     | No `CMD` do Dockerfile (`migrate --force`), pois o free não tem preDeployCommand.                                                             |
| Por que toda resposta de erro é JSON? | `shouldRenderJsonWhen($request->is('api/*'))` no `bootstrap/app.php`.                                                                         |
| Testes?                               | PHPUnit com SQLite `:memory:` (AuthTest) + Vitest no frontend.                                                                                |
| Por que tokens sem expiração?         | Simplicidade; logout revoga. Daria pra configurar no `config/sanctum.php`.                                                                    |

---

## Glossário rápido (para estudar sem medo)

**Analogia da pizza:** o **Dockerfile** é a receita, a **imagem** é a pizza congelada (idêntica em qualquer forno), o **container** é a pizza assada rodando na sua casa, e o **compose.yaml** é o balcão que faz várias pizzas juntas e liga o forno na ordem certa.

| Termo          | O que significa                                                 | No projeto                    |
| -------------- | --------------------------------------------------------------- | ----------------------------- |
| Volume nomeado | Pasta do container que sobrevive ao down                        | `mysql_data`                  |
| Bind mount     | Pasta do host "colada" no container                             | `./backend:/app`              |
| Healthcheck    | Teste de "estou vivo" para liberar o próximo                    | mysqladmin ping               |
| Multi-stage    | Dockerfile com 2+ FROM; o 1º compila, o 2º só copia o resultado | Node → nginx                  |
| Migration      | Script versionado que cria/altera o schema                      | `database/migrations/`        |
| Seeder         | Script que insere dados de teste                                | 10 usuários, senha `password` |
| ORM / Eloquent | Traduz tabelas em objetos: `Post::find(1)` vira `SELECT ...`    | Models                        |
| Middleware     | Roda ANTES do controller                                        | `auth:sanctum`                |
| Token Bearer   | Prova de identidade no header `Authorization`                   | Sanctum                       |
| CORS           | Regra do navegador que libera frontend → API                    | Laravel config                |
| Cold start     | Tempo de "acordar" do serviço dormindo (30–60s)                 | Render free                   |
| S3-compatible  | Protocolo de armazenamento do AWS usado por outros provedores   | Supabase                      |
| Blueprint      | `render.yaml`: infra como código                                | Deploy automático             |

**Quem é quem:** PHP = linguagem do backend · Laravel = framework PHP (rotas, ORM, migrations) · MySQL/TiDB = banco relacional (dev/cloud) · Vue.js = framework JS do frontend · Vite = build do frontend · Pinia = estado global · Vue Router = troca de tela sem reload · nginx = servidor web estático · Docker = containers · Swagger UI = documentação interativa da API.

---

## Check-list final

- [ ] `docker compose up --build` rodando na hora da demo (não estreie ao vivo).
- [ ] `php artisan migrate --seed` executado; senha dos usuários: `password`.
- [ ] Testar o fluxo completo 1× antes (criar post, like, comentário, follow, logout).
- [ ] Deixar o terminal do compose visível (mostra os logs).
- [ ] Não passar de 3 min no compose, 5 na demo.
- [ ] URL de produção aberta como plano B (cold start: abra antes!).
- [ ] 2 abas (2 usuários) para a demo de interação.
