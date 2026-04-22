CREATE INDEX idx_orders_user_id ON orders (user_id);
CREATE INDEX idx_order_items_order_id ON order_items (order_id);
CREATE INDEX idx_order_items_product_id ON order_items (product_id);
CREATE INDEX idx_payments_order_id ON payments (order_id);

CREATE INDEX idx_orders_created_at ON orders (created_at);
CREATE INDEX idx_payments_created_at ON payments (created_at);

CREATE INDEX idx_payments_status_created_at
    ON payments (status, created_at);