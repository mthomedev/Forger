# Apresentação — Forger (Clone do Instagram)

**Stack:** Laravel 13 (API REST, padrão MSC) · Vue 3 + Vite + Pinia · MySQL 8.4 (dev) / TiDB Serverless (prod) · Supabase Storage (prod) · Docker Compose · Swagger UI · Render

---

## Roteiro (25 min)

| # | Etapa | Tempo |
|---|-------|-------|
| 1 | Compose.yaml (explicar e rodar) | 3 min |
| 2 | Dockerfiles (dev vs prod) | 4 min |
| 3 | Projeto rodando (demo) | 5 min |
| 4 | Swagger UI | 3 min |
| 5 | Fluxo de execução do código + função dos arquivos | 7 min |
| 6 | Sintaxe | 2 min |
| 7 | Hospedagem em produção (Render + TiDB + Supabase) | 2 min |
| 8 | Dúvidas da banca | 2 min |

---

## Glossário rápido (termos que a banca pode usar)

| Termo | O que significa |
|---|---|
| **Container** | "Caixa" isolada com uma aplicação + suas dependências (PHP, Node, MySQL...). Roda igual em qualquer máquina. |
| **Imagem** | O "molde" do container: o arquivo definido pelo Dockerfile. `docker build` cria a imagem; `docker run` roda o container a partir dela. |
| **Dockerfile** | Receita de como montar a imagem, linha por linha. |
| **Volume nomeado** | Pasta do container que vive fora dele (no host). Sobrevive a `docker compose down`. |
| **Bind mount** | Pasta do host "colada" dentro do container. Mudou no host, aparece no container (e vice-versa). |
| **Multi-stage build** | Dockerfile com 2+ estágios: o estágio 1 compila, o estágio 2 só copia o resultado. Imagem final pequena. |
| **Build arg** | Variável passada na hora do build (`ARG`). Diferente de `ENV` (variável de runtime). |
| **Healthcheck** | Teste que o Docker faz para saber se o serviço está "vivo" e pronto. |
| **Migration** | Script que cria/altera o schema do banco de forma versionada. `migrate` aplica; `migrate:fresh` apaga tudo e reaplica. |
| **Seeder** | Script que insere dados de teste no banco. |
| **ORM / Eloquent** | Camada que traduz tabelas SQL em objetos PHP. `Post::find(1)` vira `SELECT * FROM posts WHERE id = 1`. |
| **Middleware** | Camada que roda ANTES do controller. Ex.: `auth:sanctum` valida o token e rejeita (401) quem não tem. |
| **Token Bearer** | Código que o cliente envia no header `Authorization: Bearer <token>` para provar quem é. |
| **Cold start** | Tempo que um serviço "dormindo" leva para ligar de novo (30–60s no Render free). |
| **Filesystem efêmero** | Disco do container que é apagado a cada redeploy/restart (por isso os arquivos vão para o Supabase). |
| **S3-compatible** | API de armazenamento com o mesmo protocolo do AWS S3. Qualquer cliente S3 (Flysystem) funciona trocando endpoint + chaves. |
| **TLS/SSL** | Criptografia da conexão (HTTPS). O TiDB Serverless **exige** TLS nas conexões de banco. |
| **Blueprint** | Arquivo `render.yaml` que descreve os serviços do Render em código (Infraestrutura como código). |
| **CORS** | Regra do navegador que permite o frontend (origem X) chamar a API (origem Y) via fetch. |
| **Sync: false** | No render.yaml: a env var NÃO vem no arquivo; você preenche no painel (segredo não vai pro GitHub). |

---

## 1. Compose.yaml (3 min)

**Fala:** "O projeto inteiro sobe com um único arquivo: `compose.yaml` na raiz. Ele orquestra 3 serviços em uma rede Docker própria, com um volume nomeado para o banco."

```yaml
services:
  mysql:                        # banco MySQL 8.4
    container_name: forger-mysql
    networks: [forger-network]  # entra na rede privada do projeto
    image: mysql:8.4
    ports: ["3306:3306"]
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword   # senha do usuário root (obrigatória na imagem MySQL)
      MYSQL_DATABASE: forger              # banco criado automaticamente
      MYSQL_USER: forger                  # usuário de aplicação (não-root)
      MYSQL_PASSWORD: forger              # senha do usuário de aplicação
    volumes:
      - mysql_data:/var/lib/mysql         # volume nomeado = dados persistem
    healthcheck:                          # espera o MySQL aceitar conexão
      test: ["CMD", "mysqladmin", "ping", "-h", "127.0.0.1", "--protocol=tcp"]
      interval: 5s
      retries: 10
    restart: unless-stopped               # sobe de novo sozinho se o container cair

  backend:                      # API Laravel (Dockerfile.dev)
    container_name: forger-backend
    depends_on:
      mysql: { condition: service_healthy }  # só sobe quando o MySQL estiver pronto
    networks: [forger-network]
    build: { context: ./backend, dockerfile: Dockerfile.dev }
    ports: ["8000:8000"]
    volumes: [./backend:/app]             # bind mount = código vive fora do container
    restart: unless-stopped

  frontend:                     # Vue 3 (Dockerfile multi-stage)
    container_name: forger-frontend
    depends_on: [backend]                 # forma simples: só ordem de subida
    networks: [forger-network]
    build:
      context: ./frontend
      dockerfile: Dockerfile
      args: { VITE_API_URL: ${VITE_API_URL:-http://localhost:8000/api} }
    ports: ["5173:80"]                    # nginx dentro do container escuta na 80
    restart: unless-stopped

volumes:
  mysql_data:                   # declara o volume nomeado no nível raiz
networks:
  forger-network:               # declara a rede privada no nível raiz
```

