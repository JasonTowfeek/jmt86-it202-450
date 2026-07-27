<?php
// jmt86 - 07/27/2026
// Allows an Admin to import a meal from TheMealDB or create one manually.

require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$row = null;
$active_form = "fetch";

if (isset($_POST["fetch_meal"])) {
    $active_form = "fetch";

    $search = $_POST["search"] ?? "";
    if (!is_string($search)) {
        $search = "";
    }

    $search = trim($search);

    if ($search === "") {
        $errors[] = "Enter a meal name before searching.";
    }

    if (empty($errors)) {
        try {
            $meals = search_meals($search, false, $errors);

            if (empty($errors) && !empty($meals)) {
                // Use the first matching meal returned by TheMealDB.
                $meal = $meals[0];

                $row = [
                    "api_meal_id" => trim((string)($meal["id"] ?? "")),
                    "meal_name" => trim((string)($meal["name"] ?? "")),
                    "category" => trim((string)($meal["category"] ?? "")),
                    "cuisine" => trim((string)($meal["cuisine"] ?? "")),
                    "image_url" => trim((string)($meal["image"] ?? "")),
                    "is_api" => 1,
                ];
            }
        } catch (Throwable $e) {
            error_log("Fetch meal failed: " . $e->getMessage());
            $errors[] = "Unable to fetch that meal right now.";
        }

        if (empty($errors) && (!$row || $row["meal_name"] === "")) {
            $errors[] = "No matching meal was found.";
        }
    }
} elseif (isset($_POST["create_meal"])) {
    $active_form = "create";

    try {
        $meal_name = $_POST["meal_name"] ?? "";
        $category = $_POST["category"] ?? "";
        $cuisine = $_POST["cuisine"] ?? "";
        $image_url = $_POST["image_url"] ?? "";

        if (!is_string($meal_name) || trim($meal_name) === "") {
            throw new InvalidArgumentException("Enter a meal name.");
        }

        if (!is_string($category) || trim($category) === "") {
            throw new InvalidArgumentException("Enter a category.");
        }

        if (!is_string($cuisine) || trim($cuisine) === "") {
            throw new InvalidArgumentException("Enter a cuisine.");
        }

        if (!is_string($image_url)) {
            throw new InvalidArgumentException("Enter a valid image URL.");
        }

        $image_url = trim($image_url);

        if ($image_url !== "" && filter_var($image_url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("Enter a valid image URL.");
        }

        // Manual records do not have a TheMealDB ID.
        $row = [
            "api_meal_id" => null,
            "meal_name" => trim($meal_name),
            "category" => trim($category),
            "cuisine" => trim($cuisine),
            "image_url" => $image_url,
            "is_api" => 0,
        ];
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Create meal input failed: " . $e->getMessage());
        $errors[] = "Unable to process the meal values.";
    }
}

if ($row && empty($errors)) {
    $insert_row = [
        ":api_meal_id" => $row["api_meal_id"],
        ":meal_name" => $row["meal_name"],
        ":category" => $row["category"],
        ":cuisine" => $row["cuisine"],
        ":image_url" => $row["image_url"],
        ":is_api" => $row["is_api"],
    ];

    try {
        $db = getDB();

        $stmt = $db->prepare(
            "INSERT INTO Meals
                (api_meal_id, meal_name, category, cuisine, image_url, is_api)
             VALUES
                (:api_meal_id, :meal_name, :category, :cuisine, :image_url, :is_api)"
        );

        $stmt->execute($insert_row);

        flash("Created meal " . $row["meal_name"], "success");
        header("Location: " . project_url("admin/list_meals.php"));
        exit;
    } catch (PDOException $e) {
        error_log("Create meal failed: " . $e->getMessage());

        $sql_state = $e->getCode();

        if ($sql_state === "23505") {
            flash("This API meal was already imported. No changes were made.", "warning");
        } else {
            flash("Unable to create meal.", "danger");
        }
    }
}

flash_errors($errors);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Meal</title>
    <link rel="stylesheet" href="<?php echo project_url("styles.css"); ?>">
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>Create Meal</h1>

        <div aria-label="Meal creation mode" role="group">
            <button data-form-mode-button="fetch" type="button">
                Import From API
            </button>

            <button data-form-mode-button="create" type="button">
                Create Manually
            </button>
        </div>

        <section
            data-form-mode-panel="fetch"
            <?php if ($active_form !== "fetch") {
                echo "hidden";
            } ?>
        >
            <form method="post">
                <h2>Import From TheMealDB</h2>

                <label for="search">Meal name</label>
                <input
                    id="search"
                    name="search"
                    maxlength="100"
                    required
                    value="<?php echo htmlspecialchars($_POST["search"] ?? ""); ?>"
                >

                <button name="fetch_meal" value="1" type="submit">
                    Import Meal
                </button>
            </form>
        </section>

        <section
            data-form-mode-panel="create"
            <?php if ($active_form !== "create") {
                echo "hidden";
            } ?>
        >
            <form method="post">
                <h2>Create Manually</h2>

                <label for="meal_name">Meal name</label>
                <input
                    id="meal_name"
                    name="meal_name"
                    maxlength="150"
                    required
                    value="<?php echo htmlspecialchars($_POST["meal_name"] ?? ""); ?>"
                >

                <label for="category">Category</label>
                <input
                    id="category"
                    name="category"
                    maxlength="100"
                    required
                    value="<?php echo htmlspecialchars($_POST["category"] ?? ""); ?>"
                >

                <label for="cuisine">Cuisine</label>
                <input
                    id="cuisine"
                    name="cuisine"
                    maxlength="100"
                    required
                    value="<?php echo htmlspecialchars($_POST["cuisine"] ?? ""); ?>"
                >

                <label for="image_url">Image URL</label>
                <input
                    id="image_url"
                    name="image_url"
                    type="url"
                    maxlength="500"
                    value="<?php echo htmlspecialchars($_POST["image_url"] ?? ""); ?>"
                >

                <button name="create_meal" value="1" type="submit">
                    Create Meal
                </button>
            </form>
        </section>
    </main>

    <?php render_flash_messages(); ?>

    <script>
        const mealFormButtons =
            document.querySelectorAll("[data-form-mode-button]");

        const mealFormPanels =
            document.querySelectorAll("[data-form-mode-panel]");

        function showMealForm(mode) {
            mealFormPanels.forEach(function (panel) {
                panel.hidden = panel.dataset.formModePanel !== mode;
            });

            mealFormButtons.forEach(function (button) {
                const isSelected = button.dataset.formModeButton === mode;
                button.setAttribute(
                    "aria-pressed",
                    isSelected ? "true" : "false"
                );
            });
        }

        mealFormButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                showMealForm(button.dataset.formModeButton);
            });
        });

        showMealForm(
            "<?php echo htmlspecialchars($active_form); ?>"
        );
    </script>
</body>
</html>