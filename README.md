# Api/Microsserviço de Gerenciamento de Pedidos de Viagem Corporativa

# Resumo

- Utilizado ferramentas de controle de qualidade no CI como: PHPStan, PHPInsights, Laravel Pint (code standard/style) e PHPMD.

- Para busca textual como nome da cidade de forma "Fuzzy Search", foi utilizado o Typesense.

- Utilizado colunas binárias (16) no lugar de char (36) por conta de performance, desempenho de indexação e comparações mais rápidas para armazenar os ids UUID V7.
  - Utilizado uma coluna de `code` para ser mais "user friendly", usar nos chamados e tudo mais.

- Sentry como logs, trace e um pouco de APM.

- Conceito e implementação do **outbox pattern** para ter resiliência na troca de mensagens entre sistemas ou partes de um sistema, quando não podemos ter atomicidade na operação.

- Tipagem estática e mais clara e direta com objetos de data do `spatie/laravel-data`. Ou seja, uma modelagem de forma estruturada é matematicamente comprovável que, se um programa fortemente tipado for compilado, é impossível que esse programa tenha uma série de bugs que poderiam existir em linguagens de tipagem fraca. Em outras palavras, tipagem forte dá ao programador uma garantia melhor de que o código realmente se comporta como deveria.

- Utilizado conceito de Actions e Queries para separar comandos, ou caso de uso de recuperação ou verificação dos dados ou regra de negócio.

- Health check, Retry e Rate Limiting.

- Idempotência com header de `Idempotency-Key` para requisições POST.

# Up 🚀

Para executar a aplicação é bastante simples, primeiro clone o repositório, após isso, faça os seguintes passos:

```bash
cd docker
make docker/config-env
make docker/app/build
make docker/up
make docker/queue/up
make docker/scheduler/up
```

Para criar um admin use o comando de: `php artisan app:create-admin`.

A autenticação é via *bearer token* e após cadastrar um novo usuário, já estará disponível o mesmo na resposta.

Importe a coleção do postman para fazer as requisições.

Pronto, sua aplicação estará sendo executada por padrão na porta `8012`.

Veja as seguintes variáveis no arquivo de `.env` de acordo com sua preferência de porta:

```
WEBSERVER_PORT_HTTP=8012
COMPOSE_MYSQL_PORT=33060
COMPOSE_REDIS_PORT=63799
```

### Postman

Para vê as collections e endpoints, importe o arquivo de `API.postman_collection.json` para o seu Postman.

## Testes 🐛

Essa API está 100% coberta com testes de integração. Para executá-los, crie um banco de dados chamado `testing`, e após isso, execute: `composer populate-db && composer tests`.
