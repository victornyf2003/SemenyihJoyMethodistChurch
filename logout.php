<?php
/**
 * Destroys the current session. Called via fetch() from js/nav-auth.js
 * when the visitor clicks "Log Out".
 */

require __DIR__ . "/includes/db.php";
start_app_session();

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

json_response(["success" => true]);
