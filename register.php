<?php
/**
 * Handles new-member registration. Called via fetch() from registration.html.
 * Expects JSON: { name, age, email, phone, member, password }
 * Responds JSON: { success: bool, message?: string, name?: string }
 */

require __DIR__ . "/includes/db.php";
start_app_session();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["success" => false, "message" => "Invalid request method."], 405);
}

$input = json_decode(file_get_contents("php://input"), true) ?? [];

$name     = trim($input["name"] ?? "");
$age      = trim($input["age"] ?? "");
$email    = trim($input["email"] ?? "");
$phone    = trim($input["phone"] ?? "");
$member   = trim($input["member"] ?? "Not specified");
$password = (string)($input["password"] ?? "");

// --- Server-side validation (never trust the client) ---
$errors = [];
if ($name === "") $errors[] = "Name is required.";
if (!ctype_digit($age) || (int)$age <= 0 || (int)$age > 120) $errors[] = "Please provide a valid age.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please provide a valid email.";
if (strlen($phone) < 7) $errors[] = "Please provide a valid phone number.";
if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

if ($errors) {
    json_response(["success" => false, "message" => implode(" ", $errors)], 422);
}

try {
    $pdo = get_db();

    $check = $pdo->prepare("SELECT registration_id FROM registration WHERE email = :email");
    $check->execute(["email" => $email]);
    if ($check->fetch()) {
        json_response(["success" => false, "message" => "An account with that email already exists. Try logging in instead."], 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "INSERT INTO registration (name, age, phone, email, member, password)
         VALUES (:name, :age, :phone, :email, :member, :password)"
    );
    $stmt->execute([
        "name" => $name,
        "age" => $age,
        "phone" => $phone,
        "email" => $email,
        "member" => $member,
        "password" => $hash,
    ]);

    // Registering also logs the person in, same as most sign-up flows.
    $_SESSION["user_id"] = (int)$pdo->lastInsertId();
    $_SESSION["user_name"] = $name;
    $_SESSION["user_email"] = $email;

    json_response(["success" => true, "name" => $name]);

} catch (Throwable $e) {
    json_response(["success" => false, "message" => db_error_message($e)], 500);
}
