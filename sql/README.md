# Teste SQL — Versotech (PostgreSQL)

Soluções das queries do teste técnico de SQL, compatíveis com PostgreSQL 12+.

## Conteúdo
- `solucoes_sql_postgres.sql` — consultas em ordem dos desafios (1 a 5).

## Como executar (rápido)
Use qualquer cliente SQL. Exemplo com `psql`:
```bash
psql -h <host> -U <usuario> -d <banco> -f solucoes_sql_postgres.sql
```

## Observações
- As consultas assumem o schema e os dados do enunciado (tabelas: `vendedores`, `clientes`, `pedido`, `itens_pedido`).
- As queries são somente leitura.
