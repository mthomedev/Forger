# Deploy do Forger no Render (guia passo a passo)

**Stack em produção:** Laravel API (Render, Docker) · Vue 3 (Render, Docker/nginx) · TiDB Cloud Serverless (MySQL-compatível) · Supabase Storage (uploads, S3-compatível) · Swagger UI

**URLs finais (exemplo):**

- Frontend: `https://forger-web.onrender.com`
- API: `https://forger-api.onrender.com/api`
- Swagger: `https://forger-api.onrender.com/docs/docs.html`

> Pré-requisito: o repositório já está no GitHub (`origin` configurado) e você tem conta no Render.

---

## Passo 1 — TiDB Cloud (banco de dados)

O Render não tem MySQL gerenciado gratuito, então o banco roda no **TiDB Cloud Serverless** — um MySQL 8.0-compatível com plano gratuito (5GB, sem cartão, não dorme).

1. Acesse https://tidbcloud.com e crie a conta (login com Google/GitHub; sem cartão).
2. **Create Cluster** → escolha **Serverless** → região mais próxima (ex.: `us-east-1`) → **Free plan** → crie.
3. Ao terminar, clique em **Connect** no cluster:
   - Copie o **host** (algo como `gateway01.xxxx.aws.tidbcloud.com`) e **port 4000**.
   - Copie o **username** (formato `xxxx.root`) e crie/pegue a **senha**.
   - O banco padrão criado se chama `test` — anote o nome ou crie um (`forger`).
4. Na aba **Networking** do cluster: adicione acesso **0.0.0.0/0** (ou "Allow access from anywhere"). O Render free não tem IP de saída fixo.
5. **Teste de conexão local** (se tiver cliente MySQL):
   ```bash
   mysql -h <host> -P 4000 -u <user> -p -D test
   ```

**Anote para o Passo 4:**

```
DB_HOST=<host-do-tidb>
DB_PORT=4000
DB_DATABASE=test
DB_USERNAME=<user.ex>

> O TiDB Serverless **exige TLS**. O Laravel já lê `MYSQL_ATTR_SSL_CA` do
> `config/database.php` — no Render vamos apontá-lo para o CA bundle do
> container (`/etc/ssl/certs/ca-certificates.crt`, já adicionado ao render.yaml).
```

---

## Passo 2 — Supabase Storage (upload de avatares e posts)

O backend usa `FILESYSTEM_DISK=s3` em produção. O Supabase Storage é S3-compatível e o plano gratuito (1GB) **não exige cartão**.

1. Acesse https://supabase.com e crie a conta com **GitHub ou Google** (sem cartão).
2. **New Project** → dê um nome, escolha a região mais próxima (ex.: `us-east-1` ou `sa-east-1`) e crie a senha do banco (pode ser aleatória — o banco do projeto não será usado). Aguarde o projeto ser provisionado.
3. Anote o **Project URL / Reference ID**: em **Project Settings → General**, o URL é `https://<ref>.supabase.co` (ex.: `https://abcxyz.supabase.co`).
4. **Crie o bucket:** menu **Storage → New bucket** → nome: `forger-media` → **marque "Public bucket"** → Create.
5. **Crie as chaves S3:** em **Project Settings → Storage → S3 Access Keys** → **Create new access key** → dê um nome (ex.: `forger`) e escolha a role **Admin (full access)** → copie o **Access Key ID** e o **Secret Access Key** (aparecem uma única vez).

**Anote para o Passo 4:**

```
AWS_ACCESS_KEY_ID=<access-key-id>
AWS_SECRET_ACCESS_KEY=<secret-access-key>
AWS_DEFAULT_REGION=auto                 # Supabase ignora a região na assinatura
AWS_BUCKET=forger-media
AWS_ENDPOINT=https://<ref>.supabase.co/storage/v1/s3
AWS_URL=https://<ref>.supabase.co/storage/v1/object/public
AWS_USE_PATH_STYLE_ENDPOINT=true
```

---

## Passo 3 — Push do código

