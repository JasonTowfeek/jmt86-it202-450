<?php
// jmt86 - 08/04/2026
// Admin page showing all user-to-meal associations.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$associations = [];
$errors = [];

try {
    $db = getDB();

    $stmt = $db->prepare(
        "SELECT
            usermeals.id AS usermeal_id,
            users.id AS user_id,
            users.username,
            users.email,
            meals.id AS meal_id,
            meals.meal_name,
            meals.category,
            meals.cuisine,
            meals.is_api,
            usermeals.created
         FROM usermeals
         JOIN users
           ON users.id = usermeals.user_id
         JOIN meals
           ON meals.id = usermeals.meal_id
         ORDER BY usermeals.created DESC"
    );

    $stmt->execute();

    $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log(
        "Admin user meal list failed: " .
        $e->getMessage()
    );

    $errors[] = "Unable to load user meal associations.";
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

    <title>User Meal Associations</title>

    <link
        rel="stylesheet"
        href="<?php echo project_url("styles.css"); ?>"
    >
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>User Meal Associations</h1>

        <?php if (empty($associations)): ?>

            <p>No users have saved any meals yet.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Meal</th>
                        <th>Category</th>
                        <th>Cuisine</th>
                        <th>Source</th>
                        <th>Saved</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($associations as $row): ?>

                        <tr>
                            <td>
                                <a
                                    href="<?php
                                        echo project_url(
                                            "user_profile.php?id=" .
                                            urlencode(
                                                (string)$row["user_id"]
                                            )
                                        );
                                    ?>"
                                >
                                    <?php
                                        echo htmlspecialchars(
                                            $row["username"]
                                        );
                                    ?>
                                </a>
                            </td>

                            <td>
                                <?php
                                    echo htmlspecialchars(
                                        $row["email"]
                                    );
                                ?>
                            </td>

                            <td>
                                <a
                                    href="<?php
                                        echo project_url(
                                            "meal_details.php?id=" .
                                            urlencode(
                                                (string)$row["meal_id"]
                                            )
                                        );
                                    ?>"
                                >
                                    <?php
                                        echo htmlspecialchars(
                                            $row["meal_name"]
                                        );
                                    ?>
                                </a>
                            </td>

                            <td>
                                <?php
                                    echo htmlspecialchars(
                                        $row["category"]
                                    );
                                ?>
                            </td>

                            <td>
                                <?php
                                    echo htmlspecialchars(
                                        $row["cuisine"]
                                    );
                                ?>
                            </td>

                            <td>
                                <?php
                                    echo $row["is_api"]
                                        ? "API"
                                        : "Manual";
                                ?>
                            </td>

                            <td>
                                <?php
                                    echo htmlspecialchars(
                                        $row["created"]
                                    );
                                ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </main>

    <?php render_flash_messages(); ?>
</body>

</html>