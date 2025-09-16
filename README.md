# API/Microsserviço de Gerenciamento de Pedidos de Viagem Corporativa ✈️

[![Test Coverage](badge-coverage.svg)](https://github.com/allysonsilva/app-onfly/actions/workflows/code_quality.yml)
[![Code Quality Analysis](https://github.com/allysonsilva/app-onfly/actions/workflows/code_quality.yml/badge.svg?branch=main)](https://github.com/allysonsilva/app-onfly/actions/workflows/code_quality.yml)
[![PHP Version](https://img.shields.io/static/v1?label=php&message=%E2%89%A58.4&color=777BB4&logo=php)](https://www.php.net)
[![Laravel Version](https://img.shields.io/static/v1?label=laravel&message=%E2%89%A512.0&color=ff2d20&logo=laravel)](https://laravel.com)


## Principais Pontos 🎯

- Utilização de ferramentas de controle de qualidade no CI, como: [PHPStan, PHPInsights, Laravel Pint (code standard/style) e PHPMD](https://github.com/allysonsilva/php-pre-push).

- Para busca textual, como o nome da cidade de forma "fuzzy search", foi utilizado o Typesense.
  - Os filtros de data na listagem dos pedidos de viagem (`departure_date` e `return_date`) podem ser utilizados com operadores de comparação como: `<, >, <=, >=, !=`. Por exemplo: `/travel-requests?departure_date=>=2025-12-10`.

- Utilizado 2 tipos de usuários com suas políticas e permissões: **admin** e **usuário comum**. O usuário admin é criado apenas via `php artisan app:create-admin`. Após a execução do comando, é retornado um token que pode ser utilizado como **Bearer Token** no caso de uso de `Atualizar o status de um pedido de viagem`.

- Utilizado colunas binárias (16) no lugar de `char` (36) por questões de performance, desempenho de indexação e comparações mais rápidas para armazenar os IDs UUID V7.
  - Utilizado uma coluna `code` para ser mais "human-readable", um código único visível ao usuário, usado para listagem, referência rápida ou compartilhamento. Exemplo: `TVR-SJJWFVE476A43`.
  - Utilizado 24 bits do UUID com uma quantidade de combinações/ms de ~16.777.216, com o risco de colisão sendo praticamente zero (Birthday Problem) ao criar 10k registros no mesmo ms.
  - Utilizado o algoritmo de Crockford’s Base32 para transformar o UUID nesse `code`.

- Sentry para logs, trace e APM básico.

- Conceito e implementação do **outbox pattern** para garantir resiliência na troca de mensagens entre sistemas ou partes de um sistema, quando não é possível ter atomicidade na operação.

- Tipagem estática mais clara e direta com objetos de data do `spatie/laravel-data`. Em outras palavras, uma modelagem de forma estruturada é matematicamente comprovado que um programa com tipagem forte, após a compilação, elimina uma série de bugs que poderiam existir em linguagens de tipagem fraca. A tipagem forte dá ao programador uma garantia maior de que o código realmente se comporta como deveria.

- Utilização da arquitetura de **Actions** e **Queries** para separar comandos/casos de uso de recuperação ou verificação dos dados.
  - Uma **Action** é uma classe que recebe uma entrada, executa uma ação (escrita no banco) e fornece uma saída. É por isso que uma Action geralmente possui apenas um método público e, às vezes, um construtor. Ela sempre trabalha de forma estruturada: recebe um objeto de DTO/Data e devolve um objeto de DTO/Data, para que o cliente/consumidor possa manipular os dados de forma mais apropriada, seja na web, API ou comando.
  - Uma **Query** é uma classe que serve para recuperar dados do banco, ou fazer verificações de regras de negócio de forma centralizada.

- Configuração de **Health check** dos principais componentes da aplicação.

- Retry e Rate Limiting.

- Idempotência com o header `Idempotency-Key` para requisições `POST`.

## Setup / Visão Geral 🏗️

Para executar a aplicação é muito simples. Primeiro, clone o repositório e, em seguida, siga os passos abaixo:

1. Execute o comando `make docker/config-env` para criar o arquivo `docker/.env` com as variáveis de ambiente do docker compose configuradas corretamente!
2. Execute `make docker/app/build` para construir a imagem principal que é utilizada pela API, QUEUE e WORKER.

### Variáveis de Ambiente 🔐

**Observação:** As variáveis de ambiente que estão em `docker/php/app/.env` têm precedência sobre as do projeto no diretório raiz (`.env`).

> Veja as variáveis: `WEBSERVER_PORT_HTTP`, `MAILPIT_DASHBOARD_PORT`, `COMPOSE_MYSQL_PORT` e `COMPOSE_REDIS_PORT`. Elas serão utilizadas no bind das portas para acesso local.

As seguintes portas serão expostas para o seu ambiente local:

```yaml
WEBSERVER_PORT_HTTP=8012
MAILPIT_DASHBOARD_PORT=8025
COMPOSE_MYSQL_PORT=33060
COMPOSE_REDIS_PORT=63789
```

### Run 🚀 🏃

Para executar a aplicação:

```bash
make docker/up
```

Após todos os serviços estarem rodando com sucesso, é necessário também iniciar os containers de agendamento e workers para processar as filas.

Para iniciar o container de **scheduler**:

```bash
make docker/scheduler/up
```

Para iniciar o container de **queue**:

```bash
make docker/queue/up
```

Pronto, a aplicação estará sendo executada por padrão na porta `8012`.

#### Health 🕵️‍♂️

Para saber se todos os serviços estão funcionando, faça uma requisição para `http://127.0.0.1:8012/v1/healthz?fresh&view`.

### Criar Admin 👨‍💼

Para acessar as rotas de admin, é necessário criar um usuário com as permissões corretas para que o token de acesso possa ser gerado.

Utilize o comando: `php artisan app:create-admin`.

Após a execução do comando, você verá uma saída como esta:

```
Admin Xyz criado com sucesso!

The token should be included in the "Authorization" header as a "Bearer" token:

7|lAbqRSdFG7KqzeFIG54OkMFr5dyO9OOuhnsN2c2109f75c19
```

## Postman 🎮

Para ver as collections e endpoints, importe o arquivo `API.postman_collection.json` para o seu Postman.

## Executar Testes 🧪 🐛

- Renomeie o arquivo `.env.testing.example` para `.env.testing` e configure-o de acordo com a sua preferência.
- Execute uma única vez: `composer populate-db`
- Sempre que precisar executar a pilha de testes: `composer tests-only`
- Para executar o mesmo comando e processo que é executado no CI: `composer tests-ci`