**Por que de cada jeito (100% do arquivo):**
- **`image: mysql:8.4`** — o MySQL NÃO é instalado na sua máquina; o container é a "máquina virtual" do banco. `ports: ["3306:3306"]` = "porta do host:porta do container".
- **`environment` do MySQL**: a imagem oficial **exige** `MYSQL_ROOT_PASSWORD` (senão não sobe) e, com `MYSQL_DATABASE`/`MYSQL_USER`/`MYSQL_PASSWORD`, ela já cria o banco e o usuário de aplicação no primeiro boot — não precisa criar na mão. O Laravel usa o usuário `forger`, não o root (boa prática: app não roda como superusuário do banco).
- **`container_name`**: fixa o nome do container (ex.: `forger-mysql`). Sem ele, o Docker gera um nome aleatório (`forger-mysql-1`, etc.). Útil para `docker compose exec forger-backend ...`.
- **`networks: [forger-network]`**: rede privada criada só para esse projeto. Dentro dela, os containers conversam entre si **pelo nome do serviço** (o backend não precisa saber o IP do MySQL — resolve `mysql:3306`). Containers de outros projetos não enxergam essa rede (isolamento).
- **`depends_on` + healthcheck**: sem isso, o Laravel sobe primeiro e quebra com "connection refused" porque o MySQL ainda está inicializando. O healthcheck faz o MySQL responder "estou pronto" e só então o backend sobe. (No frontend, `depends_on: [backend]` é só ordem de subida — forma simples, sem condição.)
- **`healthcheck`**: o Docker executa `mysqladmin ping` a cada 5s, com até 10 tentativas, até o MySQL responder. Sem healthcheck, `condition: service_healthy` não tem como saber quando liberar o backend.
- **Volume nomeado `mysql_data`**: o MySQL guarda os dados em `/var/lib/mysql` DENTRO do container. Se o container for recriado sem volume, **os dados morrem**. O volume "salva" essa pasta no host. Detalhe: o volume **precisa ser declarado no nível raiz** (`volumes: mysql_data:`) — é aí que ele é registrado no Docker.
- **`restart: unless-stopped`**: se o container crashar (ou a máquina reiniciar), o Docker sobe ele de novo automaticamente — exceto se você mandou parar explicitamente. Em dev é conforto; em produção é essencial.
- **Bind mount `./backend:/app`**: em dev, o código PHP do seu PC é o mesmo que o container roda. Edita o arquivo → `artisan serve` recarrega na hora (é um servidor de dev). Em produção isso NÃO existe — o código é copiado na imagem (`COPY . .`).
- **`VITE_API_URL` como build arg**: o Vite "congela" essa variável dentro do JS no momento do build (não é lida em runtime). Por isso precisa ser passada na hora de buildar, e não quando o container roda. O `${VITE_API_URL:-...}` significa "use a variável do shell se existir; senão, use o default `http://localhost:8000/api`".
- **`networks:` no nível raiz**: assim como `volumes`, a rede também precisa de declaração no nível raiz para existir.
- **Frontend multi-stage**: o nginx não sabe rodar Node nem npm — então o estágio 1 (Node) builda o site e o estágio 2 (nginx) só serve os arquivos estáticos prontos. Imagem final: ~50MB em vez de ~1GB.

**Comandos (rodar ao vivo):**
```bash
docker compose up --build
docker compose exec backend php artisan migrate --seed
# acessar:
#   http://localhost:5173        (app)
#   http://localhost:8000/docs/docs.html (Swagger)
```

---

## 2. Dockerfiles (4 min)

### 2.0 Por que DOIS Dockerfiles (dev e produção)?

**Resposta curta:** os dois têm **objetivos opostos**, e o Dockerfile não tem como fazer "se estiver em dev, faça X; senão, Y" — então cada ambiente ganha um arquivo próprio.

**Dev (`Dockerfile.dev`) — a "oficina":**
- Prioridade: **velocidade de iteração**. Você muda código o tempo todo e quer ver o resultado sem rebuildar nada.
- Por isso: **bind mount** (o container roda o SEU código, que vive no host — edita, salva, o `artisan serve` recarrega), **dependências de dev instaladas** (PHPUnit, Faker — você roda testes dentro do container), **sem opcache** (se o PHP cacheasse o código compilado, você veria versões velhas), usuário mapeado para o SEU UID (senão arquivos criados pelo container ficam com dono root no host).
- O tamanho da imagem não importa. A rapidez de mudança importa.

