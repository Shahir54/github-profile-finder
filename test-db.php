<?php
$host = "localhost";
$dbname = "github_finder";
$dbuser = "root";
$dbpass = "shahir123";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass
    );

    echo "SUCCESS: Database connected!";

    $stmt = $pdo->query("SELECT COUNT(*) FROM search_history");
    echo "<br>Search records: " . $stmt->fetchColumn();

} catch (PDOException $e) {
    echo "DATABASE ERROR: " . htmlspecialchars($e->getMessage());
}
