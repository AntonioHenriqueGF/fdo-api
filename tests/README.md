# Command to run tests

```bash
php artisan test --compact
```

Run only one specific test. Accepts class name or method name as a filter.

```bash
php artisan test --compact --filter=CategoryTransactions # Runs CategoryTransactionsTest.php
```

Visualize warnings

```bash
php vendor/bin/pest --display-warnings
```

---

# Comandos para rodar os testes

```bash
php artisan test --compact
```

Rodar apenas um teste específico. Aceita o nome da classe ou do método como filtro.

```bash
php artisan test --compact --filter=CategoryTransactions # Executa CategoryTransactionsTest.php
```

Visualizar avisos

```bash
php vendor/bin/pest --display-warnings
```