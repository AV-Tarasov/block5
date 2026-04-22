SELECT users.id, users.email, COUNT(*) AS failed_payments_count
FROM users
         JOIN orders ON orders.user_id = users.id
         JOIN payments ON payments.order_id = orders.id
WHERE payments.status = 'failed'
  AND payments.created_at >= NOW() - INTERVAL '24 hours'
GROUP BY users.id, users.email
HAVING COUNT(*) > 3
ORDER BY failed_payments_count DESC;
