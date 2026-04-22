SELECT orders.id, orders.user_id, orders.status, orders.created_at
FROM orders
ORDER BY orders.created_at DESC
LIMIT 100;


SELECT order_items.order_id, order_items.product_id, products.title, order_items.qty, order_items.price
FROM order_items
         JOIN products ON products.id = order_items.product_id
WHERE order_items.order_id IN ('id');