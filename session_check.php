<?php
/**
 * Tells the front end (js/nav-auth.js) whether someone is currently logged
 * in, so every page can show "Login" or "Hi, <name> / Log Out" in the nav.
 */

require __DIR__ . "/includes/db.php";
start_app_session();

if (!empty($_SESSION["user_id"])) {
    json_response(["loggedIn" => true, "name" => $_SESSION["user_name"]]);
} else {
    json_response(["loggedIn" => false]);
}
