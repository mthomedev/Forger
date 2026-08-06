# 📸 Forger — Instagram Clone

Aplicação web desenvolvida como projeto final, inspirada na experiência de navegação e interação social do Instagram. O sistema é composto por uma API REST em Laravel utilizando o padrão MSC e um Frontend moderno em Vue 3.

---

## 🚀 Tecnologias Principais

- **Backend:** Laravel 12 (PHP 8.4) • Laravel Sanctum (Autenticação Bearer) • Padrão MSC (Model–Service–Controller)
- **Banco de Dados:** MySQL 8.4
- **Frontend:** Vue 3 (Composition API) • Vite • Pinia (State Management) • Vue Router
- **Infraestrutura:** Docker & Docker Compose

---

## 📂 Estrutura do Projeto

```text
Forger/
├── backend/               # API REST em Laravel
│   ├── app/               # Models, Controllers, Services
│   ├── database/          # Migrations e Seeders
│   ├── routes/            # api.php e web.php
│   └── compose.yaml       # Docker Compose do Backend + MySQL
├── frontend/              # Aplicação SPA em Vue 3
│   ├── src/               # Views, Components, Stores, Router
│   └── compose.yaml       # Docker Compose do Frontend (Nginx)
└── Roadmap.md             # Guia de estudos e evolução

🛠️ Como Executar o Projeto com Docker
O projeto utiliza composições Docker isoladas para o backend e frontend, conforme exigido pela especificação.
1. Backend & Banco de Dados
Na raiz do projeto, execute o Docker Compose do backend:
docker compose -f backend/compose.yaml up -d --build
Isso irá subir:
- MySQL 8.4 na porta 3306
- Laravel API na porta 8000 (http://localhost:8000/api)
Executando Migrations e Seeders:
Para popular o banco de dados com usuários de teste, posts, likes e comentários:
docker exec -it forger-backend php artisan migrate:fresh --seed
2. Frontend
Navegue até a pasta do frontend ou execute o compose correspondente:
Opção A (Via Docker Compose):
docker compose -f frontend/compose.yaml up -d --build
Acesse em: http://localhost:5173
Opção B (Modo Desenvolvimento local com Vite):
cd frontend
npm install
npm run dev
Acesse em: http://localhost:5173
🔑 Credenciais de Acesso (Seeders)
Você pode logar com qualquer um dos usuários gerados pelo seeder. Todos utilizam a mesma senha padrão:
- E-mail de exemplo: test@example.com
- Senha: password
🧪 Testes Automatizados
Para executar os testes de feature e unitários do backend:
docker exec -it forger-backend php artisan test
Para executar os testes unitários do frontend:
cd frontend
npm run test:unit
📋 Funcionalidades Implementadas
- Autenticação: Registro, Login e Logout via Laravel Sanctum.
- Home / Feed: Listagem de posts de quem você segue, curtidas e comentários em tempo real.
- Perfil Próprio: Visualização de posts em grade, edição de bio/nome/username e upload de avatar.
- Perfil de Outros Usuários: Acompanhamento de seguidores/seguindo e botão de Seguir / Deixar de seguir.
- Busca: Pesquisa por nome ou username de usuários.
- Segurança: Rotas protegidas por token e restrição de exclusão de posts apenas ao autor.
```
