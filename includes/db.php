<?php
/**
 * Database connection for Semenyih Joy Methodist Church.
 *
 * Edit the constants below to match your MySQL server.
 * If the database or table doesn't exist yet, get_db() creates them
 * automatically, so importing joy_db.sql is optional.
 *
 * Not requested directly by the browser and never included from .html files.
 */

const DB_HOST = "localhost";
const DB_PORT = 3306;          // XAMPP/WAMP default. MAMP often uses 8889.
const DB_NAME = "joy_db";
const DB_USER = "root";
const DB_PASS = "";            // XAMPP default is empty. MAMP default is "root".

// While setting up, true shows the real error message in the browser.
// Set to false before going live so visitors never see technical details.
const DEBUG = true;

function db_options(): array {
    return [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
}

function get_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    if (!extension_loaded("pdo_mysql")) {
        throw new PDOException("PHP's pdo_mysql extension is not enabled. Enable extension=pdo_mysql in php.ini and restart Apache.");
    }

    $server = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";

    try {
        $pdo = new PDO($server . ";dbname=" . DB_NAME, DB_USER, DB_PASS, db_options());
    } catch (PDOException $e) {
        // 1049 = "Unknown database": create it, then connect again.
        if ((int)$e->getCode() === 1049) {
            $tmp = new PDO($server, DB_USER, DB_PASS, db_options());
            $tmp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo = new PDO($server . ";dbname=" . DB_NAME, DB_USER, DB_PASS, db_options());
        } else {
            throw $e;
        }
    }

    // Make sure the table exists (same definition as joy_db.sql).
    $pdo->exec("CREATE TABLE IF NOT EXISTS registration (
        registration_id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        age VARCHAR(10) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        member VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (registration_id),
        UNIQUE KEY uniq_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    return $pdo;
}

/** Friendly message for visitors; real reason too while DEBUG is on. */
function db_error_message(Throwable $e): string {
    $base = "Server error. Please try again later.";
    return DEBUG ? $base . " (Details: " . $e->getMessage() . ")" : $base;
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

function start_app_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "samesite" => "Lax",
        ]);
        session_start();
    }
}
