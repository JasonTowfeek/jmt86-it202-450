<?php
// jmt86 - 07/27/2026
// Shows all API-imported and manually created meals for Admin users.

require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$meals = [];

$search = $_GET["search"] ?? "";
$source = $_GET["source"] ?? "";
$sort = $_GET["sort"] ?? "newest";
$limit = $_GET["limit"] ?? "10";

if (!is_string($search)) {
    $search = "";
}

if (!is_string($source)) {
    $source = "";
}

if (!is_string($sort)) {
    $sort = "newest";
}

if (!is_string($limit) || !ctype_digit($limit)) {
    $limit = "10";
}

$search = trim($search);
$limit_number = (int)$limit;

if ($limit_number < 1 || $limit_number > 100) {
    $limit_number = 10;
}

$allowed_sources = ["", "api", "manual"];
if (!in_array($source, $allowed_sources, true)) {
    $source = "";
}

$allowed_sorts = ["newest", "oldest", "name_asc", "name_desc"];
if (!in_array($sort, $allowed_sorts, true)) {
    $sort = "newest";
}

$where = [];
$params = [];

if ($search !== "") {
    $where[] = "
        (
            meal_name ILIKE :search
            OR category ILIKE :search
            OR cuisine ILIKE :search
        )
    ";

    $params[":search"] = "%" . $search . "%";
}

if ($source === "api") {
    $where[] = "is_api = TRUE";
} elseif ($source === "manual") {
    $where[] = "is_api = FALSE";
}

$order_by = "created DESC";

if ($sort === "oldest") {
    $order_by = "created ASC";
} elseif ($sort === "name_asc") {
    $order_by = "meal_name ASC";
} elseif ($sort === "name_desc") {
    $order_by = "meal_name DESC";
}

$sql = "
    SELECT
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
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY " . $order_by;
$sql .= " LIMIT " . $limit_number;

try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("List meals failed: " . $e->getMessage());
    $errors[] = "Unable to load meals right now.";
}

flash_errors($errors);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meals</title>
    <link rel="stylesheet" href="<?php echo project_url("styles.css"); ?>">
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>Manage Meals</h1>

        <p>
            <a href="<?php echo project_url("admin/create_meal.php"); ?>">
                Create or Import Meal
            </a>
        </p>

        <form method="get">
            <label for="search">Search</label>
            <input
                id="search"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Meal, category, or cuisine"
            >

            <label for="source">Source</label>
            <select id="source" name="source">
                <option value="" <?php if ($source === "") echo "selected"; ?>>
                    All
                </option>

                <option value="api" <?php if ($source === "api") echo "selected"; ?>>
                    API
                </option>

                <option value="manual" <?php if ($source === "manual") echo "selected"; ?>>
                    Manual
                </option>
            </select>

            <label for="sort">Sort</label>
            <select id="sort" name="sort">
                <option value="newest" <?php if ($sort === "newest") echo "selected"; ?>>
                    Newest
                </option>

                <option value="oldest" <?php if ($sort === "oldest") echo "selected"; ?>>
                    Oldest
                </option>

                <option value="name_asc" <?php if ($sort === "name_asc") echo "selected"; ?>>
                    Name A-Z
                </option>

                <option value="name_desc" <?php if ($sort === "name_desc") echo "selected"; ?>>
                    Name Z-A
                </option>
            </select>

            <label for="limit">Limit</label>
            <input
                id="limit"
                name="limit"
                type="number"
                min="1"
                max="100"
                value="<?php echo $limit_number; ?>"
            >

            <button type="submit">Apply</button>
        </form>

        <?php if (empty($meals)): ?>
            <p>No meals matched your filters.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Cuisine</th>
                        <th>Source</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($meals as $meal): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($meal["meal_name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($meal["category"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($meal["cuisine"]); ?>
                            </td>

                            <td>
                                <?php echo $meal["is_api"] ? "API" : "Manual"; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($meal["created"]); ?>
                            </td>

                            <td>
                                <a
                                    href="<?php
                                        echo project_url(
                                            "meal_details.php?id=" .
                                            urlencode((string)$meal["id"])
                                        );
                                    ?>"
                                >
                                    View
                                </a>

                                <a
                                    href="<?php
                                        echo project_url(
                                            "admin/edit_meal.php?id=" .
                                            urlencode((string)$meal["id"])
                                        );
                                    ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="<?php echo project_url("admin/delete_meal.php"); ?>"
                                    style="display:inline"
                                    onsubmit="return confirm('Delete this meal?');"
                                >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo (int)$meal["id"]; ?>"
                                    >

                                    <button type="submit">
                                        Delete
                                    </button>
                                </form>
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