<?php
$host = 'localhost:3307';
$dbname = 'review_db_final';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- [AUTO SCHEMA UPDATE] ---
    try {
        $check = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'helpful_votes'");
        if (!$check->fetch()) {
            $pdo->exec("ALTER TABLE reviews ADD COLUMN helpful_votes INT DEFAULT 0");
        }
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS review_interactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            review_id INT NOT NULL,
            type VARCHAR(50) NOT NULL, -- e.g. 'helpful'
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_interaction (user_id, review_id, type)
        )");
        
    } catch (Exception $e) {
        // Suppress schema errors
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
