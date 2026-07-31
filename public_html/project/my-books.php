<?php
// UCID: mp2446
// Date: 07/30/2026
// Shows all books assigned to the currently logged-in user.

require_once(__DIR__ . "/../../lib/app.php");

require_login();

$user = current_user();
$userId = (int) $user["id"];
$books = [];

try {
    $db = getDB();

    $stmt = $db->prepare(
        "SELECT
            b.id,
            b.title,
            b.author,
            b.publish_year,
            b.cover_id,
            b.source,
            ub.created AS assigned_created
         FROM UserBooks ub
         INNER JOIN Books b
            ON b.id = ub.book_id
         WHERE ub.user_id = :user_id
         ORDER BY ub.created DESC, b.title ASC"
    );

    $stmt->bindValue(
        ":user_id",
        $userId,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log(
        "My books query failed for UCID mp2446: " .
        $e->getMessage()
    );

    flash_set(
        "Your book list could not be loaded right now.",
        "error"
    );
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Books</title>
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <h1>My Books</h1>

        <?php render_flash(); ?>

        <?php if (empty($books)): ?>
            <p>
                You have not added any books to your list yet.
            </p>

            <p>
                <a href="books.php">
                    Browse all books
                </a>
            </p>
        <?php else: ?>
            <p>
                Total books:
                <strong><?php echo count($books); ?></strong>
            </p>

            <?php foreach ($books as $book): ?>
                <article>
                    <?php if (!empty($book["cover_id"])): ?>
                        <img
                            src="https://covers.openlibrary.org/b/id/<?php
                                echo (int) $book["cover_id"];
                            ?>-S.jpg"
                            alt="Cover of <?php
                                echo htmlspecialchars($book["title"]);
                            ?>"
                        >
                    <?php endif; ?>

                    <h2>
                        <a href="book-detail.php?id=<?php
                            echo (int) $book["id"];
                        ?>">
                            <?php
                            echo htmlspecialchars($book["title"]);
                            ?>
                        </a>
                    </h2>

                    <p>
                        <strong>Author:</strong>
                        <?php
                        echo htmlspecialchars($book["author"]);
                        ?>
                    </p>

                    <p>
                        <strong>Publication Year:</strong>
                        <?php
                        echo $book["publish_year"] !== null
                            ? htmlspecialchars(
                                (string) $book["publish_year"]
                            )
                            : "Not available";
                        ?>
                    </p>

                    <p>
                        <strong>Source:</strong>
                        <?php
                        echo htmlspecialchars($book["source"]);
                        ?>
                    </p>

                    <p>
                        <strong>Added to My Books:</strong>
                        <?php
                        echo htmlspecialchars(
                            $book["assigned_created"]
                        );
                        ?>
                    </p>

                    <form
                        method="post"
                        action="user-book-action.php"
                    >
                        <input
                            type="hidden"
                            name="book_id"
                            value="<?php
                                echo (int) $book["id"];
                            ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="remove"
                        >

                        <button type="submit">
                            Remove from My Books
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <p>
            <a href="books.php">
                Browse all books
            </a>
        </p>
    </main>
</body>

</html>