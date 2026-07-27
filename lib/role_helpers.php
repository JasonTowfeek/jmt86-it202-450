<?php
/**
 * Gets active role names for a user.
 *
 * @param int $user_id User id from the users table.
 * @return array Active role names such as ["Admin"].
 */
function get_user_roles(int $user_id): array
{
    try {
        $db = getDB();

        $stmt = $db->prepare(
            "SELECT roles.name
             FROM roles
             JOIN userroles ON roles.id = userroles.role_id
             WHERE userroles.user_id = :user_id
               AND roles.is_active = TRUE
               AND userroles.is_active = TRUE
             ORDER BY roles.name"
        );

        $stmt->execute([
            ":user_id" => $user_id
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, "name");
    } catch (PDOException $e) {
        error_log("Role lookup failed: " . $e->getMessage());
        flash(
            "Permissions failed to load. Please try logging in again.",
            "warning"
        );
        return [];
    }
}

/**
 * Checks whether the logged-in user has a role.
 */
function has_role(string $role): bool
{
    if (!is_logged_in()) {
        return false;
    }

    $roles = $_SESSION["user"]["roles"] ?? [];

    return in_array($role, $roles, true);
}

/**
 * Redirects unless the logged-in user has the required role.
 */
function require_role(string $role): void
{
    if (!is_logged_in()) {
        flash("Please log in first.", "warning");
        header("Location: " . project_url("login.php"));
        exit;
    }

    if (!has_role($role)) {
        flash(
            "You do not have permission to view that page.",
            "danger"
        );
        header("Location: " . project_url("dashboard.php"));
        exit;
    }
}