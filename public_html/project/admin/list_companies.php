<?php
// public_html/project/admin/list_companies.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$companies = [];

try {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT id, symbol, name, type, region, is_api
         FROM Companies
         ORDER BY modified DESC
         LIMIT 10"
    );
    $stmt->execute();
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("List companies failed: " . $e->getMessage());
    flash("Unable to load companies.", "danger");
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Companies</h1>
        <table>
            <thead>
                <tr><th>Symbol</th><th>Name</th><th>Type</th><th>Region</th><th>Source</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                    <?php
                    $source_label = "Manual";
                    if ($company["is_api"]) {
                        $source_label = "API";
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($company["symbol"]); ?></td>
                        <td><?php echo htmlspecialchars($company["name"]); ?></td>
                        <td><?php echo htmlspecialchars($company["type"]); ?></td>
                        <td><?php echo htmlspecialchars($company["region"]); ?></td>
                        <td><?php echo $source_label; ?></td>
                        <td><a href="edit_company.php?id=<?php echo urlencode($company["id"]); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <?php render_flash_messages(); ?>
</body>
</html>