**Prod (`Dockerfile`) — a "vitrine":**
- Prioridade: **performance, segurança, reprodutibilidade e tamanho**.
- Por isso: código **copiado e imutável** dentro da imagem (`COPY . .` — o que está rodando é exatamente o que foi buildado/testado), `composer install --no-dev` (menos pacotes = menos superfície de ataque + imagem menor), **opcache** (compila o PHP uma vez e mantém em memória — ganho enorme porque o PHP interpreta/compila o script a cada request), autoloader otimizado, `migrate` no boot e porta dinâmica `$PORT` (a nuvem decide a porta, não o projeto).
- Sem bind mount: em produção o código não pode ser editado por fora — a imagem é a unidade de deploy (rollback = voltar para a imagem anterior).

**Por que não um Dockerfile só?** As necessidades são quase opostas (cache sim/não, deps sim/não, código vivo/imutável...). O Dockerfile não tem `if`, então um arquivo único viraria um amontoado de truques. Dois arquivos = clareza. **Quem escolhe qual usar é o orquestrador**: o `compose.yaml` aponta `dockerfile: Dockerfile.dev` (dev local) e o `render.yaml` aponta `dockerfilePath: ./backend/Dockerfile` (produção). O mesmo projeto, dois contextos, dois arquivos.

### 2.1 `backend/Dockerfile.dev` — desenvolvimento

