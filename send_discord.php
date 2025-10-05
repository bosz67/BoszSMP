<?php
header('Content-Type: application/json');

// Ontvang order-data
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
  http_response_code(400);
  echo json_encode(["error" => "Geen data ontvangen", "raw" => $raw]);
  exit;
}

// Webhook
$webhook = "https://discord.com/api/webhooks/1422274875569078392/KX9p1LU9lwq9_4MbhM8kB9r0FU2EJMDd26t5FwEx-qlJ0qHXMDzI9hR4uwM_nsLXELkI";

// Order gegevens
$name  = $data["name"] ?? "Onbekende klant";
$items = $data["items"] ?? $data["cart"] ?? [];
$total = $data["total"] ?? 0;

$itemList = [];
foreach ($items as $it) {
  $title = $it["title"] ?? $it["name"] ?? "Onbekend product";
  $qty   = $it["qty"] ?? 1;
  $itemList[] = $qty . "× " . $title;
}

// Embed bericht
$embed = [
  "title" => "Nieuwe donatie! 🎉",
  "description" => "Bedankt **{$name}** voor het kopen van " .
                   implode(", ", $itemList) .
                   " voor een totaal van **€" . number_format($total, 2, ",", ".") . "**!",
  "color" => hexdec("57F287")
];

$payload = json_encode([ "embeds" => [$embed] ]);

// Verstuur naar Discord
$ch = curl_init($webhook);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
  http_response_code(500);
  echo json_encode(["error" => $err]);
} else {
  echo json_encode([
    "success" => true,
    "httpCode" => $httpCode,
    "discordResponse" => $response
  ]);
}
