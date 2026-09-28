<?php
/**
 * AI chatbox backend. Called via fetch() from js/chatbot.js.
 * Expects JSON: { message: string, history: [{role, content}, ...] }
 * Responds JSON: { reply: string } or { error: string }
 *
 * Keeps the Anthropic API key on the server (includes/ai_config.php) —
 * it is never sent to the browser.
 */

require __DIR__ . "/includes/db.php";     // for json_response()
require __DIR__ . "/includes/ai_config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["error" => "Invalid request method."], 405);
}

if (ANTHROPIC_API_KEY === "YOUR_API_KEY_HERE" || ANTHROPIC_API_KEY === "") {
    json_response(["error" => "The chatbox isn't configured yet. Add your API key to includes/ai_config.php."], 500);
}

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$message = trim($input["message"] ?? "");
$history = is_array($input["history"] ?? null) ? $input["history"] : [];

if ($message === "") {
    json_response(["error" => "Please enter a message."], 422);
}
if (mb_strlen($message) > 2000) {
    json_response(["error" => "That message is too long."], 422);
}

// Only keep the last 10 turns of history, and only role/content — never
// trust the shape of client-sent data beyond that.
$messages = [];
foreach (array_slice($history, -10) as $turn) {
    if (!isset($turn["role"], $turn["content"])) continue;
    if (!in_array($turn["role"], ["user", "assistant"], true)) continue;
    $messages[] = ["role" => $turn["role"], "content" => (string)$turn["content"]];
}
$messages[] = ["role" => "user", "content" => $message];

$systemPrompt = "You are the friendly support assistant for Semenyih Joy Methodist Church's "
    . "website — act like a real help-desk agent (similar to Meta AI or a good customer support "
    . "chat): warm, proactive, and concise. Guide the visitor toward what they actually need "
    . "instead of just answering flatly.\n\n"
    . "Facts you know about the church: Sunday Worship & Holy Communion is 9:00-11:00 AM. "
    . "New members can sign up on the Register page. Visitors can reach the church on the "
    . "Contact Us page. There are pages for Events, E-Bulletin, Offerings, Our Member (photo "
    . "gallery), Mission & Vision, and Meet Our Developer.\n\n"
    . "When it's relevant, point people to the right page using a Markdown link with one of "
    . "these EXACT relative paths so the site can turn it into a working button: "
    . "[Register Now](registration.html), [Contact Us](contact.html), [View Events](events.html), "
    . "[Read the E-Bulletin](bulletin.html), [Give an Offering](offerings.html), "
    . "[Our Member Gallery](member.html), [Mission & Vision](about-us.html), [Log In](login.html). "
    . "Never invent a path that isn't in that list. Don't use markdown links for anything else.\n\n"
    . "If you don't know something specific about this church (exact staff names, precise "
    . "address, denominational policy, etc.), say so honestly and suggest the visitor use "
    . "[Contact Us](contact.html) rather than guessing. Keep replies short — 1-3 sentences plus "
    . "a link when helpful — unless the visitor clearly wants more detail.";

$payload = [
    "model" => ANTHROPIC_MODEL,
    "max_tokens" => 500,
    "system" => $systemPrompt,
    "messages" => $messages,
];

$ch = curl_init("https://api.anthropic.com/v1/messages");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-api-key: " . ANTHROPIC_API_KEY,
        "anthropic-version: 2023-06-01",
    ],
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    json_response(["error" => "Couldn't reach the AI service: " . $curlError], 502);
}

$data = json_decode($response, true);

if ($httpCode >= 400) {
    $apiMessage = $data["error"]["message"] ?? "The AI service returned an error.";
    json_response(["error" => $apiMessage], 502);
}

$reply = "";
foreach ($data["content"] ?? [] as $block) {
    if (($block["type"] ?? "") === "text") {
        $reply .= $block["text"];
    }
}

if ($reply === "") {
    json_response(["error" => "The AI didn't return a response. Please try again."], 502);
}

json_response(["reply" => $reply]);