```dockerfile
FROM php:8.4-cli-trixie          # imagem base oficial do PHP com a CLI
ARG USER_ID=1000                 # parâmetro de build (defaults = seu usuário)
ARG GROUP_ID=1000
ENV PHP_CLI_SERVER_WORKERS=4     # php artisan serve roda 4 workers (mais rápido)

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer  # copia o Composer de outra imagem

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev ffmpeg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql zip bcmath gd exif \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN groupmod -g ${GROUP_ID} www-data \
    && usermod -u ${USER_ID} www-data     # roda como SEU usuário (arquivos não ficam "root")

WORKDIR /app
COPY . .
RUN composer install                      # baixa as dependências do PHP
COPY --chown=www-data:www-data . .        # corrige dono dos arquivos
USER www-data                             # nunca roda como root
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

**Explicação linha a linha:**
- `FROM php:8.4-cli-trixie` — "trixie" é o nome de código do **Debian 13** (a distribuição Linux que serve de base). `-cli` é a variante que traz o **binário `php` no PATH** (o servidor embutido é executado pela CLI). É uma variante menor que `-fpm`/`-apache`.
- `ARG USER_ID=1000` — parâmetro passado no build (`docker build --build-arg USER_ID=$(id -u)`). O compose.yaml do projeto passa o seu UID/GID reais; sem isso, o container roda como root e **todos os arquivos que ele criar (ex.: `storage/app/public/*`) ficam com dono root no host** — você não conseguiria apagá-los/editá-los depois. O `groupmod`/`usermod` muda o ID do usuário `www-data` (que já existe na imagem) para os SEUS números.
- `ENV PHP_CLI_SERVER_WORKERS=4` — o `php artisan serve` é o **servidor web embutido do PHP**: serve para dev, mas processa **1 requisição por vez**. Com `PHP_CLI_SERVER_WORKERS=4` ele sobe 4 processos-filhos → 4 requisições simultâneas (o frontend carrega muitos arquivos; sem isso, o site demora).
- `COPY --from=composer:2 ...` — puxa o binário do Composer da **imagem oficial** dele em vez de baixar manualmente (multi-stage em miniatura: usa outra imagem como fonte de arquivo).
- `apt-get install libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev` — bibliotecas **nativas (C)** que a extensão GD precisa para **processar imagens** (JPEG, PNG, WebP, fontes). O PHP não traz essas libs; é o sistema operacional que fornece. `libzip-dev` → suporte a arquivos `.zip`; `ffmpeg` → processar vídeo (não é usado pela app — honesto: poderia sair da imagem).
- `docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp` — configura a extensão GD avisando **quais formatos/lib ele deve compilar junto**; sem isso, `imagecreatefromjpeg()` etc. falham em runtime.
- `docker-php-ext-install pdo_mysql zip bcmath gd exif` — o jeito oficial de instalar **extensões do PHP**. `pdo_mysql` é a que faz a aplicação falar com o MySQL (sem ela: "could not find driver").
- `WORKDIR /app` — define o diretório de trabalho; todos os comandos seguintes rodam lá.
- `COPY . .` + `RUN composer install` + `COPY --chown=www-data:www-data . .` — o primeiro COPY é necessário para o `composer install` encontrar o `composer.json` e gerar o `vendor/`. O segundo COPY (repetido de propósito) **corrige o dono** de tudo para `www-data` (o primeiro copiou como root; o `vendor/` gerado também ficaria root).
- `USER www-data` — **segurança básica de container**: nunca rodar processo como root. Se o Laravel for comprometido, o atacante não tem acesso total ao sistema.
- `CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]` — `artisan serve` roda o servidor embutido do PHP (o mesmo que `php -S`). `--host=0.0.0.0` é obrigatório em Docker: por padrão ele escuta só em `127.0.0.1` (loopback interno), e o mapeamento de porta do compose só funciona se o processo escutar em todas as interfaces.

### 2.2 `backend/Dockerfile` — produção (Render)

```dockerfile
FROM php:8.4-fpm
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev ffmpeg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql zip bcmath opcache gd exif \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader   # SEM dependências de dev + autoload otimizado

EXPOSE 8000
CMD ["sh", "-c", "php artisan migrate --force --no-interaction && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
```

**Diferenças para o dev, uma a uma:**
- **Base `php:8.4-fpm`** — variante com o **FPM** (FastCGI Process Manager): o jeito profissional de rodar PHP com nginx. Nota honesta: mesmo assim o `CMD` roda `artisan serve` (servidor embutido), porque no Render free não há nginx configurando o socket do FPM — a base `-fpm` garante que o binário exista e o `CMD` pode ser trocado por nginx+fpm no futuro sem rebuild da imagem.
- **`opcache` na lista de extensões** — o PHP compila o script **a cada request** (interpretação → bytecode → execução). O opcache guarda esse bytecode **em memória** → respostas 2–3× mais rápidas. No dev ele é proibido (você alteraria arquivos e o PHP serviria a versão velha em cache).
- **`composer install --no-dev`** — não instala PHPUnit, Faker, Mockery etc.: imagem menor, menos código para explorar, deploy mais rápido. Só o que roda em produção (`--optimize-autoloader` pré-computa o mapeamento de classes → carrega mais rápido).
- **`migrate --force` no CMD** — o plano free do Render **não tem `preDeployCommand`** (comando pré-deploy, exclusivo de plano pago). A solução: rodar a migration junto com o start. É **idempotente**: se o banco já tem as tabelas, não faz nada; se não tem, cria. O `--force` é obrigatório fora do ambiente local (sem ele o artisan pergunta "você tem certeza?" e trava). `${PORT:-8000}` = "use a variável de ambiente `$PORT` que o Render injeta; se não existir, use 8000".
- **Sem bind mount, sem USER mapeado** — o código é copiado (`COPY . .`) e a imagem é a unidade de deploy; rollback = voltar a uma imagem anterior. Em produção ninguém edita arquivos do container.
- **Detalhe honesto (pode virar elogio na banca):** o `COPY . .` vem ANTES do `composer install` — o ideal seria copiar só `composer.json`/`composer.lock` primeiro, rodar o install (camada cacheada), e copiar o resto depois. Assim, mudanças de código não forçariam o composer a reinstalar dependências. (O Dockerfile do frontend faz isso direito — ver 2.3.)

### 2.3 `frontend/Dockerfile` — build multi-stage

```dockerfile
# ── Estágio 1: build ─────────────────────────────
FROM node:22-alpine AS builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci                          # instala deps de forma reproduzível (npm ci, não npm install)
COPY . .
ARG VITE_API_URL=${VITE_API_URL:-http://localhost:8000/api}
ENV VITE_API_URL=${VITE_API_URL}
RUN npm run build                   # gera o dist/ (HTML + JS + CSS estáticos)

# ── Estágio 2: servidor web ───────────────────────
FROM nginx:stable-alpine
RUN rm -rf /usr/share/nginx/html/*
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

**Por que multi-stage:** o estágio 1 precisa de Node + npm (~1GB de imagem). O estágio 2 só copia o resultado (`dist/`) para dentro do nginx (~50MB). O build é "descartável". Note também: **`node:22-alpine`** é a versão Alpine do Node — uma distribuição Linux minúscula (base ~5MB), usada sempre que possível para encolher imagens.

**Ordem das COPYs = layer caching (padrão "certo", que o backend não segue):**
1. `COPY package.json package-lock.json ./` → `RUN npm ci`: essas linhas só mudam quando **as dependências** mudam.
2. `COPY . .` → `npm run build`: só copia o **código** novo.

O Docker cacheia cada camada: se você mexeu só no código, o cache da camada `npm ci` é reaproveitado (as deps não são baixadas de novo) → rebuild em segundos. Se `npm ci` viesse depois do `COPY . .`, qualquer alteração de código reinstalaria tudo.

**`npm ci` vs `npm install`:** o `npm install` pode atualizar versões e até reescrever o lock file (builds não-reproduzíveis). O `npm ci` **apaga `node_modules` e instala exatamente o que o `package-lock.json` diz** — o mesmo resultado em qualquer máquina. Em produção (CI, Docker) sempre `npm ci`.

**`ARG` + `ENV` com o mesmo valor:** a `ARG` é uma variável **só de build** (não entra na imagem final); o `ENV` persiste. O `npm run build` precisa do `VITE_API_URL` em `process.env` para o Vite "colar" a URL da API dentro do bundle (`import.meta.env.VITE_API_URL`). A `ARG` já fica disponível para os `RUN` do estágio; o `ENV` reforça/explicita para o processo de build. No deploy, o Render passa `VITE_API_URL` como build arg do serviço web; localmente usa o default `http://localhost:8000/api`.

**`nginx.conf` — por que existe:**
```nginx
server {
    listen 80;
    root /usr/share/nginx/html;
    location ~* \.(js|css|png|...)$ { expires 1y; ... }  # arquivos estáticos: cache de 1 ano
    location / { try_files $uri $uri/ /index.html; }      # qualquer outra rota → index.html
}
```
O Vue Router usa **HTML5 history mode** (URLs limpas como `/profile`, sem `#`). Quando o usuário recarrega `https://site.com/profile`, o servidor recebe um pedido por uma rota que não existe como arquivo — sem o `try_files ... /index.html`, o nginx responderia 404. Ele "empurra" tudo para o `index.html` e o Vue Router decide qual página renderizar.

**`CMD ["nginx", "-g", "daemon off;"]` — o detalhe que mantém o container vivo:** o nginx, por padrão, roda como **daemon** (processo em segundo plano). O Docker mata o container quando o **processo principal** (PID 1) termina — se o nginx virar daemon, o PID 1 "morre" na hora e o container crasha. `daemon off;` força o nginx a ficar em **foreground** (processo principal vivo = container vivo). O `rm -rf` apaga a página de boas-vindas padrão que vem na imagem; o `COPY nginx.conf` substitui o config default.

**`.dockerignore`** — exclui do build: `node_modules`, `dist`, `.env` (segredos não entram na imagem!), `.git`.

### 2.4 Tabela de oposições — dev vs prod (decore esta)

| | Dev (`Dockerfile.dev`) | Prod (`Dockerfile`) |
|---|---|---|
| **Objetivo** | Velocidade de iteração | Performance + segurança + tamanho |
| **Código** | Bind mount (edita sem rebuild) | `COPY` (imutável na imagem) |
| **Dependências** | Com dev (PHPUnit/Faker) | `--no-dev` |
| **Cache do PHP** | Sem opcache (mudança imediata) | `opcache` (bytecode em memória) |
| **Servidor** | `artisan serve` (embutido) | `migrate --force` + `artisan serve` na porta `$PORT` |
| **Usuário do processo** | `www-data` com UID/GID do host | `www-data` padrão (root nunca) |
| **Tamanho da imagem** | Irrelevante | Enxuta (Alpine, `--no-dev`) |
| **Quem escolhe o arquivo** | `compose.yaml` (`dockerfile: Dockerfile.dev`) | `render.yaml` (`dockerfilePath: ./backend/Dockerfile`) |

**Frase pronta para a banca:** "o Dockerfile de dev é a oficina — ferramentas na mesa, peças abertas, ajuste fino; o de produção é a vitrine — pronta, segura e enxuta. O mesmo projeto, dois arquivos, porque os objetivos são opostos e o Dockerfile não tem condicional."

### 2.5 Técnicas de build que a banca pode perguntar

1. **Layer caching** — cada instrução vira uma camada imutável; Docker reutiliza camadas cujo input não mudou. Por isso a ordem é otimizada (deps antes do código).
2. **Multi-stage** — um Dockerfile com vários `FROM`; estágios anteriores são descartáveis, só o último vira imagem final (Node→nginx, composer→php).
3. **`COPY --from=...`** — copiar arquivos de outra imagem (ex.: binário do Composer, ou `dist/` do estágio builder).
4. **Imagens Alpine** — distribuição Linux de ~5MB (vs ~50MB do Debian) para base de node/nginx.
5. **`.dockerignore`** — o análogo do `.gitignore` para o build: impede que `node_modules`/`vendor`/`.env` sejam mandados para o contexto de build (imagem menor e sem segredos).
6. **Determinismo** — `npm ci` + `package-lock.json` + `composer.lock` = o mesmo build em qualquer máquina/dia.

---

## 3. Projeto rodando — demo (5 min)

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

## 4. Swagger UI (3 min)

**URL (dev):** `http://localhost:8000/docs/docs.html` · **URL (prod):** `https://forger-api.onrender.com/docs/docs.html`

**Como está implementado:** não é um pacote (l5-swagger/scribe) — é um arquivo estático:
- `backend/public/docs/docs.html` → a interface do Swagger UI (CSS/JS via CDN).
- `backend/public/docs/openapi.yaml` → a especificação OpenAPI 3.0.3 escrita à mão (931 linhas, 7 tags).

```yaml
openapi: 3.0.3
info: { title: Forger API, version: "1.0.0" }
servers: [{ url: /api }]             # base de todas as rotas
security: [{ bearerAuth: [] }]       # TODAS as rotas exigem token por padrão
tags: [Auth, Profile, Posts, Likes, Comments, Follow, Users]
paths:
  /register:
    post:
      security: []                   # exceção: rota pública
      requestBody: { ... }           # corpo esperado (validação)
      responses: { "201": ..., "422": ... }   # respostas possíveis
components:
  securitySchemes:
    bearerAuth: { type: http, scheme: bearer }
```

**Demo (ao vivo):**
1. Abrir `POST /register`, clicar em "Try it out", preencher `{name, email, password, password_confirmation}`, executar → resposta `201` com `access_token`.
2. Copiar o token, clicar em **Authorize** (cadeado) e colar como `Bearer <token>`.
3. Abrir `GET /posts` → executar → mostrar o `200` com `data`, `current_page`, `last_page` (paginação).

**Frase pronta:** "Toda a API está documentada e testável pela interface: cada rota tem corpo de requisição, esquemas de resposta e códigos HTTP coerentes — 201 ao criar, 422 em validação, 401 sem token, 403 ao tentar excluir post de outro usuário."

---

## 5. Fluxo de execução do código (7 min)

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
MySQL 8.4 (dev) / TiDB (prod)
```

**A pergunta da banca é sempre "por que essa camada existe?":**
- **Router** — mapa URL → função. Sem ele, cada URL precisaria de um `if` manual.
- **Middleware** — segurança transversal: valida o token ANTES de qualquer controller rodar (1 só lugar, todas as rotas do grupo).
- **Controller** — "porteiro": recebe a request, valida os dados (422 se inválidos), chama o Service, devolve JSON com status correto. **Não tem regra de negócio** (por isso é "fino").
- **Service** — "cérebro": aqui mora a regra (quem pode deletar, o que acontece com o like, upload...). Reusável por outras portas de entrada (jobs, comandos).
- **Model** — "tradutor": vira SQL para objeto. `$post->user` dispara um JOIN automático.

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

**`bootstrap/app.php`** — o "startup" do Laravel 11+ (configuração sem `config/app.php`):
- `withRouting` — registra onde ficam as rotas web, api, console e a rota de health check `/up` (o Render pode pingar).
- `shouldRenderJsonWhen(fn ($request) => $request->is('api/*'))` — **todo erro na API vira JSON** (ex.: 404, 500) em vez de página HTML de erro. É isso que mantém o contrato de resposta consistente para o frontend.

**Services** (`app/Services/`) — a regra de negócio:
- `AuthService` — registro (cria usuário + token), login (`Hash::check`), logout (revoga token atual).
  - Detalhe: se o usuário não envia `username`, o registro gera automaticamente: `Str::slug($name) . rand(100, 999)` (ex.: "Maria Silva" → `maria-silva742`). É por isso que todo usuário tem username mesmo sem preencher.
  - Detalhe: a senha é criptografada pelo **cast `hashed`** do model (`User.php`), não manualmente com `Hash::make` no service. O login usa `Hash::check` para comparar sem revelar a senha.
  - Detalhe: o logout deleta o token **atual** da tabela `personal_access_tokens` — revogar = deletar a linha. O token anterior continua valendo até ser revogado (por isso tokens são como "cartões de acesso" individuais).
- `PostService::getFeed` (linha 31-52) — feed: pega os IDs de quem você segue, junta com o seu, e **se não segue ninguém, mostra todos os posts** (fallback); paginação + flag `is_liked` em cada post.
- `PostService::delete` (linha 64-78) — **autorização por ownership**: se `$post->user_id !== $user->id`, lança `AuthorizationException`; também limpa likes/comments e apaga a imagem do storage.
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

**Seeder** (`database/seeders/DatabaseSeeder.php`) — os dados de teste, em 5 etapas:
1. **10 usuários** com nome/username/email fake (`Faker`) e senha **`password`** (todas iguais — fácil para a demo).
2. **3–5 posts por usuário** (`rand(3,5)`), imagem do picsum.photos (URL absoluta — por isso o accessor aceita `http...`).
3. **Follows aleatórios** (cada usuário segue 3–7 outros), com verificação para não violar o `unique` da tabela.
4. **Likes de ~30% dos posts** por usuário.
5. **1–3 comentários por post** de usuários aleatórios.

> Nota: os follows/likes/comments são inseridos com `DB::table()->insert()` (query builder) porque o Eloquent espera models; no seeder, o query builder direto é mais rápido e simples.

**Sobre rotas públicas** — durante o desenvolvimento existiram sobras (`GET /test-user`, `GET /profile/{username}`, e `GET /users/{userId}/posts` fora do grupo auth). Antes da entrega elas foram **corrigidas**: `/test-user` e `/profile/{username}` (não usadas pelo frontend) foram **removidas**, e `/users/{userId}/posts` foi **movida para dentro do grupo `auth:sanctum`**. Hoje, exceto `register` e `login`, **todas as rotas exigem autenticação** — exatamente como o Context.md pede ("Exceto login e registro, todas as funcionalidades da aplicação exigem autenticação"). Isso é um ponto a citar: a auditoria de qualidade pegou e corrigiu código morto.

**Frontend (`frontend/src/`):**
- `main.js` → bootstrap: cria app Vue, instala Pinia e o Router.
- `router/index.js` → rotas com `meta.requiresAuth`/`meta.guestOnly` + **guard global** `beforeEach`: se tem token mas não tem user, chama `fetchUser()` (valida o token); redireciona deslogados para `/login` e logados longe de `/login`. Lazy loading de todas as views. `afterEach` define o título da aba (`document.title`) e `scrollBehavior` volta ao topo ao trocar de página.
- `stores/auth.js` (Pinia) → `user`, `token`, `isAuthenticated`, ações `register/login/fetchUser/logout`. `stores/ui.js` → controla o modal de criar post e propaga o post criado para as views.
- `services/api.js` → **wrapper próprio de `fetch`** (o axios está no package.json mas não é usado): injeta `Authorization: Bearer` do `localStorage`, serializa JSON ou FormData, extrai `message`/`errors` do padrão Laravel.
- `services/*` → um service por domínio (auth, post, profile, user).
- `views/` → 7 páginas (Home, Login, Register, Post, Profile, Search, UserProfile) + `layouts/` (MainLayout autenticado com NavBar/Footer/Modal; AuthLayout para login/registro).
- `components/common/` → design system próprio (BaseButton, BaseInput, BaseModal com Teleport, BaseAvatar) + design tokens em `App.vue` (CSS variables, tema dark).
- `utils/media.js` → "gêmeo" dos accessors do backend: se a URL já é absoluta (`http...`), usa como está; senão monta `API_URL/storage/<path>`. Na prática a API sempre devolve URL absoluta (`image_url`/`avatar_url`), então o fallback raramente roda. `BaseAvatar` ainda usa ui-avatars.com como fallback de avatar (inicial do nome) quando não há foto.
- `utils/time.js` → `timeAgo()` formata "5m ago", "2h ago", "3d ago" para timestamps da API; `formatDateTime()` para a data completa.

**Exemplo de um request completo (para narrar):**
> "O usuário curte um post. O Vue em `PostCard.vue` chama `postService.toggleLike(postId)`. O `api.js` monta um `fetch POST /api/posts/5/like` com o Bearer token. A rota em `api.php` exige `auth:sanctum` → o middleware valida o token na tabela `personal_access_tokens`. O `LikeController::toggle` delega ao `LikeService::toggle`, que faz `firstOrCreate`/`delete`. Retorna `200` com o novo estado. O Vue atualiza o ícone e o contador reativamente."

### O ciclo de vida de uma requisição Laravel (para a banca de arquitetura)

```
1. public/index.php          → ponto de entrada único (todo pedido passa aqui)
2. bootstrap/app.php         → cria a aplicação, registra rotas/middleware/erros
3. Middleware global         → CORS, parse do body, confiança de proxies...
4. Roteador (api.php)        → encontra a rota + valida o path e o método
5. Middleware da rota        → auth:sanctum: busca o token, carrega o usuário (401 se inválido)
6. Controller                → $request->validate() (422 se inválido) → chama o Service
7. Service                   → regra de negócio (usa Eloquent/Storage/DB)
8. Model → SQL → banco       → Eloquent traduz, executa, hidrata objetos
9. response()->json()        → volta pelo mesmo caminho até o navegador
```

**Segundo exemplo de ponta a ponta (follow, para variar o discurso):**
> "Sigo o usuário Maria. `UserProfileView.vue` chama `userService.toggleFollow(userId)` → `POST /api/users/3/follow` com Bearer. `FollowController::toggle` delega ao `FollowService::toggle`, que primeiro **verifica se eu não estou me seguindo** (retorna sem fazer nada se `follower->id === target->id`), depois consulta a tabela `follows` com o query builder: se a relação existe, deleta (deixar de seguir); se não, insere (seguir). Retorna `{following: true, followers_count: 42}` — o Vue atualiza o botão para 'Seguindo' e o contador do perfil reativamente. O banco tem `unique(follower_id, followed_id)`, então a dupla nunca duplica."

---

## 6. Sintaxe (2 min)

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

## 7. Hospedagem em produção (2 min)

### Arquitetura

```
Navegador
   │ https://forger-web.onrender.com
   ▼
Render — forger-web (nginx + SPA Vue)
   │ fetch HTTPS → https://forger-api.onrender.com/api
   ▼
Render — forger-api (Laravel + Sanctum)
   ├── MySQL protocol (porta 4000, TLS) → TiDB Cloud Serverless (banco)
   └── S3 API (HTTPS) → Supabase Storage (imagens de posts e avatares)
```

**Por que essa divisão:**
| Peça | Papel | Por quê |
|---|---|---|
| **Render** | Hospeda os 2 serviços (web + API) via Docker | Deploy automático pelo GitHub (Blueprint) |
| **TiDB Cloud Serverless** | Banco de dados MySQL-compatível | Render **não tem MySQL gerenciado**; rodar MySQL como container no Render exige plano pago (private service + disk). O TiDB é grátis, não dorme e aceita o código 100% MySQL |
| **Supabase Storage** | Armazenamento das imagens (S3-compatível) | O filesystem do Render free é **efêmero** (imagens sumiriam a cada redeploy). O Supabase é grátis, sem cartão, e fala o protocolo S3 que o Laravel já usa |

### O `render.yaml` (Blueprint) explicado

```yaml
services:
  - type: web                  # serviço acessível pela internet
    name: forger-api
    runtime: docker            # builda o Dockerfile
    plan: free
    region: oregon
    dockerContext: ./backend   # contexto do build (raiz do Dockerfile)
    dockerfilePath: ./backend/Dockerfile
    envVars:
      - key: APP_ENV
        value: production          # valor fixo, versionado
      - key: APP_KEY
        generateValue: true        # Render gera um segredo aleatório
      - key: DB_PASSWORD
        sync: false                # segredo: preenche no painel, NÃO vai pro GitHub
```

**Os 3 tipos de env var:**
- `value:` — fixa no código (não é segredo).
- `generateValue: true` — o Render gera um valor aleatório na primeira vez (APP_KEY é a chave de criptografia do Laravel; sem ela, sessões/tokens quebram).
- `sync: false` — o valor é preenchido manualmente no painel (senhas e chaves não podem ir pro GitHub).

### Decisões técnicas da hospedagem

1. **Sem `preDeployCommand` / `startCommand`**: o plano free não aceita. Migrations rodam no `CMD` do Dockerfile (`migrate --force && serve`). Idempotente: roda a cada boot sem apagar dados.
2. **`DB_PORT=4000`**: porta padrão do TiDB Serverless (MySQL local usa 3306).
3. **`MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt`**: o TiDB **exige TLS**. O Laravel lê essa env var no `config/database.php` e usa o certificado do sistema do container para criptografar a conexão (verificado pela CA pública).
4. **TiDB não tem lista de IPs no Serverless** — aceita qualquer origem (a segurança é senha + TLS). Por isso não precisou configurar nada no painel do TiDB.
5. **Supabase no lugar do R2**: o Cloudflare R2 exige cartão em arquivo; o Supabase Storage não. Só mudaram as env vars `AWS_*` (mesmo driver S3 do Flysystem — **zero mudança de código**).
6. **CORS**: o navegador bloqueia fetch entre origens diferentes. O Laravel 11+ já vem com middleware CORS liberando `api/*` para qualquer origem (o token Bearer não usa cookies, então não precisa de `credentials`).
7. **Cold start / keep-alive**: o free tier dorme após 15 min sem tráfego e demora 30–60s para acordar. Para a demo, pingue o site a cada 10 min com o cron-job.org (grátis).
8. **750 horas/mês** por workspace: com 2 serviços dormindo quase sempre, não estoura.

### Verificação feita pós-deploy (para citar na banca)

- `POST /api/register` → 201 com token (prova que TiDB + migrations funcionam).
- `GET /api/profile` (com token do registro) → perfil com contagens (prova a leitura no banco).
- Swagger disponível em produção.
- Upload de imagem → arquivo aparece no bucket do Supabase.

---

## 8. Dúvidas da banca (previsões + respostas)

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
| Onde os arquivos (avatar/posts) ficam? | Dev: disco `public` (`storage/app/public`). Prod: Supabase Storage via driver S3 (`FILESYSTEM_DISK=s3`) — porque o filesystem do Render free é efêmero. O avatar antigo é deletado ao trocar. |
| Como você hospedou? | Render Blueprint (render.yaml) com 2 serviços Docker; banco no TiDB Serverless (MySQL-compatível, TLS, porta 4000); imagens no Supabase Storage (S3-compatível, bucket público). |
| Por que não MySQL no Render? | O Render não tem MySQL gerenciado; a forma oficial (private service + disk) é paga e o free tier não tem disco persistente. |
| Como as migrations rodam em produção? | No `CMD` do Dockerfile: `php artisan migrate --force` antes do `serve` (o free tier não tem preDeployCommand). |
| Como valida os dados? | `$request->validate()` inline nos controllers (rules do Laravel). Erros retornam 422 com `{message, errors}` automaticamente. |
| Por que tokens sem expiração? | Simplicidade; logout revoga. Em produção real, daria para configurar expiração no `config/sanctum.php`. |
| Como o guard de rotas restaura a sessão? | No `beforeEach`: token existe + user vazio → `fetchUser()`; se falhar (token inválido), `logout()` e redireciona. |
| Existem rotas públicas além de login/registro? | Não. Havia sobras de desenvolvimento (`/test-user`, `/profile/{username}`) que foram **removidas** na auditoria de qualidade, e `/users/{userId}/posts` foi movida para dentro do grupo `auth:sanctum`. Hoje só `register` e `login` são públicas, conforme o Context.md. |
| Por que toda resposta de erro da API é JSON? | `bootstrap/app.php` tem `shouldRenderJsonWhen($request->is('api/*'))` — erros nunca viram HTML, o frontend sempre recebe `{message, errors}`. |
| Testes? | PHPUnit com SQLite `:memory:` (`AuthTest` cobre registro/login; roda com `composer test`). Vitest no frontend (`App.spec.js`, `npm run test:unit`). |

---

## Check-list final antes de apresentar

- [ ] `docker compose up --build` rodando na hora da demo (não rode a demo pela primeira vez ao vivo).
- [ ] `php artisan migrate --seed` executado (usuários de teste criados).
- [ ] Testar o fluxo completo 1x antes (criar post, like, comentário, follow, logout).
- [ ] Saber a senha dos usuários do seeder de cor (ou logar criando um usuário novo).
- [ ] Fechar outras abas; deixar o terminal do compose visível para mostrar os logs.
- [ ] Timers mentais: não passar de 3 min no compose, 5 na demo.
- [ ] Ter a URL de produção aberta como "plano B" caso o Docker falhe no dia (cold start de 30–60s: abra antes!).
