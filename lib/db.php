<?php
// Keep errors out of the page response and rely on logs/terminal output.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

function getDB()
{
    static $db = null;

    if ($db === null) {
        try {
            require_once(__DIR__ . "/config.php");

            $connection_string = "pgsql:host=$dbhost;port=5432;dbname=$dbdatabase;sslmode=require";

            $db = new PDO($connection_string, $dbuser, $dbpass, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ));
        } catch (Throwable $e) {
            error_log("getDB() error: " . var_export($e, true));
            $db = null;
            throw new Exception("Error connecting to database, see logs for further information");
        }
    }

    return $db;
}