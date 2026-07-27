<?php
// public_html/project/testApi-stock.php
require_once(__DIR__ . "/../../lib/app.php");

$symbol = "IBM";
if (isset($_POST["symbol"])) {
    $submitted_symbol = $_POST["symbol"];
    if (is_string($submitted_symbol)) {
        $symbol = trim($submitted_symbol);
    } else {
        $symbol = "";
    }
}
$decoded = null;
$errors = [];

if (isset($_POST["source"])) {
    $source = $_POST["source"];

    if ($source !== "live" && $source !== "sample") {
        $errors[] = "Choose a valid API source.";
    } elseif ($source === "live" && $symbol === "") {
        $errors[] = "Enter a stock symbol before sending the request.";
    }

    if (empty($errors)) {
        if ($source === "sample") {
            $result = api_sample_response("stock-quote.json");
        } else {
            $result = api_get(
                "https://alpha-vantage.p.rapidapi.com/query",
                ["function" => "GLOBAL_QUOTE", "symbol" => $symbol],
                ["key_name" => "STOCK_API_KEY", "host_name" => "STOCK_API_HOST"]
            );
        }
        $json_key = "Global Quote";
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
    <title>Test Stock API</title>
</head>

<body>
    <?php render_nav(); ?>
    <main>
        <h1>Test Stock API</h1>
        <form method="post">
            <label for="symbol">Stock symbol</label>
            <input id="symbol" name="symbol" value="<?php echo htmlspecialchars($symbol); ?>" required>
            <button name="source" value="live" type="submit">Fetch Live Quote</button>
            <button name="source" value="sample" type="submit">Use Cached Sample</button>
        </form>

        <pre><?php var_dump($decoded); ?></pre>
    </main>
    <?php render_flash_messages(); ?>
</body>

</html>