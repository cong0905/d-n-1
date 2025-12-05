<?php
require_once __DIR__ . '/db.php';
header('Content-Type: text/plain; charset=utf-8');
echo "Kết nối CSDL thành công.\n";
echo 'Host: ' . DB_HOST . ':' . DB_PORT . "\n";
echo 'DB: ' . DB_NAME . "\n";
