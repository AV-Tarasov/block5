SELECT products.title, SUM(order_items.qty)
FROM order_items
         JOIN products ON products.id = order_items.product_id
         JOIN orders ON orders.id = order_items.order_id
WHERE orders.created_at BETWEEN to_timestamp(:date_from) AND to_timestamp(:date_to)
GROUP BY products.title
ORDER BY SUM(order_items.qty) DESC
LIMIT 50;