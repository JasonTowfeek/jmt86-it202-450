<?php
require_once(__DIR__ . "/../../lib/db.php");

$sqlFile = __DIR__ . "/../../sql/000_create_table_todo.sql";

try {
    $db = getDB();
    $sql = file_get_contents($sqlFile);
    $db->exec($sql);
    echo "SQL ran successfully";
} catch (Throwable $e) {
    echo "SQL failed. Check terminal.";
    error_log("run_init_db error: " . var_export($e, true));
}