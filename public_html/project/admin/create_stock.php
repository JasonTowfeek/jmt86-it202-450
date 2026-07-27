<?php
// public_html/project/admin/create_stock.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$row = null;
$active_form = "fetch";

if (isset($_POST["fetch_stock"])) {
    $active_form = "fetch";
    $symbol = "";
    if (isset($_POST["symbol"])) {
        $submitted_symbol = $_POST["symbol"];
        if (is_string($submitted_symbol)) {
            $symbol = trim($submitted_symbol);
        }
    }

    if ($symbol === "") {
        $errors[] = "Enter a stock symbol before fetching.";
    }

    if (empty($errors)) {
        try {
            $row = fetch_quote($symbol, $errors);
        } catch (Throwable $e) {
            error_log("Fetch stock failed: " . $e->getMessage());
            $errors[] = "Unable to fetch that stock right now.";
        }

        if (empty($errors) && (!$row || empty($row["symbol"]))) {
            $errors[] = "No stock quote was found.";
        }
    }
} elseif (isset($_POST["create_stock"])) {
    $active_form = "create";
    try {
        $symbol = $_POST["symbol"] ?? "";
        if (!is_string($symbol) || trim($symbol) === "") {
            throw new InvalidArgumentException("Enter a stock symbol.");
        }

        foreach (["open", "high", "low", "price"] as $field_name) {
            $value = $_POST[$field_name] ?? "";
            if (!is_string($value) && !is_numeric($value)) {
                $label = str_replace("_", " ", $field_name);
                throw new InvalidArgumentException("Enter a valid $label value.");
            }
        }

        $latest_trading_day = $_POST["latest_trading_day"] ?? "";
        if (
            !is_string($latest_trading_day)
            || !preg_match("/^\\d{4}-\\d{2}-\\d{2}$/", $latest_trading_day)
        ) {
            throw new InvalidArgumentException("Enter a valid latest trading day.");
        }

        if ($_POST["price"] <= 0) {
            throw new InvalidArgumentException("Enter a price greater than zero.");
        }
        // Build an allowlisted row instead of passing raw $_POST to SQL.
        $row = [
            "symbol" => trim($symbol),
            "open" => (float)$_POST["open"],
            "high" => (float)$_POST["high"],
            "low" => (float)$_POST["low"],
            "price" => (float)$_POST["price"],
            "latest_trading_day" => $latest_trading_day,
            "is_api" => 0,
        ];

        
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Create stock input failed: " . $e->getMessage());
        $errors[] = "Unable to process the stock values.";
    }
}

if ($row && empty($errors)) {
    // The API row has more fields than this first INSERT uses.
    // Keep only the placeholders named in the SQL below.
    $insert_row = [
        ":symbol" => $row["symbol"],
        ":open" => $row["open"],
        ":high" => $row["high"],
        ":low" => $row["low"],
        ":price" => $row["price"],
        ":latest_trading_day" => $row["latest_trading_day"],
        ":is_api" => $row["is_api"],
    ];

    try {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO Stocks (symbol, open, high, low, price, latest_trading_day, is_api)
             VALUES (:symbol, :open, :high, :low, :price, :latest_trading_day, :is_api)"
        );
        $stmt->execute($insert_row);
        flash("Created stock " . $row["symbol"], "success");
        header("Location: " . project_url("admin/list_stocks.php"));
        exit;
    } catch (PDOException $e) {
        error_log("Create stock failed: " . $e->getMessage());
        $error_code = 0;
        if (isset($e->errorInfo[1])) {
            $error_code = (int)$e->errorInfo[1];
        }

        if ($error_code === 1062) {
            flash("A stock with this symbol already exists. No changes were made.", "warning");
        } else {
            flash("Unable to create stock.", "danger");
        }
    }
}

flash_errors($errors);
?>
<!-- TODO add the create stock form snippet here. -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Stock</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Create Stock</h1>

        <div aria-label="Stock creation mode" role="group">
            <button data-form-mode-button="fetch" type="button">Fetch From API</button>
            <button data-form-mode-button="create" type="button">Create Manually</button>
        </div>

        <section data-form-mode-panel="fetch"<?php if ($active_form !== "fetch") { echo " hidden"; } ?>>
            <form method="post">
                <h2>Fetch From API</h2>
                <label for="symbol_fetch">Symbol</label>
                <input id="symbol_fetch" name="symbol" required pattern="[A-Za-z0-9.\-]{1,10}">
                <button name="fetch_stock" value="1" type="submit">Fetch Stock</button>
            </form>
        </section>

        <section data-form-mode-panel="create"<?php if ($active_form !== "create") { echo " hidden"; } ?>>
            <form method="post">
                <h2>Create Manually</h2>
                <label for="symbol_create">Symbol</label>
                <input id="symbol_create" name="symbol" required pattern="[A-Za-z0-9.\-]{1,10}">

                <label for="open">Open</label>
                <input id="open" name="open" type="number" min="0" step="0.0001" required>

                <label for="high">High</label>
                <input id="high" name="high" type="number" min="0" step="0.0001" required>

                <label for="low">Low</label>
                <input id="low" name="low" type="number" min="0" step="0.0001" required>

                <label for="price">Price</label>
                <input id="price" name="price" type="number" min="0.0001" step="0.0001" required>

                <label for="latest_trading_day">Latest trading day</label>
                <input id="latest_trading_day" name="latest_trading_day" type="date" required>

                <button name="create_stock" value="1" type="submit">Create Stock</button>
            </form>
        </section>
    </main>
    <?php render_flash_messages(); ?>
    <script>
        const stockFormButtons = document.querySelectorAll("[data-form-mode-button]");
        const stockFormPanels = document.querySelectorAll("[data-form-mode-panel]");

        function showStockForm(mode) {
            stockFormPanels.forEach(function (panel) {
                panel.hidden = panel.dataset.formModePanel !== mode;
            });
            stockFormButtons.forEach(function (button) {
                button.setAttribute("aria-pressed", button.dataset.formModeButton === mode ? "true" : "false");
            });
        }

        stockFormButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                showStockForm(button.dataset.formModeButton);
            });
        });

        showStockForm("<?php echo $active_form; ?>");
    </script>
</body>
</html>
