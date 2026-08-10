<?php
require_once(__DIR__ . "/../../../../lib/db.php");
?>

<?php
$db = getDB();

// UCID: mp2446
// Date: 08/10/2026
// Plan:
// 1. Validate the submitted todo id before using it.
// 2. Mark only that incomplete todo as complete.
// 3. Set the completed date to today.
// 4. Fetch only incomplete todos.
// 5. Calculate days_offset from today's date.
// 6. Order pending todos by the soonest due date.

// process complete action
if (isset($_POST["id"])) {
    $id = filter_var($_POST["id"], FILTER_VALIDATE_INT);

    if ($id !== false && $id > 0) {

        $query = "
            UPDATE M4_Todos
            SET
                is_complete = 1,
                completed = CURRENT_DATE
            WHERE id = :id
              AND is_complete = 0
        ";

        $params = [
            ":id" => $id
        ];

        try {
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);

            if ($r) {
                echo "Marked task $id as completed";
            } else {
                echo "Failed to mark task $id as completed";
            }
        } catch (PDOException $e) {
            echo "Error updating task $id; check the logs (terminal)";
            error_log("Update Error: " . var_export($e, true));
        }
    } else {
        echo "Invalid todo id";
    }
}


// Fetch pending todos
$query = "
    SELECT
        id,
        task,
        due,
        DATEDIFF(due, CURRENT_DATE) AS days_offset,
        assigned
    FROM M4_Todos
    WHERE is_complete = 0
    ORDER BY due ASC
";

$results = [];

try {
    $stmt = $db->prepare($query);
    $r = $stmt->execute();

    if ($r) {
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    echo "Error fetching pending todos; check the logs (terminal)";
    error_log("Select Error: " . var_export($e, true));
}
?>

<html>

<body>

    <?php require_once(__DIR__ . "/../nav.php"); ?>

    <section>

        <h2>Pending ToDos</h2>

        <table border="1">

            <thead>
                <tr>
                    <th>Task</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($results as $row): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["task"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["due"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td>

                            <?php if ($row["days_offset"] > 0): ?>

                                <?php
                                echo $row["days_offset"] .
                                    " day(s) remaining";
                                ?>

                            <?php elseif ($row["days_offset"] == 0): ?>

                                Due today

                            <?php else: ?>

                                <?php
                                echo abs($row["days_offset"]) .
                                    " day(s) overdue";
                                ?>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["assigned"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </td>

                        <td>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $row["id"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>" />

                                <input
                                    type="submit"
                                    value="Complete" />

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (count($results) === 0): ?>

                    <tr>
                        <td colspan="100%">
                            No results
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </section>

</body>

</html>