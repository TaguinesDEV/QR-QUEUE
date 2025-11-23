<?php
include('db.php');

// Mark currently serving as Done
$conn->query("UPDATE queue SET status='Done' WHERE status='Serving'");

// Find next Waiting student
$result = $conn->query("SELECT id FROM queue WHERE status='Waiting' ORDER BY id ASC LIMIT 1");
if ($result->num_rows == 0) {
  echo "No more students in queue.";
  exit;
}
$row = $result->fetch_assoc();
$nextId = $row['id'];

// Update to Serving
$conn->query("UPDATE queue SET status='Serving' WHERE id=$nextId");

echo "Next student is now Serving (Queue #{$nextId}).";
?>
