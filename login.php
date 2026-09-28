<?php
/**
 * Handles login. Called via fetch() from login.html.
 * Expects JSON: { email, password }
 * Responds JSON: { success: bool, message?: string, name?: string }
 */

require __DIR__ . "/includes/db.php";
start_app_session();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["success" => false, "message" => "Invalid request method."], 405);
}

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$email    = trim($input["email"] ?? "");
$password = (string)($input["password"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
    json_response(["success" => false, "message" => "Please enter a valid email and password."], 422);
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT registration_id, name, password FROM registration WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user["password"])) {
        json_response(["success" => false, "message" => "Incorrect email or password."], 401);
    }

    $_SESSION["user_id"] = (int)$user["registration_id"];
    $_SESSION["user_name"] = $user["name"];
    $_SESSION["user_email"] = $email;

    json_response(["success" => true, "name" => $user["name"]]);

} catch (Throwable $e) {
    json_response(["success" => false, "message" => db_error_message($e)], 500);
}
