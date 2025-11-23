<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Queue Number</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <h2>Thank You!</h2>
    <p>Your queue number is:</p>
    <h1 class="queue-num">#<?php echo htmlspecialchars($_GET['num']); ?></h1>
    <p>Please wait for your number to be called.</p>
  </div>
</body>
</html>