O `render.yaml` já contém os 2 serviços (API + Web) e o TLS do TiDB. Se ainda não pusher, faça:

```bash
git add -A
git commit -m "feat: deploy config for Render (TiDB + R2)"
git push origin main
```

---

## Passo 4 — Blueprint no Render

1. Acesse https://dashboard.render.com → **New → Blueprint**.
2. Selecione o repositório `Forger` → Render detecta o `render.yaml` e cria:
   - **forger-api** (backend Laravel)
   - **forger-web** (frontend Vue/nginx)
3. O deploy inicia sozinho. Enquanto isso, clique em cada serviço e preencha as **env vars** que estão `sync: false` (guarda ícone de aviso):

**Em forger-api (Environment → Environment Variables):**

```
APP_URL=https://forger-api.onrender.com
DB_HOST=<host-do-tidb>
DB_PORT=4000
DB_DATABASE=test
DB_USERNAME=<user.ex>
DB_PASSWORD=<senha>
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_ENDPOINT=https://<ref>.supabase.co/storage/v1/s3
AWS_BUCKET=forger-media
AWS_URL=https://<ref>.supabase.co/storage/v1/object/public
```

> `APP_KEY`, `MYSQL_ATTR_SSL_CA`, `FILESYSTEM_DISK=s3`, `AWS_DEFAULT_REGION=auto`,
> `AWS_USE_PATH_STYLE_ENDPOINT=true` e os drivers de cache/session/queue já vêm do render.yaml.

4. **Salve** (em forger-api, clique em **Save, rebuild and deploy**).
5. Confira os logs do serviço: o `preDeployCommand` roda `php artisan migrate --force` automaticamente antes de subir. Se tudo der certo, o deploy fica `Live` com a API em `https://forger-api.onrender.com/api`.

---

## Passo 5 — Verificação

1. **Swagger:** `https://forger-api.onrender.com/docs/docs.html` — abrir `POST /register`, "Try it out", criar usuário, copiar token, Authorize, listar `/posts`.
2. **App:** `https://forger-web.onrender.com` — registrar um usuário novo, criar post com imagem (a imagem deve aparecer no R2), curtir, comentar, seguir, trocar avatar, deletar post.
3. **Verifique no Supabase:** o bucket `forger-media` deve conter as imagens em `posts/` e `avatars/` (Storage → forger-media).
4. **Janela anônima / celular:** acessar e testar registro e login (critério da Context.md).

---

## Passo 6 — Manutenção (free tier)

- O Render free **dorme após 15 min sem tráfego** e demora 30–60s para acordar (cold start).
- Para manter acordado durante a apresentação/demo: use o cron-job.org ou UptimeRobot para **pingar o frontend a cada 10 min** (`https://forger-web.onrender.com`).
- Limite de **750 horas/mês** por workspace: com 2 serviços dormindo a maior parte do tempo, não estoura para um projeto de faculdade.

---

## Solução de problemas

| Sintoma                                                      | Causa provável                                | Correção                                                   |
| ------------------------------------------------------------ | --------------------------------------------- | ---------------------------------------------------------- |
| `SQLSTATE[HY000] [2002] Connection refused` no log do deploy | DB_HOST/DB_PORT errados ou TiDB ainda criando | Conferir host/porta 4000 e salvar env vars                 |
| `Access denied for user`                                     | Username deve ser `<user>.<cluster-id>`       | Conferir o usuário no painel do TiDB (Connect)             |
| TLS handshake falha                                          | MYSQL_ATTR_SSL_CA ausente                     | Confirmar a env var no serviço forger-api                  |
| Imagens não carregam na produção                             | AWS_URL errado ou bucket sem acesso público   | Conferir se o bucket é público e as chaves S3 (Admin)      |
| Login 401 no frontend                                        | CORS                                          | Laravel já permite `api/*` para todas as origens (default) |
| Primeiro acesso demora                                       | Cold start do free tier                       | Ping a cada 10 min (Passo 6)                               |
