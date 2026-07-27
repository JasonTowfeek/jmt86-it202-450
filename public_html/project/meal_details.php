<?php
// jmt86 - 07/27/2026
// Public detail page for one meal record.

require_once(__DIR__ . "/../../lib/app.php");

$errors = [];
$meal = null;

$id = $_GET["id"] ?? "";

if (!is_string($id) || !ctype_digit($id)) {
    flash("Invalid meal ID.", "warning");
    header("Location: " . project_url("admin/list_meals.php"));
    exit;
}

$meal_id = (int)$id;

if ($meal_id < 1) {
    flash("Invalid meal ID.", "warning");
    header("Location: " . project_url("admin/list_meals.php"));
    exit;
}

try {
    $db = getDB();

    $stmt = $db->prepare(
        "SELECT
            id,
            api_meal_id,
            meal_name,
            category,
            cuisine,
            image_url,
            is_api,
            created,
            modified
         FROM meals
         WHERE id = :id"
    );

    $stmt->execute([
        ":id" => $meal_id
    ]);

    $meal = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Meal details failed: " . $e->getMessage());
    $errors[] = "Unable to load this meal right now.";
}

if (!$meal && empty($errors)) {
    flash("Meal not found.", "warning");
    header("Location: " . project_url("admin/list_meals.php"));
    exit;
}

flash_errors($errors);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Details</title>
    <link rel="stylesheet" href="<?php echo project_url("styles.css"); ?>">
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>Meal Details</h1>

        <?php if ($meal): ?>
            <h2><?php echo htmlspecialchars($meal["meal_name"]); ?></h2>

            <?php if (!empty($meal["image_url"])): ?>
                <img
                    src="<?php echo htmlspecialchars($meal["image_url"]); ?>"
                    alt="<?php echo htmlspecialchars($meal["meal_name"]); ?>"
                    width="300"
                >
            <?php endif; ?>

            <p>
                <strong>Category:</strong>
                <?php echo htmlspecialchars($meal["category"]); ?>
            </p>

            <p>
                <strong>Cuisine:</strong>
                <?php echo htmlspecialchars($meal["cuisine"]); ?>
            </p>

            <p>
                <strong>Source:</strong>
                <?php echo $meal["is_api"] ? "TheMealDB API" : "Manual"; ?>
            </p>

            <?php if (!empty($meal["api_meal_id"])): ?>
                <p>
                    <strong>API Meal ID:</strong>
                    <?php echo htmlspecialchars($meal["api_meal_id"]); ?>
                </p>
            <?php endif; ?>

            <p>
                <strong>Created:</strong>
                <?php echo htmlspecialchars($meal["created"]); ?>
            </p>

            <p>
                <strong>Last Modified:</strong>
                <?php echo htmlspecialchars($meal["modified"]); ?>
            </p>

            <?php if (has_role("Admin")): ?>
                <p>
                    <a
                        href="<?php
                            echo project_url(
                                "admin/edit_meal.php?id=" .
                                urlencode((string)$meal["id"])
                            );
                        ?>"
                    >
                        Edit Meal
                    </a>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php render_flash_messages(); ?>
</body>
</html>