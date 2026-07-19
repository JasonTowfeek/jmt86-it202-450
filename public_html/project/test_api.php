<?php
// jmt86 - 07/18/2026
// Test page for TheMealDB API.

require_once(__DIR__ . "/../../lib/app.php");

$search = "chicken";
$meals = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $search = trim($_POST["search"] ?? "");
    $source = $_POST["source"] ?? "";

    if ($source === "sample") {
        $meals = search_meals($search, true, $errors);
    } elseif ($source === "live") {
        $meals = search_meals($search, false, $errors);
    } else {
        $errors[] = "Please choose a valid option.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TheMealDB API Test</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php require(__DIR__ . "/../../partials/nav.php"); ?>

    <main>
        <h1>TheMealDB API Test</h1>

        <form method="POST">
            <label for="search">Meal name:</label>

            <input
                type="text"
                id="search"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit" name="source" value="sample">
                Test Cached Sample
            </button>

            <button type="submit" name="source" value="live">
                Test Live API
            </button>
        </form>

        <?php if (!empty($errors)): ?>
            <h2>Errors</h2>

            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($meals)): ?>
            <h2>Meal Results</h2>

            <?php foreach ($meals as $meal): ?>
                <div>
                    <h3><?php echo htmlspecialchars($meal["name"]); ?></h3>

                    <p>
                        <strong>ID:</strong>
                        <?php echo htmlspecialchars($meal["id"]); ?>
                    </p>

                    <p>
                        <strong>Category:</strong>
                        <?php echo htmlspecialchars($meal["category"]); ?>
                    </p>

                    <p>
                        <strong>Cuisine:</strong>
                        <?php echo htmlspecialchars($meal["cuisine"]); ?>
                    </p>

                    <?php if ($meal["image"] !== ""): ?>
                        <img
                            src="<?php echo htmlspecialchars($meal["image"]); ?>"
                            alt="<?php echo htmlspecialchars($meal["name"]); ?>"
                            width="250"
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>