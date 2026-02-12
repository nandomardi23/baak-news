<?php
try {
    $dsn = "mysql:host=127.0.0.1;port=3306;dbname=baak-news";
    $pdo = new PDO($dsn, "root", "");
    echo "CONNECTED TO DB SUCCESSFULLY\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    $count = $stmt->fetchColumn();
    echo "Settings count: $count\n";
    
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('neo_feeder_url', 'neo_feeder_username')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['key']}: {$row['value']}\n";
    }
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
