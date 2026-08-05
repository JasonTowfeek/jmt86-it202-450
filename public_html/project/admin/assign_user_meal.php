<?php
// jmt86 - 08/05/2026
// Admin page for searching users and meals and applying relationships.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$errors = [];
$users = [];
$meals = [];

$user_search = $_GET["user_search"] ?? "";
$meal_search = $_GET["meal_search"] ?? "";

if (!is_string($user_search)) {
    $user_search = "";
}

if (!is_string($meal_search)) {
    $meal_search = "";
}

$user_search = trim($user_search);
$meal_search = trim($meal_search);

try {
    $db = getDB();

    /*
     * User search:
     * Uses a partial username and returns no more than 25 users.
     */
    $user_stmt = $db->prepare(
        "SELECT
            id,
            username,
            email
         FROM users
         WHERE username ILIKE :user_search
         ORDER BY username ASC
         LIMIT 25"
    );

    $user_stmt->execute([
        ":user_search" => "%" . $user_search . "%"
    ]);

    $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Meal search:
     * Searches friendly meal values and returns no more than 25 meals.
     */
    $meal_stmt = $db->prepare(
        "SELECT
            id,
            meal_name,
            category,
            cuisine,
            is_api
         FROM meals
         WHERE
            meal_name ILIKE :meal_search
            OR category ILIKE :meal_search
            OR cuisine ILIKE :meal_search
         ORDER BY meal_name ASC
         LIMIT 25"
    );

    $meal_stmt->execute([
        ":meal_search" => "%" . $meal_search . "%"
    ]);

    $meals = $meal_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log(
        "Assignment search failed: " .
        $e->getMessage()
    );

    $errors[] = "Unable to search users or meals.";
}

