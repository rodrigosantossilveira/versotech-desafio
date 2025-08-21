# Desafio Versotech — SQL e PHP

Este repositório contém as soluções dos dois testes técnicos:

## Estrutura
```
versotech-desafio/
├─ sql/   → soluções do teste de SQL (PostgreSQL)
└─ php/   → soluções do teste de PHP (CRUD + vinculação de cores em SQLite)
```

## SQL
- Local: [`sql/`](./sql)
- Arquivo principal: [`solucoes_sql_postgres.sql`](./sql/solucoes_sql_postgres.sql)
- Inclui também um `README.md` para instruções de execução.

## PHP
- Local: [`php/`](./php)
- Implementação em PHP puro (sem frameworks).
- Funcionalidades:
  - CRUD de usuários (Criar, Listar, Editar, Excluir)
  - Vincular/desvincular várias cores a um usuário (tabela `user_colors`)
- Banco de dados: SQLite (`database/db.sqlite` já incluso)
- Executar localmente:
  ```bash
  cd php
  php -S 0.0.0.0:7070
  # acessar http://localhost:7070
  ```

---
Desenvolvido por Rodrigo Santos da Silveira
