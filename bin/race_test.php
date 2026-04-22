<?php

$orderId = (int)($argv[1] ?? 0);

if (!$orderId) {
    echo "Usage: php race_test.php <order_id>\n";
    exit(1);
}

$processes = 10;
$commands = [];

echo "Starting race test for order {$orderId}\n";

for ($i = 0; $i < $processes; $i++) {
    $commands[] = "php bin/pay_order.php {$orderId}";
}

foreach ($commands as $cmd) {
    pclose(popen($cmd, 'r'));
}

echo "All processes started...\n";

sleep(3);

echo "Check results in DB\n";