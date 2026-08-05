<?php
// jmt86 - 08/04/2026
// Removes all saved meals for the logged-in user.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    flash("Please log in first.", "warning");
    header("Location: " . project_url("login.php"));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash("Invalid request.", "danger");
    header("Location: " . project_url("my_meals.php"));
    exit;
}

$user_id = (int)$_SESSION["user"]["user_id"];

try {
    $db = getDB();

    $stmt = $db->prepare(
        "DELETE FROM usermeals
         WHERE user_id = :user_id"
    );

    $stmt->execute([
        ":user_id" => $user_id
    ]);

    if ($stmt->rowCount() > 0) {
        flash("All saved meals removed successfully.", "success");
    } else {
        flash("You do not have any saved meals to remove.", "warning");
    }
} catch (PDOException $e) {
    error_log("Remove all saved meals failed: " . $e->getMessage());
    flash("Unable to remove your saved meals.", "danger");
}

header("Location: " . project_url("my_meals.php"));
exit;