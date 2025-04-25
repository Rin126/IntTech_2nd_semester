<?php
$logDbPath = __DIR__ . '/requests_log.db';
try {
    $logDbh = new PDO("sqlite:$logDbPath");
    $logDbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $logDbh->exec("CREATE TABLE IF NOT EXISTS request_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        request_url TEXT NOT NULL,
        endpoint TEXT NOT NULL,
        param1_name TEXT,
        param1_value TEXT,
        param2_name TEXT,
        param2_value TEXT,
        ip_address TEXT,
        user_agent TEXT
    )");
} catch(PDOException $e) {
    error_log("Помилка підключення до SQLite: " . $e->getMessage());
    die();
}
?>