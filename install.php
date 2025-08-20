<?php
$db = new SQLite3('users.db');
$db->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY, username TEXT, password TEXT)");
$db->exec("DELETE FROM users");
$db->exec("INSERT INTO users(username,password) VALUES('admin','12345')");
echo "Database created. User: admin / 12345";
?>