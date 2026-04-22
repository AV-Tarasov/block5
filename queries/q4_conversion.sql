SELECT COUNT(*) FILTER (WHERE orders.status = 'paid') AS paid_orders,
       COUNT(*)                                       AS total_orders,
       ROUND(
               COUNT(*) FILTER (WHERE orders.status = 'paid')::numeric
                   / NULLIF(COUNT(*), 0) * 100,
               2
       )                                              AS conversion_percent
FROM orders
WHERE orders.created_at BETWEEN :date_from AND :date_to;