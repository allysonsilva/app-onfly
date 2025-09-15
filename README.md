# Api/Microsserviço de Gerenciamento de Pedidos de Viagem Corporativa

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
