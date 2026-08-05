<?php
// jmt86 - 08/04/2026
// Admin page showing meals that no user has saved yet.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$meals = [];
$errors = [];

try {
    $db = getDB();

    $stmt = $db->prepare(
        "SELECT
            meals.id,
            meals.meal_name,
            meals.category,
            meals.cuisine,
            meals.is_api,
            meals.created
         FROM meals
         LEFT JOIN usermeals
           ON usermeals.meal_id = meals.id
         WHERE usermeals.id IS NULL
         ORDER BY meals.created DESC"
    );

    $stmt->execute();

    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log(
        "Unassociated meals list failed: " .
        $e->getMessage()
    );

    $errors[] = "Unable to load unassociated meals.";
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

    <title>Unassociated Meals</title>

    <link
        rel="stylesheet"
        href="<?php echo project_url("styles.css"); ?>"
    >
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>Unassociated Meals</h1>

        <?php if (empty($meals)): ?>

            <p>Every meal has been saved by at least one user.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Meal</th>
                        <th>Category</th>
                        <th>Cuisine</th>
                        <th>Source</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($meals as $meal): ?>

                        <tr>
                            <td>
                                <a
                                    href="<?php
                                        echo project_url(
                                            "meal_details.php?id=" .
                                            urlencode(
                                                (string)$meal["id"]
                                            )
                                        );
                                    ?>"
                                >
                                    <?php
                                        echo htmlspecialchars(
                                            $meal["meal_name"]
                                        );
                                    ?>
                                </a>
                            </td>

                            <td>
                                <?php
                                    echo htmlspecialchars(
                                        $meal["category"]
                                    );
                                ?>
                            </td>

                            <td>
                                <?php
                                    echo htmlspecialchars(
                                        $meal["cuisine"]
                                    );
                                ?>
                            </td>

                            <td>
                                <?php
                                    echo $meal["is_api"]
                                        ? "API"
                                        : "Manual";
                                ?>
                            </td>

                            <td>
                                <?php
                                    echo htmlspecialchars(
                                        $meal["created"]
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