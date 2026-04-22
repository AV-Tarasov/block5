SELECT COUNT(*) FILTER (WHERE p.status = 'paid') AS paid_orders,
       COUNT(*)                                  AS total_orders,
       ROUND(
               CASE
                   WHEN COUNT(*) = 0 THEN 0
                   ELSE COUNT(*) FILTER (WHERE p.status = 'paid')::numeric
                            / COUNT(*) * 100
                   END,
               2
       )                                         AS conversion_percent
FROM orders o
         JOIN payments p ON p.order_id = o.id
WHERE o.created_at BETWEEN to_timestamp(:date_from) AND to_timestamp(:date_to);