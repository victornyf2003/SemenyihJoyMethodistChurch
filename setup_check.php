<?php
/**
 * Open this in your browser (http://localhost/.../setup_check.php) to see
 * exactly why registration/login might be failing. DELETE this file before
 * putting the site online.
 */
require __DIR__ . "/includes/db.php";

$checks = [];
$checks[] = ["PHP version (need 7.4+)", version_compare(PHP_VERSION, "7.4.0", ">="), PHP_VERSION];
$checks[] = ["pdo_mysql extension enabled", extension_loaded("pdo_mysql"),
    extension_loaded("pdo_mysql") ? "yes" : "No. Enable extension=pdo_mysql in php.ini, then restart Apache."];

$ok = false; $detail = "";
try {
    $pdo = get_db();
    $ok = true;
    $detail = "Connected to database '" . DB_NAME . "' as '" . DB_USER . "'.";
    $count = $pdo->query("SELECT COUNT(*) FROM registration")->fetchColumn();
    $checks[] = ["Table 'registration' exists", true, "$count registered member(s) so far."];
} catch (Throwable $e) {
    $detail = $e->getMessage();
    $hint = "";
    if (strpos($detail, "2002") !== false) $hint = " → MySQL isn't running (or wrong host/port). Start MySQL in XAMPP/WAMP/MAMP, and check DB_PORT in includes/db.php.";
    elseif (strpos($detail, "1045") !== false) $hint = " → Wrong DB_USER/DB_PASS in includes/db.php.";
    $detail .= $hint;
}
array_splice($checks, 2, 0, [["Connect to MySQL", $ok, $detail]]);
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Setup check</title>
<style>body{font-family:system-ui,sans-serif;max-width:720px;margin:40px auto;padding:0 16px}
li{margin:10px 0;padding:10px 14px;border-radius:8px}.ok{background:#e3f3e6}.bad{background:#fbe4e7}</style></head>
<body><h1>Setup check</h1><ul>
<?php foreach ($checks as [$label, $pass, $info]): ?>
<li class="<?= $pass ? "ok" : "bad" ?>"><?= $pass ? "✅" : "❌" ?> <strong><?= htmlspecialchars($label) ?></strong><br><?= htmlspecialchars($info) ?></li>
<?php endforeach; ?>
</ul><p>Delete <code>setup_check.php</code> and set <code>DEBUG = false</code> in <code>includes/db.php</code> before going live.</p></body></html>
