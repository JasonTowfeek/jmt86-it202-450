<?php
// jmt86 - 08/04/2026
// Shows meals saved by the logged-in user.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    flash("Please log in first.", "warning");
    header("Location: " . project_url("login.php"));
    exit;
}

$user_id = (int)$_SESSION["user"]["user_id"];
$meals = [];
$errors = [];

try {
    $db = getDB();

    $stmt = $db->prepare(
        "SELECT
            usermeals.id AS usermeal_id,
            meals.id AS meal_id,
            meals.meal_name,
            meals.category,
            meals.cuisine,
            meals.image_url,
            meals.is_api,
            usermeals.created
         FROM usermeals
         JOIN meals ON meals.id = usermeals.meal_id
         WHERE usermeals.user_id = :user_id
         ORDER BY usermeals.created DESC"
    );

    $stmt->execute([
        ":user_id" => $user_id
    ]);

    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("My meals lookup failed: " . $e->getMessage());
    $errors[] = "Unable to load your saved meals.";
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

    <title>My Saved Meals</title>

    <link
        rel="stylesheet"
        href="<?php echo project_url("styles.css"); ?>"
    >
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>My Saved Meals</h1>

        <?php if (empty($meals)): ?>

            <p>You have not saved any meals yet.</p>

        <?php else: ?>

            <form
                method="post"
                action="<?php
                    echo project_url(
                        "remove_all_saved_meals.php"
                    );
                ?>"
            >
                <button type="submit">
                    Remove All Saved Meals
                </button>
            </form>

            <?php foreach ($meals as $meal): ?>

                <article>
                    <h2>
                        <a
                            href="<?php
                                echo project_url(
                                    "meal_details.php?id=" .
                                    urlencode(
                                        (string)$meal["meal_id"]
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
                    </h2>

                    <?php if (!empty($meal["image_url"])): ?>

                        <img
                            src="<?php
                                echo htmlspecialchars(
                                    $meal["image_url"]
                                );
                            ?>"
                            alt="<?php
                                echo htmlspecialchars(
                                    $meal["meal_name"]
                                );
                            ?>"
                            width="200"
                        >

                    <?php endif; ?>

                    <p>
                        <strong>Category:</strong>

                        <?php
                            echo htmlspecialchars(
                                $meal["category"]
                            );
                        ?>
                    </p>

                    <p>
                        <strong>Cuisine:</strong>

                        <?php
                            echo htmlspecialchars(
                                $meal["cuisine"]
                            );
                        ?>
                    </p>

                    <p>
                        <strong>Source:</strong>

                        <?php
                            echo $meal["is_api"]
                                ? "API"
                                : "Manual";
                        ?>
                    </p>

                    <p>
                        <strong>Saved:</strong>

                        <?php
                            echo htmlspecialchars(
                                $meal["created"]
                            );
                        ?>
                    </p>

                    <form
                        method="post"
                        action="<?php
                            echo project_url(
                                "remove_saved_meal.php"
                            );
                        ?>"
                    >
                        <input
                            type="hidden"
                            name="usermeal_id"
                            value="<?php
                                echo (int)$meal["usermeal_id"];
                            ?>"
                        >

                        <button type="submit">
                            Remove Saved Meal
                        </button>
                    </form>
                </article>

            <?php endforeach; ?>

        <?php endif; ?>
    </main>

    <?php render_flash_messages(); ?>
</body>

</html>