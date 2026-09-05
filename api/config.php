<?php
// Prevent direct access to this file
if (!defined('LEADS_API')) {
    http_response_code(403);
    exit('Direct access not permitted');
}

// Hostinger MySQL database credentials.
// Get these from hPanel -> Databases -> MySQL Databases (phpMyAdmin).
define('DB_HOST', 'localhost');
define('DB_NAME', 'u261758575_manipalamisha');
define('DB_USER', 'u261758575_manipalamisha');
define('DB_PASS', 'm3@G$HxmAr?C');

function getDbConnection(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}
