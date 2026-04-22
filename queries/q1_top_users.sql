SELECT users.id, users.email, SUM(orders.total_amount) AS total_amount
FROM users
         JOIN orders ON orders.user_id = users.id
WHERE orders.status = 'paid'
  AND orders.created_at >= NOW() - INTERVAL '30 days'
GROUP BY users.id, users.email
ORDER BY total_amount DESC
LIMIT 20;