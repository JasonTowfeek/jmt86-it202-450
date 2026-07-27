<?php
// public_html/project/admin/edit_stock.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    flash("Missing stock id", "danger");
    header("Location: " . project_url("admin/list_stocks.php"));
    exit;
}

if (isset($_POST["save"])) {
    $updated_values = [];

    try {
        // Handle only the valid columns this brief edit form allows.
        foreach (["open", "high", "price"] as $field_name) {
            $value = $_POST[$field_name] ?? "";
            // Reject arrays or non-numeric text before converting the value to a float.
            if (!is_string($value) && !is_numeric($value)) {
                throw new InvalidArgumentException("Enter a valid $field_name value.");
            }

            $updated_values[$field_name] = (float)$value;
            // These stock values must stay positive.
            if ($updated_values[$field_name] <= 0) {
                throw new InvalidArgumentException("Enter a $field_name value greater than zero.");
            }
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Edit stock input failed: " . $e->getMessage());
        $errors[] = "Unable to process the stock values.";
    }

    if (empty($errors)) {
        $data = [
            ":id" => $id,
            ":open" => $updated_values["open"],
            ":high" => $updated_values["high"],
            ":price" => $updated_values["price"],
        ];

        try {
            $db = getDB();
            $stmt = $db->prepare(
                "UPDATE Stocks
                 SET open = :open, high = :high, price = :price
                 WHERE id = :id"
            );
            $stmt->execute($data);
            flash("Stock updated", "success");
            header("Location: " . project_url("admin/list_stocks.php"));
            exit;
        } catch (PDOException $e) {
            error_log("Update stock failed: " . $e->getMessage());
            $errors[] = "Unable to update stock.";
        }
    }
}

flash_errors($errors);

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT open, high, price, symbol FROM Stocks WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Load stock for edit failed: " . $e->getMessage());
    flash("Unable to load that stock.", "danger");
    header("Location: " . project_url("admin/list_stocks.php"));
    exit;
}
if (!$stock) {
    flash("Stock not found", "danger");
    header("Location: " . project_url("admin/list_stocks.php"));
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stock</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Edit <?php echo htmlspecialchars($stock["symbol"]); ?></h1>
        <form method="post">
            <label for="open">Open</label>
            <input id="open" name="open" type="number" min="0.0001" step="0.0001"
                value="<?php echo htmlspecialchars($stock["open"]); ?>" required>

            <label for="high">High</label>
            <input id="high" name="high" type="number" min="0.0001" step="0.0001"
                value="<?php echo htmlspecialchars($stock["high"]); ?>" required>

            <label for="price">Price</label>
            <input id="price" name="price" type="number" min="0.0001" step="0.0001"
                value="<?php echo htmlspecialchars($stock["price"]); ?>" required>

            <button name="save" value="1" type="submit">Save Stock</button>
        </form>
    </main>
    <?php render_flash_messages(); ?>
</body>
</html>
