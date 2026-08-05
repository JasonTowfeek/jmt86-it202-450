<?php
// jmt86 - 08/04/2026
// Admin page for assigning one meal to one user.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$users = [];
$meals = [];
$errors = [];

try {
    $db = getDB();

    $user_stmt = $db->prepare(
        "SELECT id, username, email
         FROM users
         ORDER BY username ASC"
    );

    $user_stmt->execute();
    $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

    $meal_stmt = $db->prepare(
        "SELECT id, meal_name
         FROM meals
         ORDER BY meal_name ASC"
    );

    $meal_stmt->execute();
    $meals = $meal_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Assignment page load failed: " . $e->getMessage());
    $errors[] = "Unable to load users or meals.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST["user_id"] ?? "";
    $meal_id = $_POST["meal_id"] ?? "";

    if (
        !is_string($user_id)
        || !ctype_digit($user_id)
        || (int)$user_id <= 0
    ) {
        $errors[] = "Please select a valid user.";
    }

    if (
        !is_string($meal_id)
        || !ctype_digit($meal_id)
        || (int)$meal_id <= 0
    ) {
        $errors[] = "Please select a valid meal.";
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare(
                "INSERT INTO usermeals (user_id, meal_id)
                 VALUES (:user_id, :meal_id)
                 ON CONFLICT (user_id, meal_id)
                 DO NOTHING"
            );

            $stmt->execute([
                ":user_id" => (int)$user_id,
                ":meal_id" => (int)$meal_id
            ]);

            if ($stmt->rowCount() === 1) {
                flash("Meal assigned to user successfully.", "success");
            } else {
                flash("That user already has this meal.", "warning");
            }

            header(
                "Location: " .
                project_url("admin/assign_user_meal.php")
            );
            exit;
        } catch (PDOException $e) {
            error_log("User meal assignment failed: " . $e->getMessage());
            $errors[] = "Unable to assign the meal.";
        }
    }
}

flash_errors($errors);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Assign Meal to User</title>

    <link
        rel="stylesheet"
        href="<?php echo project_url("styles.css"); ?>"
    >
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>Assign Meal to User</h1>

        <form method="post">
            <label for="user_id">User</label>

            <select id="user_id" name="user_id" required>
                <option value="">Choose a user</option>

                <?php foreach ($users as $user): ?>
                    <option value="<?php echo (int)$user["id"]; ?>">
                        <?php
                            echo htmlspecialchars(
                                $user["username"] .
                                " - " .
                                $user["email"]
                            );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="meal_id">Meal</label>

            <select id="meal_id" name="meal_id" required>
                <option value="">Choose a meal</option>

                <?php foreach ($meals as $meal): ?>
                    <option value="<?php echo (int)$meal["id"]; ?>">
                        <?php
                            echo htmlspecialchars(
                                $meal["meal_name"]
                            );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">
                Assign Meal
            </button>
        </form>
    </main>

    <?php render_flash_messages(); ?>
</body>

</html>