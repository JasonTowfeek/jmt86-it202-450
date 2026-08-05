<?php
// jmt86 - 08/04/2026
// Removes one saved meal from the logged-in user's list.

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

$usermeal_id = $_POST["usermeal_id"] ?? "";

if (
    !is_string($usermeal_id)
    || !ctype_digit($usermeal_id)
    || (int)$usermeal_id <= 0
) {
    flash("Invalid saved meal.", "danger");
    header("Location: " . project_url("my_meals.php"));
    exit;
}

$user_id = (int)$_SESSION["user"]["user_id"];
$usermeal_id = (int)$usermeal_id;

try {
    $db = getDB();

    $stmt = $db->prepare(
        "DELETE FROM usermeals
         WHERE id = :usermeal_id
           AND user_id = :user_id"
    );

    $stmt->execute([
        ":usermeal_id" => $usermeal_id,
        ":user_id" => $user_id
    ]);

    if ($stmt->rowCount() === 1) {
        flash("Saved meal removed successfully.", "success");
    } else {
        flash("Saved meal not found.", "warning");
    }
} catch (PDOException $e) {
    error_log("Remove saved meal failed: " . $e->getMessage());
    flash("Unable to remove the saved meal.", "danger");
}

header("Location: " . project_url("my_meals.php"));
exit;