-- USERS
CREATE TABLE users
(
    id         BIGSERIAL PRIMARY KEY,
    email      TEXT NOT NULL UNIQUE,
    name       TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

-- PRODUCTS
CREATE TABLE products
(
    id         BIGSERIAL PRIMARY KEY,
    sku        TEXT           NOT NULL UNIQUE,
    title      TEXT           NOT NULL,
    price      NUMERIC(10, 2) NOT NULL CHECK (price >= 0),
    created_at TIMESTAMP DEFAULT NOW()
);

-- ORDERS
CREATE TABLE orders
(
    id           BIGSERIAL PRIMARY KEY,
    user_id      BIGINT         NOT NULL,
    status       TEXT           NOT NULL CHECK (status IN ('new', 'paid', 'cancelled')),
    total_amount NUMERIC(10, 2) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP               DEFAULT NOW(),

    FOREIGN KEY (user_id) REFERENCES users (id)
);

-- ORDER ITEMS
CREATE TABLE order_items
(
    id         BIGSERIAL PRIMARY KEY,
    order_id   BIGINT         NOT NULL,
    product_id BIGINT         NOT NULL,
    qty        INT            NOT NULL CHECK (qty > 0),
    price      NUMERIC(10, 2) NOT NULL CHECK (price >= 0),

    FOREIGN KEY (order_id) REFERENCES orders (id),
    FOREIGN KEY (product_id) REFERENCES products (id)
);

-- PAYMENTS
CREATE TABLE payments
(
    id         BIGSERIAL PRIMARY KEY,
    order_id   BIGINT NOT NULL UNIQUE,
    status     TEXT   NOT NULL CHECK (status IN ('pending', 'paid', 'failed')),
    provider   TEXT   NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),

    FOREIGN KEY (order_id) REFERENCES orders (id)
);

-- AUDIT LOG
CREATE TABLE audit_log
(
    id          BIGSERIAL PRIMARY KEY,
    entity_type TEXT   NOT NULL,
    entity_id   BIGINT NOT NULL,
    action      TEXT   NOT NULL,
    meta        JSONB,
    created_at  TIMESTAMP DEFAULT NOW()
);