/*
 * Apply selected relationships.
 *
 * For every selected user-meal pair:
 * - Create the relationship if it does not exist.
 * - Remove the relationship if it already exists.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_role("Admin");

    $selected_users = $_POST["user_ids"] ?? [];
    $selected_meals = $_POST["meal_ids"] ?? [];

    if (!is_array($selected_users)) {
        $selected_users = [];
    }

    if (!is_array($selected_meals)) {
        $selected_meals = [];
    }

    $valid_user_ids = [];
    $valid_meal_ids = [];

    foreach ($selected_users as $user_id) {
        if (
            is_string($user_id)
            && ctype_digit($user_id)
            && (int)$user_id > 0
        ) {
            $valid_user_ids[] = (int)$user_id;
        }
    }

    foreach ($selected_meals as $meal_id) {
        if (
            is_string($meal_id)
            && ctype_digit($meal_id)
            && (int)$meal_id > 0
        ) {
            $valid_meal_ids[] = (int)$meal_id;
        }
    }

    $valid_user_ids = array_values(
        array_unique($valid_user_ids)
    );

    $valid_meal_ids = array_values(
        array_unique($valid_meal_ids)
    );

    if (empty($valid_user_ids)) {
        $errors[] = "Select at least one user.";
    }

    if (empty($valid_meal_ids)) {
        $errors[] = "Select at least one meal.";
    }

    if (empty($errors)) {
        $created_count = 0;
        $removed_count = 0;

        try {
            $db->beginTransaction();

            $check_stmt = $db->prepare(
                "SELECT id
                 FROM usermeals
                 WHERE user_id = :user_id
                   AND meal_id = :meal_id"
            );

            $insert_stmt = $db->prepare(
                "INSERT INTO usermeals (
                    user_id,
                    meal_id
                 )
                 VALUES (
                    :user_id,
                    :meal_id
                 )"
            );

            $delete_stmt = $db->prepare(
                "DELETE FROM usermeals
                 WHERE user_id = :user_id
                   AND meal_id = :meal_id"
            );

            foreach ($valid_user_ids as $user_id) {
                foreach ($valid_meal_ids as $meal_id) {
                    $pair = [
                        ":user_id" => $user_id,
                        ":meal_id" => $meal_id
                    ];

                    $check_stmt->execute($pair);

                    $existing_id = $check_stmt->fetchColumn();

                    if ($existing_id !== false) {
                        $delete_stmt->execute($pair);
                        $removed_count++;
                    } else {
                        $insert_stmt->execute($pair);
                        $created_count++;
                    }
                }
            }

            $db->commit();

            flash(
                "Assignments updated. Created: " .
                $created_count .
                ". Removed: " .
                $removed_count .
                ".",
                "success"
            );

            $query = http_build_query([
                "user_search" => $user_search,
                "meal_search" => $meal_search
            ]);

            header(
                "Location: " .
                project_url(
                    "admin/assign_user_meal.php?" .
                    $query
                )
            );

            exit;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            error_log(
                "Assignment update failed: " .
                $e->getMessage()
            );

            $errors[] = "Unable to update the assignments.";
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

    <title>Assign Meals to Users</title>

    <link
        rel="stylesheet"
        href="<?php echo project_url("styles.css"); ?>"
    >
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>Assign Meals to Users</h1>

        <form method="get">
            <h2>Search</h2>

            <label for="user_search">
                Username
            </label>

            <input
                id="user_search"
                name="user_search"
                value="<?php
                    echo htmlspecialchars($user_search);
                ?>"
                placeholder="Enter part of a username"
            >

            <label for="meal_search">
                Meal
            </label>

            <input
                id="meal_search"
                name="meal_search"
                value="<?php
                    echo htmlspecialchars($meal_search);
                ?>"
                placeholder="Meal, category, or cuisine"
            >

            <button type="submit">
                Search
            </button>
        </form>

        <form method="post">
            <input
                type="hidden"
                name="user_search"
                value="<?php
                    echo htmlspecialchars($user_search);
                ?>"
            >

            <input
                type="hidden"
                name="meal_search"
                value="<?php
                    echo htmlspecialchars($meal_search);
                ?>"
            >

            <div class="assignment-results">
                <section>
                    <h2>User Results</h2>

                    <p>
                        Showing up to 25 matching users.
                    </p>

                    <?php if (empty($users)): ?>

                        <p>No users matched your search.</p>

                    <?php else: ?>

                        <?php foreach ($users as $user): ?>

                            <label>
                                <input
                                    type="checkbox"
                                    name="user_ids[]"
                                    value="<?php
                                        echo (int)$user["id"];
                                    ?>"
                                >

                                <strong>
                                    <?php
                                        echo htmlspecialchars(
                                            $user["username"]
                                        );
                                    ?>
                                </strong>

                                <?php
                                    echo htmlspecialchars(
                                        $user["email"]
                                    );
                                ?>
                            </label>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </section>

                <section>
                    <h2>Meal Results</h2>

                    <p>
                        Showing up to 25 matching meals.
                    </p>

                    <?php if (empty($meals)): ?>

                        <p>No meals matched your search.</p>

                    <?php else: ?>

                        <?php foreach ($meals as $meal): ?>

                            <label>
                                <input
                                    type="checkbox"
                                    name="meal_ids[]"
                                    value="<?php
                                        echo (int)$meal["id"];
                                    ?>"
                                >

                                <strong>
                                    <?php
                                        echo htmlspecialchars(
                                            $meal["meal_name"]
                                        );
                                    ?>
                                </strong>

                                <?php
                                    echo htmlspecialchars(
                                        $meal["category"]
                                    );
                                ?>

                                |

                                <?php
                                    echo htmlspecialchars(
                                        $meal["cuisine"]
                                    );
                                ?>

                                |

                                <?php
                                    echo $meal["is_api"]
                                        ? "API"
                                        : "Manual";
                                ?>
                            </label>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </section>
            </div>

            <button type="submit">
                Apply Assignments
            </button>
        </form>
    </main>

    <?php render_flash_messages(); ?>
</body>

</html>