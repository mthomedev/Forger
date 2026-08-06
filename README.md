# Forger — Instagram Clone

Aplicação web desenvolvida como projeto final, inspirada na experiência de navegação e interação social do Instagram. O sistema é composto por uma API REST em Laravel organizada no padrão MSC (Model–Service–Controller) e um frontend SPA em Vue 3.

## Tecnologias

- **Backend:** Laravel 13 (PHP 8.4) • Laravel Sanctum (autenticação Bearer) • Padrão MSC
- **Banco de Dados:** MySQL 8.4 (Docker) / TiDB Cloud Serverless em produção
- **Frontend:** Vue 3 (Composition API) • Vite • Pinia • Vue Router
- **Armazenamento:** Disco local (dev) / Supabase Storage (produção, S3-compatível)
- **Documentação:** Swagger UI (OpenAPI 3.0)
- **Infraestrutura:** Docker & Docker Compose • Render (produção)

## Estrutura do Projeto

```text
Forger/
├── backend/                  # API REST em Laravel (MSC)
│   ├── app/Http/Controllers/ # Controllers finos (validação + delegação)
│   ├── app/Services/         # Regras de negócio
│   ├── app/Models/           # Entidades e relacionamentos Eloquent
│   ├── database/             # Migrations, factories e seeders
│   ├── public/docs/          # Swagger UI (docs.html + openapi.yaml)
│   └── routes/api.php        # Todas as rotas da API
├── frontend/                 # SPA em Vue 3
│   ├── src/views/            # Páginas (Home, Profile, Search, ...)
│   ├── src/components/       # Componentes e design system (Base*)
│   ├── src/stores/           # Pinia (auth, ui)
│   ├── src/services/         # Camada HTTP (fetch + token)
│   └── src/router/           # Rotas e guards de autenticação
├── compose.yaml              # MySQL + Backend + Frontend (dev)
├── render.yaml               # Blueprint de produção no Render
├── Deploy.md                 # Guia completo de deploy
└── Context.md                # Especificação do projeto
```

## Como executar (Docker)

```bash
docker compose up --build
```

Depois de subir, dentro do container do backend:

```bash
docker compose exec backend php artisan migrate --seed
```

Acesse:

- **App:** http://localhost:5173
- **API:** http://localhost:8000/api
- **Swagger UI:** http://localhost:8000/docs/docs.html

### Credenciais do seeder

Todos os usuários gerados pelo seeder usam a senha `password` (e-mails aleatórios do faker).

## Testes

```bash
docker compose exec backend php artisan test   # PHPUnit (SQLite :memory:)
cd frontend && npm run test:unit               # Vitest
```

## Funcionalidades

- Autenticação: registro, login e logout via Sanctum (tokens Bearer).
- Home/feed: posts de quem você segue, curtidas, comentários e sugestões de usuários.
- Perfil próprio: edição de bio/nome/username, upload de avatar, grade de posts e exclusão de posts.
- Perfil de outros usuários: seguir/deixar de seguir com contadores atualizados.
- Busca por nome ou username com debounce.
- Navegação com Vue Router (rotas protegidas e redirecionamento de rotas inexistentes).

## Deploy em produção

Veja o guia completo em [Deploy.md](Deploy.md) — TiDB Cloud (MySQL-compatível), Supabase Storage (S3-compatível) e Render Blueprint.
