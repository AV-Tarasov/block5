<?php

$pdo = new PDO(
    "pgsql:host=localhost;port=5432;dbname=shop",
    "postgres",
    "12345",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE;");
$pdo->exec("TRUNCATE TABLE products RESTART IDENTITY CASCADE;");
$pdo->exec("TRUNCATE TABLE orders RESTART IDENTITY CASCADE;");
$pdo->exec("TRUNCATE TABLE order_items RESTART IDENTITY CASCADE;");
$pdo->exec("TRUNCATE TABLE payments RESTART IDENTITY CASCADE;");
$pdo->exec("TRUNCATE TABLE audit_log RESTART IDENTITY CASCADE;");
function seedUsers(PDO $pdo, int $count = 50000)
{
    $batchSize = 1000;

    for ($i = 1; $i <= $count; $i += $batchSize) {
        $values = [];
        $params = [];

        for ($j = 0; $j < $batchSize && ($i + $j) <= $count; $j++) {
            $email = "user" . ($i + $j) . "@mail.com";
            $name = "User " . ($i + $j);

            $values[] = "(?, ?, NOW())";
            $params[] = $email;
            $params[] = $name;
        }

        $sql = "INSERT INTO users (email, name, created_at) VALUES " . implode(',', $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "Inserted users: " . ($i + $batchSize - 1) . "\n";
    }
}

function seedProducts(PDO $pdo, int $count = 20000)
{
    $batchSize = 1000;

    for ($i = 1; $i <= $count; $i += $batchSize) {
        $values = [];
        $params = [];

        for ($j = 0; $j < $batchSize && ($i + $j) <= $count; $j++) {
            $index = $i + $j;

            $sku = "SKU" . str_pad($index, 6, '0', STR_PAD_LEFT);
            $title = "Product " . $index;
            $price = rand(100, 10000) / 10; // 10.0 - 1000.0

            $values[] = "(?, ?, ?, NOW())";
            $params[] = $sku;
            $params[] = $title;
            $params[] = $price;
        }

        $sql = "INSERT INTO products (sku, title, price, created_at) VALUES " . implode(',', $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "Inserted products: " . min($i + $batchSize - 1, $count) . "\n";
    }
}

function seedOrders(PDO $pdo, int $count = 100000)
{
    $batchSize = 1000;

    for ($i = 1; $i <= $count; $i += $batchSize) {
        $values = [];
        $params = [];

        for ($j = 0; $j < $batchSize && ($i + $j) <= $count; $j++) {

            if (($i + $j) <= 70000) {
                $userId = 1;
            } else {
                $userId = rand(2, 50000);
            }

            // распределение статусов
            $rand = rand(1, 100);
            if ($rand <= 70) {
                $status = 'paid';
            } elseif ($rand <= 90) {
                $status = 'new';
            } else {
                $status = 'cancelled';
            }

            $values[] = "(?, ?, 0, NOW())";
            $params[] = $userId;
            $params[] = $status;
        }

        $sql = "INSERT INTO orders (user_id, status, total_amount, created_at) VALUES " . implode(',', $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "Inserted orders: " . min($i + $batchSize - 1, $count) . "\n";
    }
}

function seedOrderItems(PDO $pdo, int $count = 200000)
{
    $batchSize = 1000;

    for ($i = 1; $i <= $count; $i += $batchSize) {
        $values = [];
        $params = [];

        for ($j = 0; $j < $batchSize && ($i + $j) <= $count; $j++) {
            $orderId = rand(1, 100000);
            $productId = rand(1, 20000);
            $qty = rand(1, 5);
            $price = rand(100, 10000) / 10;

            $values[] = "(?, ?, ?, ?)";
            $params[] = $orderId;
            $params[] = $productId;
            $params[] = $qty;
            $params[] = $price;
        }

        $sql = "INSERT INTO order_items (order_id, product_id, qty, price) VALUES " . implode(',', $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "Inserted order_items: " . min($i + $batchSize - 1, $count) . "\n";
    }
}

function seedPayments(PDO $pdo, int $count = 100000)
{
    $batchSize = 1000;

    for ($i = 1; $i <= $count; $i += $batchSize) {
        $values = [];
        $params = [];

        for ($j = 0; $j < $batchSize && ($i + $j) <= $count; $j++) {
            $orderId = $i + $j;

            $rand = rand(1, 100);
            if ($rand <= 70) {
                $status = 'paid';
            } elseif ($rand <= 90) {
                $status = 'failed';
            } else {
                $status = 'pending';
            }

            $providers = ['stripe', 'paypal', 'cash'];
            $provider = $providers[array_rand($providers)];

            $values[] = "(?, ?, ?, NOW())";
            $params[] = $orderId;
            $params[] = $status;
            $params[] = $provider;
        }

        $sql = "INSERT INTO payments (order_id, status, provider, created_at) VALUES " . implode(',', $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "Inserted payments: " . min($i + $batchSize - 1, $count) . "\n";
    }
}

function seedAuditLog(PDO $pdo, int $count = 50000)
{
    $batchSize = 1000;

    for ($i = 1; $i <= $count; $i += $batchSize) {
        $values = [];
        $params = [];

        for ($j = 0; $j < $batchSize && ($i + $j) <= $count; $j++) {
            $entityTypes = ['order', 'payment', 'user'];
            $actions = ['created', 'updated', 'paid', 'failed'];

            $entityType = $entityTypes[array_rand($entityTypes)];
            $entityId = rand(1, 100000);
            $action = $actions[array_rand($actions)];

            $meta = json_encode([
                'ip' => '127.0.0.1',
                'source' => 'seed'
            ]);

            $values[] = "(?, ?, ?, ?, NOW())";
            $params[] = $entityType;
            $params[] = $entityId;
            $params[] = $action;
            $params[] = $meta;
        }

        $sql = "INSERT INTO audit_log (entity_type, entity_id, action, meta, created_at) VALUES " . implode(',', $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "Inserted audit_log: " . min($i + $batchSize - 1, $count) . "\n";
    }
}

seedUsers($pdo);
echo "DONE USERS\n";

seedProducts($pdo);
echo "DONE PRODUCTS\n";

seedOrders($pdo);
echo "DONE ORDERS\n";

seedOrderItems($pdo);
echo "DONE ORDER ITEMS\n";

seedPayments($pdo);
echo "DONE PAYMENTS\n";

seedAuditLog($pdo);
echo "DONE AUDIT LOG\n";