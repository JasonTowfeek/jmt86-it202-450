<?php
// public_html/project/testApi-companies.php
require_once(__DIR__ . "/../../lib/app.php");

$search = "micro";
if (isset($_POST["search"])) {
    $submitted_search = $_POST["search"];
    if (is_string($submitted_search)) {
        $search = trim($submitted_search);
    } else {
        $search = "";
    }
}
$decoded = null;
$errors = [];

if (isset($_POST["source"])) {
    $source = $_POST["source"];

    if ($source !== "live" && $source !== "sample") {
        $errors[] = "Choose a valid API source.";
    } elseif ($source === "live" && $search === "") {
        $errors[] = "Enter search text before sending the request.";
    }

    if (empty($errors)) {
        if ($source === "sample") {
            $result = api_sample_response("company-search.json");
        } else {
            $result = api_get(
                "https://alpha-vantage.p.rapidapi.com/query",
                ["function" => "SYMBOL_SEARCH", "keywords" => $search],
                ["key_name" => "STOCK_API_KEY", "host_name" => "STOCK_API_HOST"]
            );
        }
        $json_key = "bestMatches";
        $decoded = decode_api_response($result, $json_key, $errors);
        $decoded = $decoded[$json_key];
    }
}

flash_errors($errors);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Multiple Entities</title>
</head>

<body>
    <?php render_nav(); ?>
    <main>
        <h1>Test Multiple Entities</h1>
        <form method="post">
            <label for="search">Search text</label>
            <input id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" required>
            <button name="source" value="live" type="submit">Search Live API</button>
            <button name="source" value="sample" type="submit">Use Cached Sample</button>
        </form>

        <pre><?php var_dump($decoded); ?></pre>
    </main>
    <?php render_flash_messages(); ?>
</body>

</html>