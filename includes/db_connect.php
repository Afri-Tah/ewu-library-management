<?php
/**
 * Database connection.
 * Credentials are isolated here so they can be swapped per-environment
 * without touching application code.
 */

// Make mysqli throw exceptions on error instead of failing silently /
// requiring a manual check after every single call.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "ewu_library";

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Never leak DB internals to the browser in a real deployment;
    // for this course project we show a generic message and log the real one.
    error_log("DB connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
