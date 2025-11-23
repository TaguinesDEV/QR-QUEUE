<?php
include('db.php');
include('phpqrcode/qrlib.php');

$validHours = 12;

// Check for existing valid QR
$check = $conn->query("SELECT * FROM qr_codes WHERE TIMESTAMPDIFF(HOUR, created_at, NOW()) < $validHours ORDER BY id DESC LIMIT 1");

if ($check->num_rows > 0) {
  $qr = $check->fetch_assoc();
  $timeLeft = $validHours * 3600 - (time() - strtotime($qr['created_at']));
  echo json_encode([
    "exists" => true,
    "qr" => "qrcodes/{$qr['qr_token']}.png",
    "url" => "http://localhost/sir-A/index.php?token={$qr['qr_token']}",
    "remaining" => $timeLeft
  ]);
  exit;
}

// No valid QR — generate new
$token = bin2hex(random_bytes(16));
$conn->query("INSERT INTO qr_codes (qr_token) VALUES ('$token')");

$url = "http://localhost/sir-A/index.php?token=$token";
$filePath = "qrcodes/$token.png";
if (!is_dir("qrcodes")) mkdir("qrcodes", 0777, true);

QRcode::png($url, $filePath, QR_ECLEVEL_L, 6);

echo json_encode([
  "exists" => false,
  "token" => $token,
  "qr" => $filePath,
  "url" => $url,
  "remaining" => $validHours * 3600
]);
?>
