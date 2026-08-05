<?php
// jmt86 - 08/05/2026
// Admin-only handler for removing one user-meal association.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash("Invalid request.", "danger");
    header(
        "Location: " .
        project_url("admin/list_user_meals.php")
    );
    exit;
}

$usermeal_id = $_POST["usermeal_id"] ?? "";

if (
    !is_string($usermeal_id)
    || !ctype_digit($usermeal_id)
    || (int)$usermeal_id <= 0
) {
    flash("Invalid relationship ID.", "danger");
    header(
        "Location: " .
        project_url("admin/list_user_meals.php")
    );
    exit;
}

$usermeal_id = (int)$usermeal_id;

try {
    $db = getDB();

    $stmt = $db->prepare(
        "DELETE FROM usermeals
         WHERE id = :usermeal_id"
    );

    $stmt->execute([
        ":usermeal_id" => $usermeal_id
    ]);

    if ($stmt->rowCount() === 1) {
        flash(
            "User-meal association removed successfully.",
            "success"
        );
    } else {
        flash(
            "User-meal association not found.",
            "warning"
        );
    }
} catch (PDOException $e) {
    error_log(
        "Admin association removal failed: " .
        $e->getMessage()
    );

    flash(
        "Unable to remove the user-meal association.",
        "danger"
    );
}

header(
    "Location: " .
    project_url("admin/list_user_meals.php")
);

exit;