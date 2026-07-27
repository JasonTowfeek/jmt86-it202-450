<?php
// jmt86 - 07/27/2026
// Allows an Admin to safely edit approved meal fields.

require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$meal = null;

$id = $_GET["id"] ?? $_POST["id"] ?? "";

if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    flash("Invalid meal ID.", "warning");
    header("Location: " . project_url("admin/list_meals.php"));
    exit;
}

$meal_id = (int)$id;

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
    error_log("Load meal for edit failed: " . $e->getMessage());
    $errors[] = "Unable to load this meal right now.";
}

if (!$meal && empty($errors)) {
    flash("Meal not found.", "warning");
    header("Location: " . project_url("admin/list_meals.php"));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $meal) {
    $meal_name = $_POST["meal_name"] ?? "";
    $category = $_POST["category"] ?? "";
    $cuisine = $_POST["cuisine"] ?? "";
    $image_url = $_POST["image_url"] ?? "";

    if (!is_string($meal_name) || trim($meal_name) === "") {
        $errors[] = "Enter a meal name.";
    }

    if (!is_string($category) || trim($category) === "") {
        $errors[] = "Enter a category.";
    }

    if (!is_string($cuisine) || trim($cuisine) === "") {
        $errors[] = "Enter a cuisine.";
    }

    if (!is_string($image_url)) {
        $image_url = "";
    }

    $image_url = trim($image_url);

    if ($image_url !== "" && filter_var($image_url, FILTER_VALIDATE_URL) === false) {
        $errors[] = "Enter a valid image URL.";
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare(
                "UPDATE meals
                 SET
                    meal_name = :meal_name,
                    category = :category,
                    cuisine = :cuisine,
                    image_url = :image_url,
                    modified = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );

            $stmt->execute([
                ":meal_name" => trim($meal_name),
                ":category" => trim($category),
                ":cuisine" => trim($cuisine),
                ":image_url" => $image_url,
                ":id" => $meal_id
            ]);

            flash("Meal updated successfully.", "success");

            header(
                "Location: " .
                project_url("meal_details.php?id=" . $meal_id)
            );
            exit;
        } catch (PDOException $e) {
            error_log("Update meal failed: " . $e->getMessage());
            $errors[] = "Unable to update this meal.";
        }
    }

    // Keep submitted values visible after an error.
    $meal["meal_name"] = is_string($meal_name) ? $meal_name : "";
    $meal["category"] = is_string($category) ? $category : "";
    $meal["cuisine"] = is_string($cuisine) ? $cuisine : "";
    $meal["image_url"] = $image_url;
}

flash_errors($errors);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Meal</title>
    <link rel="stylesheet" href="<?php echo project_url("styles.css"); ?>">
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>Edit Meal</h1>

        <?php if ($meal): ?>
            <form method="post">
                <input
                    type="hidden"
                    name="id"
                    value="<?php echo (int)$meal["id"]; ?>"
                >

                <label for="meal_name">Meal name</label>
                <input
                    id="meal_name"
                    name="meal_name"
                    maxlength="150"
                    required
                    value="<?php echo htmlspecialchars($meal["meal_name"]); ?>"
                >

                <label for="category">Category</label>
                <input
                    id="category"
                    name="category"
                    maxlength="100"
                    required
                    value="<?php echo htmlspecialchars($meal["category"]); ?>"
                >

                <label for="cuisine">Cuisine</label>
                <input
                    id="cuisine"
                    name="cuisine"
                    maxlength="100"
                    required
                    value="<?php echo htmlspecialchars($meal["cuisine"]); ?>"
                >

                <label for="image_url">Image URL</label>
                <input
                    id="image_url"
                    name="image_url"
                    type="url"
                    maxlength="500"
                    value="<?php echo htmlspecialchars($meal["image_url"]); ?>"
                >

                <button type="submit">
                    Save Changes
                </button>
            </form>

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
        <?php endif; ?>
    </main>

    <?php render_flash_messages(); ?>
</body>
</html>