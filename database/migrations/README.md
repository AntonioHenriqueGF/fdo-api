# Instruções

## Criar migrações no Laravel

Para criar uma migração no Laravel, siga os passos abaixo:
1. Abra o terminal e navegue até a raiz do projeto Laravel.
2. Execute o comando abaixo para criar uma nova migração de criação de tabela:
```bash
php artisan make:migration create_table_name --create=table_name
```
3. Ou então, execute o comando abaixo para criar uma nova migração de alteração de tabela:
```bash
php artisan make:migration add_column_to_table_name --table=table_name
```
4. Se quiser alterar uma tabela existente, execute o comando abaixo:
```bash
php artisan make:migration alter_table_name --table=table_name
```

## Rodar as migrações no Laravel

Para rodar as migrações no Laravel, siga os passos abaixo:
1. Certifique-se de que o ambiente de desenvolvimento esteja configurado corretamente, incluindo a conexão com o banco de dados.
2. Abra o terminal e navegue até a raiz do projeto Laravel.
3. Execute o comando abaixo para rodar as migrações:
```bash
php artisan migrate
```