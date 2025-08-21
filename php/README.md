# Prova PHP — CRUD + Vinculação de Cores (SQLite)

Implementação sem frameworks:
- CRUD de usuários (Create/Read/Update/Delete)
- Vincular/desvincular **várias cores** a um usuário (tabela `user_colors`)

## Requisitos
- PHP 8+ com PDO SQLite habilitado

## Como executar
```bash
# subir servidor embutido do PHP na pasta do projeto
php -S 0.0.0.0:7070
# abrir no navegador
# http://localhost:7070
```

> O banco `database/db.sqlite` já acompanha o projeto.
> Se quiser popular cores iniciais, use o arquivo `seeds/insert_colors_seed.sql` (ou insira manualmente na tabela `colors`).

## Estrutura
- `index.php` — UI e rotas básicas (list, create, edit, delete, colors)
- `connection.php` — conexão PDO SQLite
- `migrations/*.sql` — DDL (opcional, já aplicado em `db.sqlite`)
- `seeds/*.sql` — inserts de exemplo
- `database/db.sqlite` — base pronta
