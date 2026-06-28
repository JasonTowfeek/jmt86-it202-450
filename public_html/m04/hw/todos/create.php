<?php
require_once(__DIR__ . "/../../../../lib/db.php"); ?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"]; //hint: must be a valid MySQL date format
    $assigned = $_GET["assigned"]; // Must be "self" or a valid format (not empty or equivalent)

    $is_valid = true;
    // TODO Validate the incoming data for correct format based on the SQL table definition.
    // When not valid, provide a user-friendly message of what specifically was wrong and set $is_valid to false.
    // Assigned should check for "self" if a valid format/value isn't provided.
    // Start validations
    // UCID: jmt86
    // Date: 06/27/2026
    // Plan: Validate task, due date, and assigned before inserting the todo.

    if (empty(trim($task))) {
        echo "Task cannot be empty<br>";
        $is_valid = false;
    }

    if (empty($due)) {
        echo "Due date cannot be empty<br>";
        $is_valid = false;
    }

    if (!empty($due)) {
        $date_parts = explode("-", $due);
        if (count($date_parts) !== 3 || !checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
            echo "Due date must be a valid date<br>";
            $is_valid = false;
        }
    }

    if (empty(trim($assigned))) {
        echo "Assigned cannot be empty<br>";
        $is_valid = false;
    }

    // End validations

    
    if ($is_valid) {
        /*
        Design a query to insert the incoming data to the proper columns.
        Ensure valid and proper PDO named placeholders are used.
        https://phpdelusions.net/pdo
        */
    $query = "INSERT INTO M4_Todos (task, due, assigned) VALUES (:task, :due, :assigned)";
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
            // extra credit
            // check if the exception was related to a unique constraint
            // provide an appropriate user-friendly message for this scenario
            // Otherwise show the default message below
            echo "There was an error inserting the record; check the logs (terminal)";
            error_log("Insert Error: " . var_export($e, true)); // shows in the terminal
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
        <h2>Create ToDo </h2>
        <form>
    <div>
        <label for="task">Task</label>
        <input type="text" id="task" name="task">
    </div>

    <div>
        <label for="due">Due Date</label>
        <input type="date" id="due" name="due">
    </div>

    <div>
        <label for="assigned">Assigned</label>
        <input type="text" id="assigned" name="assigned" value="self">
    </div>

    <div>
        <input type="submit" value="Create Todo">
    </div>
</form>
    </section>
</body>

</html>