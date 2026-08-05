<?php
// jmt86 - 08/04/2026
// Public profile page showing one user and their saved meals.

require_once(__DIR__ . "/../../lib/app.php");

$user = null;
$meals = [];
$errors = [];

$user_id = $_GET["id"] ?? "";

if (
    !is_string($user_id)
    || !ctype_digit($user_id)
    || (int)$user_id <= 0
) {
    flash("Invalid user.", "warning");
    header("Location: " . project_url("meals.php"));
    exit;
}

$user_id = (int)$user_id;

try {
    $db = getDB();

    $user_stmt = $db->prepare(
        "SELECT
            id,
            username
         FROM users
         WHERE id = :user_id"
    );

    $user_stmt->execute([
        ":user_id" => $user_id
    ]);

    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        flash("User not found.", "warning");
        header("Location: " . project_url("meals.php"));
        exit;
    }

    $meal_stmt = $db->prepare(
        "SELECT
            meals.id,
            meals.meal_name,
            meals.category,
            meals.cuisine,
            meals.image_url,
            meals.is_api,
            usermeals.created
         FROM usermeals
         JOIN meals
           ON meals.id = usermeals.meal_id
         WHERE usermeals.user_id = :user_id
         ORDER BY usermeals.created DESC"
    );

    $meal_stmt->execute([
        ":user_id" => $user_id
    ]);

    $meals = $meal_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Public user profile failed: " . $e->getMessage());
    $errors[] = "Unable to load this user profile.";
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

    <title>User Profile</title>

    <link
        rel="stylesheet"
        href="<?php echo project_url("styles.css"); ?>"
    >
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>User Profile</h1>

        <h2>
            <?php echo htmlspecialchars($user["username"]); ?>
        </h2>

        <h3>Saved Meals</h3>

        <?php if (empty($meals)): ?>

            <p>This user has not saved any meals yet.</p>

        <?php else: ?>

            <?php foreach ($meals as $meal): ?>

                <article>
                    <h4>
                        <a
                            href="<?php
                                echo project_url(
                                    "meal_details.php?id=" .
                                    urlencode((string)$meal["id"])
                                );
                            ?>"
                        >
                            <?php
                                echo htmlspecialchars(
                                    $meal["meal_name"]
                                );
                            ?>
                        </a>
                    </h4>

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
                        <?php echo htmlspecialchars($meal["category"]); ?>
                    </p>

                    <p>
                        <strong>Cuisine:</strong>
                        <?php echo htmlspecialchars($meal["cuisine"]); ?>
                    </p>

                    <p>
                        <strong>Source:</strong>
                        <?php echo $meal["is_api"] ? "API" : "Manual"; ?>
                    </p>

                    <p>
                        <strong>Saved:</strong>
                        <?php echo htmlspecialchars($meal["created"]); ?>
                    </p>
                </article>

            <?php endforeach; ?>

        <?php endif; ?>
    </main>

    <?php render_flash_messages(); ?>
</body>

</html>