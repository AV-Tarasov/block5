<?php

$orderId = (int)($argv[1] ?? 0);

if (!$orderId) {
    echo "Usage: php race_test.php <order_id>\n";
    exit(1);
}

$processes = 10;

echo "Starting race test for order {$orderId}\n";

for ($i = 0; $i < $processes; $i++) {
    exec("php bin/pay_order.php {$orderId} > /tmp/pay_test_{$i}.log 2>&1 &");
}

echo "Processes started. Waiting...\n";

sleep(2);

// показываем результаты
for ($i = 0; $i < $processes; $i++) {
    echo "---- Process {$i} ----\n";
    echo file_get_contents("/tmp/pay_test_{$i}.log");
}

echo "Race test completed\n";