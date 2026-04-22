SELECT users.name, SUM(orders.total_amount) AS total_amount
FROM orders
         JOIN users ON users.id = orders.user_id
         JOIN payments ON payments.order_id = orders.id
WHERE payments.status = 'paid'
  AND orders.created_at >= NOW() - INTERVAL '30 days'
GROUP BY users.name
ORDER BY total_amount DESC
LIMIT 20