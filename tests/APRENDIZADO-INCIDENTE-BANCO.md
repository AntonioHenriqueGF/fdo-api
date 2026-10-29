# Por que os testes apagaram o banco (e como foi resolvido)

> Anotação pessoal de aprendizado. Os links apontam para o código do projeto e do `vendor/` — abra no VS Code para seguir.

## Resumo em uma frase

Rodar `php artisan test` dentro do container executou `migrate:fresh` **no banco real** (`fdo_api`), porque as variáveis de ambiente do Docker venceram as variáveis de teste do `phpunit.xml`.

---

## 1. O que causou o erro

Foram três peças que, juntas, causaram o estrago.

### Peça 1 — O teste apaga o banco de propósito

[tests/Feature/CategoryTransactionsTest.php:9](tests/Feature/CategoryTransactionsTest.php#L9):

```php
uses(RefreshDatabase::class);
```

O trait `RefreshDatabase` existe para dar a cada execução um banco limpo. Para isso ele roda `migrate:fresh`, que **derruba todas as tabelas** e recria do zero:

[vendor/.../Testing/RefreshDatabase.php:119](vendor/laravel/framework/src/Illuminate/Foundation/Testing/RefreshDatabase.php#L119):

```php
$this->artisan('migrate:fresh', $this->migrateFreshUsing());
```

Isso é normal e desejado — **desde que o banco seja o de testes**.

### Peça 2 — O `phpunit.xml` não estava trocando o banco

A versão antiga tinha:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Dois problemas:

1. `:memory:` é um recurso do **SQLite** (banco em memória). Com MySQL não significa nada.
2. Mais grave: `<env>` **não sobrescreve** uma variável que já existe. Veja como o PHPUnit trata o `<env>`:

   [vendor/phpunit/.../PhpHandler.php:140-148](vendor/phpunit/phpunit/src/TextUI/Configuration/PhpHandler.php#L140-L148):

   ```php
   if ($force || getenv($name) === false) {   // só grava se NÃO existir
       putenv("{$name}={$value}");
   }
   // ...
   if ($force || !isset($_ENV[$name])) {
       $_ENV[$name] = $value;
   }
   ```

### Peça 3 — O Docker já definia as variáveis reais

No [docker-compose.yml](docker-compose.yml), o serviço `api` tem:

```yaml
env_file:
  - .env
```

Isso injeta `DB_DATABASE=fdo_api` (e todo o resto do `.env`) como **variáveis de ambiente do sistema** do container. Quando o PHPUnit iniciava, `DB_DATABASE` já existia → o `<env>` era ignorado → os testes conectavam no `fdo_api` → `migrate:fresh` → banco vazio.

> Efeito colateral que ninguém via: **nenhuma** variável do `phpunit.xml` estava valendo no container. Os testes rodavam com `APP_ENV=local`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`... ou seja, usando a infraestrutura real.

---

## 2. Por que `force="true"` não bastou (o detalhe que mais ensina)

A primeira ideia foi `<env ... force="true"/>`. Pelo código acima, com `force` o PHPUnit grava em `putenv` e em `$_ENV`. Parece suficiente — mas os testes continuaram vendo `fdo_api`.

O motivo está em **onde o Laravel lê** as variáveis. Ele usa a biblioteca `phpdotenv`, que consulta os "adaptadores" nesta ordem:

[vendor/vlucas/phpdotenv/.../RepositoryBuilder.php:25-28](vendor/vlucas/phpdotenv/src/Repository/RepositoryBuilder.php#L25-L28):

```php
private const DEFAULT_ADAPTERS = [
    ServerConstAdapter::class,  // 1º: $_SERVER
    EnvConstAdapter::class,     // 2º: $_ENV
];
```

E em [vendor/.../Support/Env.php:89](vendor/laravel/framework/src/Illuminate/Support/Env.php#L89) o repositório é criado como `immutable()` — o primeiro valor encontrado vence.

As variáveis do Docker estão em **`$_SERVER`**, que é lido **primeiro**. O `force` mexia em `$_ENV` e `putenv`, que nunca chegavam a ser consultados.

**Solução:** usar `<server>` em vez de `<env>`. O PHPUnit grava direto em `$_SERVER`, sem condição:

[vendor/phpunit/.../PhpHandler.php:119-124](vendor/phpunit/phpunit/src/TextUI/Configuration/PhpHandler.php#L119-L124):

```php
private function handleServerVariables(VariableCollection $variables): void
{
    foreach ($variables as $variable) {
        $_SERVER[$variable->name()] = $variable->value();
    }
}
```

---

## 3. A solução, camada por camada

A ideia é ter **várias barreiras independentes**: se uma falhar, a outra segura.

### Camada 1 — Apontar os testes para outro banco

[phpunit.xml:20-36](phpunit.xml#L20-L36):

```xml
<server name="APP_ENV" value="testing"/>
<server name="DB_DATABASE" value="fdo_api_testing"/>
<server name="QUEUE_CONNECTION" value="sync"/>
...
```

Todas as variáveis viraram `<server>`, então agora todas realmente valem dentro do container.

### Camada 2 — A trava de segurança

Mesmo com o `phpunit.xml` certo, alguém pode alterá-lo no futuro. Por isso existe uma verificação que **aborta os testes** se o banco não for de teste:

[tests/TestCase.php:15-29](tests/TestCase.php#L15-L29):

```php
public function createApplication(): Application
{
    $app = parent::createApplication();

    $connection = $app['config']->get('database.default');
    $database = (string) $app['config']->get("database.connections.{$connection}.database");

    if (! str_ends_with($database, '_testing')) {
        throw new RuntimeException(/* ... */);
    }

    return $app;
}
```

**Por que em `createApplication()`?** Porque é o primeiro momento em que a configuração existe, e acontece **antes** de o `RefreshDatabase` rodar. Se a checagem ficasse num `setUp()` comum ou em `beforeEach`, o `migrate:fresh` já teria rodado.

### Camada 3 — O banco de testes precisa existir

[docker/mysql/init/01-create-testing-database.sql](docker/mysql/init/01-create-testing-database.sql) cria o `fdo_api_testing`. Ele é montado em [docker-compose.override.yml:10-13](docker-compose.override.yml#L10-L13) na pasta `/docker-entrypoint-initdb.d`, que a imagem oficial do MySQL executa **somente quando o volume é criado do zero**.

- Está no `override` (que só vale em dev) para não criar banco de teste em produção.
- No seu volume já existente, o banco foi criado manualmente uma vez.

### Camada 4 — Produção nem tem como rodar testes

A melhor proteção em produção é o comando não existir. O `composer install --no-dev` não instala o Pest/PHPUnit, então `php artisan test` simplesmente não está disponível.

[Dockerfile:18-20](Dockerfile#L18-L20):

```dockerfile
ARG COMPOSER_FLAGS=""
RUN composer install --no-interaction --no-scripts --prefer-dist ${COMPOSER_FLAGS}
```

[docker-compose.yml:12-16](docker-compose.yml#L12-L16) — o build é declarado uma vez com uma **âncora YAML** (`&app-build`) e reaproveitado pelos serviços com `build: *app-build`:

```yaml
x-app-build: &app-build
  context: .
  dockerfile: Dockerfile
  args:
    COMPOSER_FLAGS: ${COMPOSER_FLAGS:-}
```

O `${COMPOSER_FLAGS:-}` é **interpolação do Docker Compose**: ele lê o `.env` da pasta do projeto ao montar o YAML. Isso é diferente do `env_file`, que só entrega variáveis ao container **já rodando** (tarde demais para o build). Em produção, basta ter no `.env`:

```
COMPOSER_FLAGS="--no-dev --optimize-autoloader"
```

E em [.dockerignore:13](.dockerignore#L13), `bootstrap/cache/*.php` foi ignorado: o `packages.php` gerado localmente lista providers de pacotes de dev (Boost, Pail, Pest...). Copiado para uma imagem sem eles, o Laravel tentaria carregar classes inexistentes e quebraria. Sem o arquivo, o Laravel o regenera a partir do que está instalado.

---

## 4. O segundo incidente: o 500 no `/sanctum/csrf-cookie`

### O sintoma

Depois de corrigir os testes, sobraram warnings: `file_get_contents(/var/www/html/.env): Failed to open stream`. O `.env` não existe na imagem (o [.dockerignore:9](.dockerignore#L9) o exclui — correto, para não embutir segredos). A tentativa de correção foi `RUN touch .env` no Dockerfile. Resultado: erro 500 com `MissingAppKeyException`.

### A causa

O `php artisan serve` tem um recurso de **recarregar o servidor quando o `.env` muda**. Para isso funcionar, quando existe um `.env` ele **remove** as variáveis de ambiente do processo filho, esperando que o filho as releia do arquivo:

[vendor/.../Console/ServeCommand.php:183-189](vendor/laravel/framework/src/Illuminate/Foundation/Console/ServeCommand.php#L183-L189):

```php
(new Collection($_ENV))->mapWithKeys(function ($value, $key) use ($hasEnvironment) {
    if ($this->option('no-reload') || ! $hasEnvironment) {
        return [$key => $value];            // sem .env: repassa tudo
    }

    return $this->shouldPassThroughEnvironmentVariable($key)
        ? [$key => $value]
        : [$key => false];                  // com .env: APAGA a variável
})
```

Só sobrevivem as da lista [`$passthroughVariables`](vendor/laravel/framework/src/Illuminate/Foundation/Console/ServeCommand.php#L79) (`APP_ENV`, `PATH`...). Como o `.env` estava **vazio**, o `APP_KEY` vindo do Docker foi apagado e nunca relido → sem chave de criptografia → 500. A resposta saiu duplicada porque o erro estourou de novo na fase de *terminate* da requisição (o middleware de cookies precisa do encrypter).

### A solução

Remover o `touch .env` e criar um [.env.testing](.env.testing) (só comentários). O Laravel procura `.env.{APP_ENV}` quando `APP_ENV` está definido:

[vendor/.../Bootstrap/LoadEnvironmentVariables.php:49-57](vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/LoadEnvironmentVariables.php#L49-L57):

```php
$environment = Env::get('APP_ENV');
// ...
$this->setEnvironmentFilePath($app, $app->environmentFile().'.'.$environment);
```

- Nos testes, `APP_ENV=testing` (pelo `phpunit.xml`) → lê `.env.testing`, que existe → sem warning.
- O `serve` verifica apenas `.env`, que continua não existindo → repassa todas as variáveis do Docker → `APP_KEY` intacto.

---

## 5. Lições

1. **Variáveis de ambiente têm precedência, e a ordem importa.** Antes de confiar numa configuração, descubra *quem lê* e *de onde lê*. Aqui: Laravel → `$_SERVER` → `$_ENV`, e o primeiro vence.
2. **Defesa em camadas.** Configuração correta + trava no código + ausência da ferramenta em produção. Cada uma cobre a falha da outra.
3. **A imagem Docker é uma foto do código.** Como o código entra com `COPY . .` (sem bind mount), editar um arquivo não muda o container. Sempre `docker compose up -d --build` antes de testar. *(Foi exatamente isso que causou uma segunda limpeza do banco durante a correção: os testes rodaram com o `phpunit.xml` antigo ainda dentro da imagem.)*
4. **"Arquivo vazio" não é o mesmo que "arquivo ausente".** Ferramentas mudam de comportamento só pela existência de um arquivo, como o `serve` com o `.env`.
5. **Mockar o banco não resolveria.** As queries usam `DATE_FORMAT` (exclusivo do MySQL) e a lógica testada é a própria agregação SQL — um mock testaria o mock. O certo é um banco real, porém isolado.

## Como rodar os testes

```bash
docker compose up -d --build api
docker compose exec api php artisan test --compact

# para ver detalhes de warnings
docker compose exec api php vendor/bin/pest --display-warnings
```
