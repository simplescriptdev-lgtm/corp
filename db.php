<?php
// db.php — SQLite bootstrap + schema (idempotent).
// Creates/opens data.sqlite, ensures tables exist, returns PDO via db()

function db() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dbFile = __DIR__ . '/data.sqlite';
    $pdo = new PDO('sqlite:' . $dbFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    // Users
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL
    )');

    // Capital inflows (Рух капіталу)
    $pdo->exec('CREATE TABLE IF NOT EXISTS capital_inflows (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        source TEXT NOT NULL,     -- owner/bank/etc
        category TEXT,            -- optional category
        amount REAL NOT NULL,
        created_at TEXT NOT NULL
    )');

    // Owner withdrawals
    $pdo->exec('CREATE TABLE IF NOT EXISTS owner_withdrawals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        amount REAL NOT NULL,
        note TEXT,
        created_at TEXT NOT NULL
    )');

    // Operational withdrawals
    $pdo->exec('CREATE TABLE IF NOT EXISTS operational_withdrawals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        amount REAL NOT NULL,
        note TEXT,
        created_at TEXT NOT NULL
    )');

    // IT withdrawals
    $pdo->exec('CREATE TABLE IF NOT EXISTS it_withdrawals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        amount REAL NOT NULL,
        note TEXT,
        created_at TEXT NOT NULL
    )');

    // Charity (25/75)
    $pdo->exec('CREATE TABLE IF NOT EXISTS charity_outflows (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        part TEXT NOT NULL,      -- "25" / "75"
        amount REAL NOT NULL,
        note TEXT,
        created_at TEXT NOT NULL
    )');

    // Insurance UAH (40%)
    $pdo->exec('CREATE TABLE IF NOT EXISTS insurance_sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        amount REAL NOT NULL DEFAULT 0,  -- стартова сума в грн
        note TEXT,
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS insurance_investments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        source_id INTEGER NOT NULL,
        amount REAL NOT NULL,            -- грн
        note TEXT,
        created_at TEXT NOT NULL,
        FOREIGN KEY(source_id) REFERENCES insurance_sources(id) ON DELETE CASCADE
    )');

    // Insurance FX (40%)
    $pdo->exec('CREATE TABLE IF NOT EXISTS insurance_fx_sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        currency TEXT NOT NULL,                   -- "USD", "EUR", etc
        amount_currency REAL NOT NULL DEFAULT 0,  -- стартова кількість у валюті
        note TEXT,
        display_rate REAL,                        -- курс для ₴-еквіваленту
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS insurance_fx_investments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        source_id INTEGER NOT NULL,
        amount_uah REAL NOT NULL,
        rate REAL NOT NULL,
        quantity_currency REAL NOT NULL,         -- amount_uah / rate
        note TEXT,
        created_at TEXT NOT NULL,
        FOREIGN KEY(source_id) REFERENCES insurance_fx_sources(id) ON DELETE CASCADE
    )');

    // Default admin (once)
    $row = $pdo->query('SELECT COUNT(*) AS c FROM users')->fetch();
    if (((int)($row['c'] ?? 0)) === 0) {
        $hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute(['admin', $hash]);
    }
    return $pdo;
}
