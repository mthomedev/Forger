PROJETO FINAL

Clone do Instagram

Especificação organizada de requisitos e entrega

Tecnologias principais: Vue.js • Laravel • PostgreSQL/MySQL • Docker • Swagger UI

Sumário

1. Visão geral e objetivo

Objetivo principal. Desenvolver uma aplicação web inspirada no Instagram, reproduzindo suas principais experiências de navegação e interação social. O frontend deve ser construído em Vue.js, enquanto o backend deve ser uma API REST em Laravel organizada no padrão MSC (Model–Service–Controller).

Resultado esperado. Ao final, o sistema deve permitir que usuários se registrem, façam login, editem seus perfis, publiquem conteúdo, naveguem pelos posts, curtam, comentem, pesquisem pessoas e sigam outros usuários.

1.1 Escopo do clone

O objetivo é clonar o comportamento e a organização visual básica do frontend, não reproduzir cada detalhe do Instagram original.

O projeto deve demonstrar domínio dos fundamentos de Vue.js e integração completa com uma API Laravel.

Chat em tempo real não faz parte da prioridade do projeto.

A interface deve ser funcional, responsiva e coerente, mesmo que simplificada.

2. Tecnologias e conceitos obrigatórios

2.1 Frontend — Vue.js

2.2 Backend — Laravel

API REST responsável por autenticação, regras de negócio, persistência e respostas em JSON.

Padrão MSC: Models para entidades e relacionamentos; Services para regras de negócio; Controllers para receber requisições e devolver respostas.

Banco de dados PostgreSQL ou MySQL executado localmente pelo compose.yaml do backend.

Seeders para popular o ambiente de desenvolvimento com dados de teste.

Documentação interativa da API com Swagger UI.

2.3 Infraestrutura

3. Escopo funcional

4. Critérios de aceite por funcionalidade

4.1 Login e registro

☐ O usuário consegue se registrar com os dados definidos pelo projeto.

☐ O sistema valida campos obrigatórios e informa erros de forma legível.

☐ O usuário consegue fazer login com credenciais válidas.

☐ Fora das telas de login e registro, não existe uma versão da aplicação para usuários não autenticados.

☐ A sessão permanece ativa conforme a estratégia de autenticação escolhida.

☐ Existe ação de logout.

4.2 Perfil do próprio usuário

☐ Exibe foto, username, nome, bio, número de posts, seguidores e seguindo.

☐ Permite alterar foto de perfil.

☐ Permite alterar bio, username e nome.

☐ Valida username único.

☐ Exibe todos os posts do usuário em uma grade ou lista.

☐ Permite abrir um post ao clicar em sua miniatura.

☐ Permite excluir os próprios posts. Essa funcionalidade é obrigatória.

4.3 Perfil de outro usuário

☐ Exibe foto, username, nome, posts, seguidores e seguindo.

☐ Exibe a quantidade de posts publicados.

☐ Exibe botão “Seguir” quando ainda não existe relação.

☐ Exibe estado “Seguindo” ou ação “Deixar de seguir” quando a relação já existe.

☐ A quantidade de seguidores é atualizada após seguir ou deixar de seguir.

☐ O próprio perfil não deve exibir botão para seguir a si mesmo.

4.4 Home

☐ Exibe uma lista de posts.

☐ Cada post mostra ao menos autor, conteúdo visual, legenda e estado de curtida.

☐ Permite curtir e remover a curtida.

☐ Permite acessar os comentários pelo botão correspondente ou clicando no post.

Observação: stories não são obrigatórios na Home. Qualquer implementação de stories pertence aos extras; somente a expiração em 24h vale +10 pontos.

☐ Exibe sugestões de usuários para seguir.

☐ O username do autor leva ao perfil correspondente.

4.5 Tela de post

☐ É acessível ao clicar no post ou no botão de comentários.

☐ Exibe os dados completos do post e do autor.

☐ Permite curtir e descurtir.

☐ Exibe a lista de comentários.

☐ Permite adicionar comentário.

☐ O autor consegue excluir o próprio post. Essa funcionalidade é obrigatória.

☐ O usuário é identificado corretamente nas interações.

4.6 Search

☐ Sem termo de pesquisa, mostra todos os usuários ou uma listagem paginada.

☐ Permite pesquisar pelo nome.

☐ Permite pesquisar pelo username.

☐ A pesquisa pode ser executada durante a digitação ou mediante envio.

☐ Cada resultado permite abrir o perfil do usuário.

☐ O próprio usuário pode aparecer ou ser removido dos resultados, desde que a decisão seja consistente.

4.7 Navegação

☐ A navegação principal possui Home, Search e Profile.

☐ O item ativo é visualmente identificado.

☐ A navegação funciona com o roteador do Vue, sem recarregar a página inteira.

☐ Rotas inexistentes possuem tratamento adequado, como redirecionamento ou página 404.

5. Regras de negócio

Exceto login e registro, todas as funcionalidades da aplicação exigem autenticação.

Um usuário não pode seguir a si mesmo.

