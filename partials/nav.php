<?php
// File: partials/nav.php

$isLoggedIn = is_logged_in();
?>

<link rel="stylesheet" href="/project/styles.css">

<nav>
    <ul>
        <li>
            <a href="<?php echo project_url("index.php"); ?>">
                Home
            </a>
        </li>

        <li>
            <a href="<?php echo project_url("meals.php"); ?>">
                Meals
            </a>
        </li>

        <?php if ($isLoggedIn): ?>

            <li>
                <a href="<?php echo project_url("dashboard.php"); ?>">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="<?php echo project_url("profile.php"); ?>">
                    Profile
                </a>
            </li>

            <li>
                <a href="<?php echo project_url("my_meals.php"); ?>">
                    My Saved Meals
                </a>
            </li>

            <?php if (has_role("Admin")): ?>

                <li>
                    <a href="<?php echo project_url("admin/create_role.php"); ?>">
                        Create Role
                    </a>
                </li>

                <li>
                    <a href="<?php echo project_url("admin/list_roles.php"); ?>">
                        List Roles
                    </a>
                </li>

                <li>
                    <a href="<?php echo project_url("admin/assign_roles.php"); ?>">
                        Assign Roles
                    </a>
                </li>

                <li>
                    <a href="<?php echo project_url("admin/list_meals.php"); ?>">
                        Manage Meals
                    </a>
                </li>

                <li>
                    <a href="<?php echo project_url("admin/list_user_meals.php"); ?>">
                        User Meal Associations
                    </a>
                </li>

                <li>
                    <a href="<?php echo project_url("admin/assign_user_meal.php"); ?>">
                        Assign Meal to User
                    </a>
                </li>

                <li>
                    <a href="<?php echo project_url("admin/list_unassociated_meals.php"); ?>">
                        Unassociated Meals
                    </a>
                </li>

            <?php endif; ?>

            <li>
                <a href="<?php echo project_url("logout.php"); ?>">
                    Logout
                </a>
            </li>

        <?php else: ?>

            <li>
                <a href="<?php echo project_url("login.php"); ?>">
                    Login
                </a>
            </li>

            <li>
                <a href="<?php echo project_url("register.php"); ?>">
                    Register
                </a>
            </li>

        <?php endif; ?>
    </ul>
</nav>

<script src="<?php echo project_url("helpers.js"); ?>"></script>

<!-- Temporary milestone evidence utility.
     Remove after all milestone submissions are complete. -->
<script src="https://matttoegel.github.io/IT202-Utils/submission-utils.js"></script>