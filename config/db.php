<?php
/**
 * GlobeTrek Adventures - Database Connection & Initialization
 * Provides PDO connection with automatic SQLite database creation and fallback.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_FILE', __DIR__ . '/../database/globetrek.sqlite');

function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dbDir = dirname(DB_FILE);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            $pdo = new PDO('sqlite:' . DB_FILE);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("PRAGMA foreign_keys = ON;");
        } catch (PDOException $e) {
            die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/**
 * Executes a query with bound parameters
 */
function db_query($sql, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all rows
 */
function db_fetch_all($sql, $params = []) {
    return db_query($sql, $params)->fetchAll();
}

/**
 * Fetch a single row
 */
function db_fetch_one($sql, $params = []) {
    return db_query($sql, $params)->fetch();
}

/**
 * Fetch a single scalar value
 */
function db_fetch_val($sql, $params = []) {
    $row = db_query($sql, $params)->fetch(PDO::FETCH_NUM);
    return $row ? $row[0] : null;
}

/**
 * Insert or update and return last insert ID
 */
function db_insert($sql, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $db->lastInsertId();
}
