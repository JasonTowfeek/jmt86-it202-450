<?php
// jmt86 - 07/27/2026
// Deletes one meal record using POST only.

require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash("Invalid delete request.", "warning");
    header("Location: " . project_url("admin/list_meals.php"));
    exit;
}

$id = $_POST["id"] ?? "";

if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    flash("Invalid meal ID.", "warning");
    header("Location: " . project_url("admin/list_meals.php"));
    exit;
}

$meal_id = (int)$id;

try {
    $db = getDB();

    $stmt = $db->prepare(
        "DELETE FROM meals
         WHERE id = :id"
    );

    $stmt->execute([
        ":id" => $meal_id
    ]);

    if ($stmt->rowCount() === 1) {
        flash("Meal deleted successfully.", "success");
    } else {
        flash("Meal was not found.", "warning");
    }
} catch (PDOException $e) {
    error_log("Delete meal failed: " . $e->getMessage());
    flash("Unable to delete this meal.", "danger");
}

header("Location: " . project_url("admin/list_meals.php"));
exit;