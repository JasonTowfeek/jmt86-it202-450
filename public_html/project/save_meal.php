<?php
// jmt86 - 08/04/2026
// Saves one meal to the logged-in user's meal list.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    flash("Please log in first.", "warning");
    header("Location: " . project_url("login.php"));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash("Invalid request.", "danger");
    header("Location: " . project_url("meals.php"));
    exit;
}

$meal_id = $_POST["meal_id"] ?? "";

if (!is_string($meal_id) || !ctype_digit($meal_id) || (int)$meal_id <= 0) {
    flash("Invalid meal.", "danger");
    header("Location: " . project_url("meals.php"));
    exit;
}

$user_id = (int)$_SESSION["user"]["user_id"];
$meal_id = (int)$meal_id;

try {
    $db = getDB();

    $stmt = $db->prepare(
        "INSERT INTO usermeals (user_id, meal_id)
         VALUES (:user_id, :meal_id)
         ON CONFLICT (user_id, meal_id)
         DO NOTHING"
    );

    $stmt->execute([
        ":user_id" => $user_id,
        ":meal_id" => $meal_id
    ]);

    if ($stmt->rowCount() === 1) {
        flash("Meal saved successfully.", "success");
    } else {
        flash("You already saved this meal.", "warning");
    }
} catch (PDOException $e) {
    error_log("Save meal failed: " . $e->getMessage());
    flash("Unable to save the meal.", "danger");
}

header("Location: " . project_url("meals.php"));
exit;