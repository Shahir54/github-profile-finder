
<?php

require_once __DIR__ . "/db.php";

function saveSearch($pdo, $username)
{
    if (!$pdo) {
        return;
    }

    try {
        $sql = "INSERT INTO search_history (username)
                VALUES (:username)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "username" => $username
        ]);

    } catch (PDOException $e) {
        error_log("Could not save search history.");
    }
}

// Return recent searches when this file is requested directly.
if (basename($_SERVER["SCRIPT_NAME"]) === "history.php") {
    header("Content-Type: application/json; charset=utf-8");

    if (!$pdo) {
        http_response_code(503);
        echo json_encode([
            "error" => "Search history is temporarily unavailable."
        ]);
        exit;
    }

    try {
        $sql = "SELECT username, searched_at
                FROM search_history
                ORDER BY id DESC
                LIMIT 10";

        $stmt = $pdo->query($sql);

        echo json_encode($stmt->fetchAll());

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Could not load search history."
        ]);
    }
}