A combinação seguidor + seguido deve ser única.

Cada usuário deve ter no máximo um like por post.

Username deve ser único e validado antes da atualização.

Somente o autor pode excluir o próprio post, e essa funcionalidade deve estar implementada.

Comentários e likes exigem usuário autenticado.

Quando stories com expiração forem implementados, stories vencidos não devem ser retornados pela API.

Respostas devem utilizar códigos HTTP coerentes e um formato de erro consistente.

6. Principais relacionamentos

7. Seeders

A API deve possuir seeders para popular o banco de desenvolvimento com dados de teste suficientes para verificar as funcionalidades implementadas.

8. Dockerização

8.1 Frontend

Deve existir somente um Dockerfile para o frontend.

Deve existir somente um compose.yaml para o frontend.

O Dockerfile deve instalar dependências e executar/buildar o projeto de forma reproduzível.

A URL da API deve ser configurável por variável de ambiente.

Arquivos e pastas desnecessários devem ser ignorados por .dockerignore.

8.2 Backend

Dockerfile.dev para desenvolvimento.

Dockerfile para produção.

Um único compose.yaml para o backend, contendo a API e o banco PostgreSQL ou MySQL.

Volume persistente para o banco de dados.

Variáveis de ambiente para conexão com banco, URL da aplicação e segredos.

O projeto deve conter exatamente um compose.yaml no frontend e um compose.yaml no backend.

9. Stories, destaques e hospedagem

Stories são opcionais e sempre pertencem aos extras, com expiração ou sem expiração. Stories sem expiração não acrescentam pontos; somente a implementação com expiração em 24 horas vale +10 pontos.

9.1 Destaques

Permitir que o usuário agrupe stories em coleções permanentes no perfil.

Cada destaque pode ter título e capa.

A regra de destaque deve ser definida com clareza: copiar a mídia ou manter uma referência persistente ao conteúdo selecionado.

9.2 Hospedagem

☐ Frontend e backend acessíveis publicamente.

☐ Banco de dados de produção configurado.

☐ Uploads/mídias persistentes e acessíveis.

☐ CORS e URLs configurados para o domínio real.

☐ Migrations executadas em produção.

☐ Registro habilitado para novos usuários.

☐ Aplicação testada em janela anônima e em outro dispositivo.

10. Checklist de entrega

10.1 Funcionalidades obrigatórias

☐ Login e registro funcionando.

☐ Não existe navegação da aplicação para usuários não autenticados, além das telas de login e registro.

☐ Perfil próprio com edição de foto, bio, username e nome; posts, seguidores e seguindo.

☐ Exclusão dos próprios posts funcionando.

☐ Perfil alheio com posts, foto, número de posts, seguidores, seguindo e botão de seguir/deixar de seguir.

☐ Home com posts e indicações de usuários para seguir.

☐ Likes e comentários funcionando na Home e na tela individual do post.

☐ Tela individual do post acessível pelo post ou pelo botão de comentários.

☐ Busca que lista usuários e permite pesquisar por nome ou username.

☐ Navegação com Home, Search e Profile.

☐ Não há elementos visuais sem funcionamento apenas para imitar o Instagram.

10.2 Vue.js

☐ Template syntax com interpolation ({{ }}) e diretivas v-bind e v-on.

☐ Reatividade com data(), ref() ou reactive().

☐ Renderização condicional com v-if, v-else e v-show.

☐ Renderização de listas com v-for.

☐ Tratamento de eventos com v-on / @click e methods.

☐ Form input bindings com v-model.

10.3 Backend e banco de dados

☐ API Laravel organizada no padrão MSC (Model–Service–Controller).

☐ Migrations e relacionamentos principais configurados.

☐ Autenticação e autorização aplicadas às funcionalidades da aplicação.

☐ Regras de follow, like, username e exclusão do próprio post respeitadas.

☐ Seeders populam o banco de desenvolvimento.

☐ Banco PostgreSQL ou MySQL funcionando.

☐ Respostas e códigos HTTP consistentes.

10.4 Docker, Swagger UI e repositório

☐ Frontend possui um único Dockerfile e um único compose.yaml.

☐ Backend possui Dockerfile.dev, Dockerfile de produção e um único compose.yaml.

☐ O compose.yaml do backend contém a API e o banco PostgreSQL ou MySQL.

☐ Todos os arquivos de Compose utilizam o nome compose.yaml.

☐ Swagger UI está acessível e permite consultar a documentação da API.

☐ Repositório no GitHub está acessível pelo link enviado.

☐ README contém as informações necessárias para executar o projeto.

☐ .env real não foi versionado e existe um .env.example sem segredos reais.

10.5 Extras opcionais

☐ Stories, quando implementados, estão claramente tratados como extra opcional.

☐ Stories com expiração em 24h: +10 pontos.

☐ Destaques: +2 pontos.

☐ Aplicação hospedada com registro e interação entre usuários reais: +10 pontos.

10.6 Conferência final

Foco: completar bem o essencial, manter o projeto funcional e saber explicar as decisões tomadas.
