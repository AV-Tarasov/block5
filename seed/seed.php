<?php

$pdo = new PDO(
    "pgsql:host=localhost;port=5432;dbname=shop",
    "postgres",
    "12345",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

mt_srand(12345);

$config = [
    'users' => 50000,
    'products' => 20000,
    'orders' => 100000,
    'order_items' => 200000,
    'payments' => 100000,
];

$pdo->beginTransaction();

truncateTables($pdo);

seedUsers($pdo, $config['users']);
seedProducts($pdo, $config['products']);
seedOrders($pdo, $config['orders']);
seedOrderItems($pdo, $config['order_items']);
seedPayments($pdo, $config['payments']);

recalculateOrderTotals($pdo);

$pdo->commit();

echo "Seed completed.\n";

function truncateTables(PDO $pdo): void
{
    $pdo->exec("
        TRUNCATE TABLE
            audit_log,
            payments,
            order_items,
            orders,
            products,
            users
        RESTART IDENTITY CASCADE
    ");
}

function seedUsers(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO users (email, name, created_at)
        VALUES (?, ?, ?)
    ");

    for ($i = 1; $i <= $count; $i++) {
        $createdAt = randomDate();

        $stmt->execute([
            "user{$i}@mail.com",
            "User {$i}",
            $createdAt
        ]);
    }

    echo "Users seeded\n";
}

function seedProducts(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO products (sku, title, price, created_at)
        VALUES (?, ?, ?, ?)
    ");

    for ($i = 1; $i <= $count; $i++) {
        $price = mt_rand(100, 100000) / 100;

        $stmt->execute([
            "SKU-" . str_pad((string)$i, 6, '0', STR_PAD_LEFT),
            "Product {$i}",
            $price,
            randomDate()
        ]);
    }

    echo "Products seeded\n";
}

function seedOrders(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, status, total_amount, created_at)
        VALUES (?, ?, 0, ?)
    ");

    for ($i = 1; $i <= $count; $i++) {
        $status = weightedRandom([
            'paid' => 70,
            'new' => 20,
            'cancelled' => 10
        ]);

        $stmt->execute([
            mt_rand(1, 50000),
            $status,
            randomDate()
        ]);
    }

    echo "Orders seeded\n";
}

function seedOrderItems(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, qty, price)
        VALUES (?, ?, ?, ?)
    ");

    for ($i = 1; $i <= $count; $i++) {
        $stmt->execute([
            mt_rand(1, 100000),
            mt_rand(1, 20000),
            mt_rand(1, 5),
            mt_rand(100, 50000) / 100
        ]);
    }

    echo "Order items seeded\n";
}

function seedPayments(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO payments (order_id, status, provider, created_at)
        VALUES (?, ?, ?, ?)
    ");

    $providers = ['stripe', 'paypal', 'cash'];

    for ($i = 1; $i <= $count; $i++) {
        $status = weightedRandom([
            'paid' => 70,
            'failed' => 20,
            'pending' => 10
        ]);

        $stmt->execute([
            $i,
            $status,
            $providers[array_rand($providers)],
            randomDate()
        ]);
    }

    echo "Payments seeded\n";
}

function recalculateOrderTotals(PDO $pdo): void
{
    $pdo->exec("
        UPDATE orders o
        SET total_amount = totals.total
        FROM (
            SELECT
                order_id,
                SUM(qty * price) AS total
            FROM order_items
            GROUP BY order_id
        ) totals
        WHERE totals.order_id = o.id
    ");

    echo "Order totals recalculated\n";
}

function weightedRandom(array $weights): string
{
    $sum = array_sum($weights);
    $rand = mt_rand(1, $sum);

    foreach ($weights as $value => $weight) {
        $rand -= $weight;
        if ($rand <= 0) {
            return $value;
        }
    }

    return array_key_first($weights);
}

function randomDate(): string
{
    $timestamp = mt_rand(
        strtotime('-180 days'),
        time()
    );

    return date('Y-m-d H:i:s', $timestamp);
}