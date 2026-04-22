<?php


$dsn = "pgsql:host=localhost;port=5432;dbname=shop;";
$username = "postgres";
$password = "12345";

$orderId = (int)($argv[1] ?? 0);

if (!$orderId) {
    echo "Usage: php pay_order.php <order_id>\n";
    exit(1);
}

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT id, status 
        FROM orders 
        WHERE id = :id 
        FOR UPDATE
    ");
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

    if ($order['status'] !== 'new') {
        throw new Exception("Order already processed");
    }

    $stmt = $pdo->prepare("
        UPDATE payments 
        SET status = 'paid'
        WHERE order_id = :order_id
    ");
    $stmt->execute(['order_id' => $orderId]);

    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'paid'
        WHERE id = :id
    ");
    $stmt->execute(['id' => $orderId]);

    $stmt = $pdo->prepare("
        INSERT INTO audit_log (entity_type, entity_id, action, meta, created_at)
        VALUES ('order', :id, 'paid', :meta, NOW())
    ");
    $stmt->execute([
        'id' => $orderId,
        'meta' => json_encode(['source' => 'pay_order.php'])
    ]);

    $pdo->commit();

    echo "Order {$orderId} paid successfully\n";

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}