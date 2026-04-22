SELECT *
FROM orders
ORDER BY created_at DESC
LIMIT 100;

SELECT *
FROM order_items
WHERE order_id = ANY (:order_ids);