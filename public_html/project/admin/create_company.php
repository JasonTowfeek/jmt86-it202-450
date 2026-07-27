<?php
// public_html/project/admin/create_company.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$rows = [];
$active_form = "fetch";

if (isset($_POST["fetch_companies"])) {
    $active_form = "fetch";
    $search = "";
    if (isset($_POST["search"])) {
        $submitted_search = $_POST["search"];
        if (is_string($submitted_search)) {
            $search = trim($submitted_search);
        }
    }

    if ($search === "") {
        $errors[] = "Enter search text before calling the API.";
    }

    if (empty($errors)) {
        try {
            $rows = search_companies($search, $errors);
        } catch (Throwable $e) {
            error_log("Fetch companies failed: " . $e->getMessage());
            $errors[] = "Unable to search for companies right now.";
        }

        if (empty($errors) && !$rows) {
            $errors[] = "No companies matched that search.";
        }
    }
} elseif (isset($_POST["create_company"])) {
    $active_form = "create";
    try {
        $symbol = $_POST["symbol"] ?? "";
        $name = $_POST["name"] ?? "";
        $type = $_POST["type"] ?? "";
        $region = $_POST["region"] ?? "";
        $currency = $_POST["currency"] ?? "";
        if (
            !is_string($symbol) || !is_string($name) || !is_string($type)
            || !is_string($region) || !is_string($currency)
        ) {
            throw new InvalidArgumentException("Enter both a company symbol and name.");
        }
        if (trim($symbol) === "" || trim($name) === "") {
            throw new InvalidArgumentException("Enter both a company symbol and name.");
        }

        // Wrap one manual row so API and manual paths use the same insert loop.
        $rows[] = [
            "symbol" => trim($symbol),
            "name" => trim($name),
            "type" => trim($type),
            "region" => trim($region),
            "currency" => trim($currency),
            "is_api" => 0,
        ];
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Create company input failed: " . $e->getMessage());
        $errors[] = "Unable to process the company values.";
    }
}

if ($rows && empty($errors)) {
    $saved = 0;
    $duplicates = 0;

    try {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO Companies (symbol, name, type, region, currency, is_api)
             VALUES (:symbol, :name, :type, :region, :currency, :is_api)"
        );

        foreach ($rows as $row) {
            try {
                $stmt->execute($row);
                $saved++;
            } catch (PDOException $e) {
                if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                    $duplicates++;
                    continue;
                }
                throw $e;
            }
        }

        if ($saved > 0) {
            flash("Saved $saved company record(s).", "success");
        }
        if ($duplicates > 0) {
            flash("Skipped $duplicates duplicate company record(s).", "warning");
        }
        header("Location: " . project_url("admin/list_companies.php"));
        exit;
    } catch (PDOException $e) {
        error_log("Create companies failed: " . $e->getMessage());
        flash("Unable to save the company records.", "danger");
    }
}

flash_errors($errors);
?>
<!-- TODO add the create company form snippet here. -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Company</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Create Company</h1>

        <div aria-label="Company creation mode" role="group">
            <button data-form-mode-button="fetch" type="button">Search The API</button>
            <button data-form-mode-button="create" type="button">Create Manually</button>
        </div>

        <section data-form-mode-panel="fetch"<?php if ($active_form !== "fetch") { echo " hidden"; } ?>>
            <form method="post">
                <h2>Search The API</h2>
                <label for="search">Search text</label>
                <input id="search" name="search" required>
                <button name="fetch_companies" value="1" type="submit">Search Companies</button>
            </form>
        </section>

        <section data-form-mode-panel="create"<?php if ($active_form !== "create") { echo " hidden"; } ?>>
            <form method="post">
                <h2>Create Manually</h2>
                <label for="symbol">Symbol</label>
                <input id="symbol" name="symbol" required pattern="[A-Za-z0-9.\-]{1,10}">
                <label for="name">Company name</label>
                <input id="name" name="name" required>

                <label for="type">Type</label>
                <input id="type" name="type" maxlength="40">

                <label for="region">Region</label>
                <input id="region" name="region" maxlength="80">

                <label for="currency">Currency</label>
                <input id="currency" name="currency" maxlength="10">

                <button name="create_company" value="1" type="submit">Create Company</button>
            </form>
        </section>
    </main>
    <?php render_flash_messages(); ?>
    <script>
        const companyFormButtons = document.querySelectorAll("[data-form-mode-button]");
        const companyFormPanels = document.querySelectorAll("[data-form-mode-panel]");

        function showCompanyForm(mode) {
            companyFormPanels.forEach(function (panel) {
                panel.hidden = panel.dataset.formModePanel !== mode;
            });
            companyFormButtons.forEach(function (button) {
                button.setAttribute("aria-pressed", button.dataset.formModeButton === mode ? "true" : "false");
            });
        }

        companyFormButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                showCompanyForm(button.dataset.formModeButton);
            });
        });

        showCompanyForm("<?php echo $active_form; ?>");
    </script>
</body>
</html>
