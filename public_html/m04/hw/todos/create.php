<?php
require_once(__DIR__ . "/../../../../lib/db.php");
?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"];
    $assigned = $_GET["assigned"];

    $is_valid = true;

    // UCID: mp2446
    // Date: 08/10/2026
    // Plan:
    // 1. Validate task as required text with a maximum length of 128.
    // 2. Validate due as a valid MySQL date.
    // 3. Validate assigned as text with a maximum length of 60.
    // 4. Fall back to "self" when assigned is empty or invalid.
    // 5. Insert valid data using PDO named placeholders.

    // Start validations
    // can edit here

    $task = trim($task);
    $due = trim($due);
    $assigned = trim($assigned);

    // Validate task
    if ($task === "") {
        echo "<p>Task is required.</p>";
        $is_valid = false;
    } elseif (strlen($task) > 128) {
        echo "<p>Task must be 128 characters or less.</p>";
        $is_valid = false;
    }

    // Validate due date
    $date = DateTime::createFromFormat("Y-m-d", $due);

    if (
        $due === "" ||
        !$date ||
        $date->format("Y-m-d") !== $due
    ) {
        echo "<p>Please enter a valid due date.</p>";
        $is_valid = false;
    }

    // Assigned falls back to self when empty
    if ($assigned === "") {
        $assigned = "self";
    }

    // assigned is VARCHAR(60)
    if (strlen($assigned) > 60) {
        echo "<p>Assigned value must be 60 characters or less. Using self instead.</p>";
        $assigned = "self";
    }

    // End validations

    if ($is_valid) {

        $query = "
            INSERT INTO M4_Todos (
                task,
                due,
                assigned
            )
            VALUES (
                :task,
                :due,
                :assigned
            )
        ";

        $params = [
            ":task" => $task,
            ":due" => $due,
            ":assigned" => $assigned
        ];

        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);

            if ($r) {
                echo "Inserted new Todo with id " . $db->lastInsertId();
            } else {
                echo "Failed to insert";
            }

        } catch (PDOException $e) {

            if ($e->getCode() === "23000") {
                echo "<p>A todo with the same task and due date already exists.</p>";
            } else {
                echo "<p>There was an error inserting the record; check the logs.</p>";
            }

            error_log(
                "Insert Error: " .
                var_export($e, true)
            );
        }

    } else {
        error_log("Creation input wasn't valid");
    }
}
?>

<html>

<body>

    <?php require_once(__DIR__ . "/../nav.php"); ?>

    <section>

        <h2>Create ToDo</h2>

        <form method="get">

            <!--
            UCID: mp2446
            Date: 08/10/2026

            Plan:
            Create inputs named task, due, and assigned.
            Use text for task, date for due,
            and default assigned to self.
            -->

            <div>
                <label for="task">
                    Task
                </label>

                <input
                    type="text"
                    id="task"
                    name="task"
                    maxlength="128"
                    required
                />
            </div>

            <div>
                <label for="due">
                    Due Date
                </label>

                <input
                    type="date"
                    id="due"
                    name="due"
                    required
                />
            </div>

            <div>
                <label for="assigned">
                    Assigned
                </label>

                <input
                    type="text"
                    id="assigned"
                    name="assigned"
                    value="self"
                    maxlength="60"
                />
            </div>

            <div>
                <input type="submit" />
            </div>

        </form>

    </section>

</body>

</html>