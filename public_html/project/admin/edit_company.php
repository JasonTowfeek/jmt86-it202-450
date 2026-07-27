<?php
// public_html/project/admin/edit_company.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    flash("Missing company id", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
    exit;
}

if (isset($_POST["save"])) {
    $updated_values = [];

    try {
        // Handle only the valid columns this brief edit form allows.
        foreach (["name", "type", "region"] as $field_name) {
            $value = $_POST[$field_name] ?? "";
            // Reject unexpected array input before trimming and saving text.
            if (!is_string($value)) {
                throw new InvalidArgumentException("Enter a valid $field_name value.");
            }

            $updated_values[$field_name] = trim($value);
        }

        if ($updated_values["name"] === "") {
            throw new InvalidArgumentException("Enter a company name.");
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Edit company input failed: " . $e->getMessage());
        $errors[] = "Unable to process the company values.";
    }

    if (empty($errors)) {
        $data = [
            ":id" => $id,
            ":name" => $updated_values["name"],
            ":type" => $updated_values["type"],
            ":region" => $updated_values["region"],
        ];

        try {
            $db = getDB();
            $stmt = $db->prepare(
                "UPDATE Companies
                 SET name = :name, type = :type, region = :region
                 WHERE id = :id"
            );
            $stmt->execute($data);
            flash("Company updated", "success");
            header("Location: " . project_url("admin/list_companies.php"));
            exit;
        } catch (PDOException $e) {
            error_log("Update company failed: " . $e->getMessage());
            $errors[] = "Unable to update company.";
        }
    }
}

flash_errors($errors);

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT `name`, `region`, `type`, `symbol` FROM Companies WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Load company for edit failed: " . $e->getMessage());
    flash("Unable to load that company.", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
    exit;
}
if (!$company) {
    flash("Company not found", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Edit <?php echo htmlspecialchars($company["symbol"]); ?></h1>
        <form method="post">
            <label for="name">Name</label>
            <input id="name" name="name" value="<?php echo htmlspecialchars($company["name"]); ?>" required>

            <label for="type">Type</label>
            <input id="type" name="type" value="<?php echo htmlspecialchars($company["type"]); ?>">

            <label for="region">Region</label>
            <input id="region" name="region" value="<?php echo htmlspecialchars($company["region"]); ?>">

            <button name="save" value="1" type="submit">Save Company</button>
        </form>
    </main>
    <?php render_flash_messages(); ?>
</body>
</html>
