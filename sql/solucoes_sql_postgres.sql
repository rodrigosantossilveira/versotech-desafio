-- Soluções dos Desafios (PostgreSQL)
-- Base conforme schema/dados fornecidos

-- 1) Vendedores ativos (id, nome, salario) ordenados por nome ASC
SELECT
  id_vendedor AS id,
  nome,
  salario
FROM vendedores
WHERE inativo = FALSE
ORDER BY nome ASC;

-- 2) Funcionários com salário acima da média (id, nome, salario) ordenado por salario DESC
SELECT
  id_vendedor AS id,
  nome,
  salario
FROM vendedores
WHERE salario > (SELECT AVG(salario) FROM vendedores)
ORDER BY salario DESC;

-- 3) Resumo por cliente: todos os clientes e o total de pedidos transmitidos
-- (total = soma de valor_total), inclusive clientes sem pedidos (total = 0)
SELECT
  c.id_cliente AS id,
  c.razao_social,
  COALESCE(SUM(p.valor_total), 0) AS total
FROM clientes c
LEFT JOIN pedido p ON p.id_cliente = c.id_cliente
GROUP BY c.id_cliente, c.razao_social
ORDER BY total DESC, id;

-- 4) Situação por pedido (id, valor, data, situacao)
SELECT
  p.id_pedido AS id,
  p.valor_total AS valor,
  p.data_emissao AS data,
  CASE
    WHEN p.data_cancelamento IS NOT NULL THEN 'CANCELADO'
    WHEN p.data_faturamento IS NOT NULL THEN 'FATURADO'
    ELSE 'PENDENTE'
  END AS situacao
FROM pedido p
ORDER BY p.id_pedido;

-- 5) Produto mais vendido (em quantidade) com total_vendido, #clientes distintos e #pedidos
-- Desempate por total_vendido DESC
SELECT
  ip.id_produto,
  SUM(ip.quantidade) AS quantidade_vendida,
  SUM(ip.preco_praticado * ip.quantidade) AS total_vendido,
  COUNT(DISTINCT p.id_cliente) AS clientes,
  COUNT(DISTINCT ip.id_pedido) AS pedidos
FROM itens_pedido ip
JOIN pedido p ON p.id_pedido = ip.id_pedido
GROUP BY ip.id_produto
ORDER BY quantidade_vendida DESC, total_vendido DESC
LIMIT 1;
