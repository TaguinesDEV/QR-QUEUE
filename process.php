<?php
include('db.php');

// Get last queue number
$result = $conn->query("SELECT queue_number FROM queue ORDER BY id DESC LIMIT 1");
$row = $result->fetch_assoc();
$last_number = $row ? $row['queue_number'] : 0;
$new_number = $last_number + 1;

// Collect form data
$student_id = $_POST['student_id'];
$full_name = $_POST['full_name'];
$year_section = $_POST['year_section'];

// Handle purposes
$purpose_array = isset($_POST['purpose']) ? $_POST['purpose'] : [];
$other_text = trim($_POST['other_text'] ?? '');

if (in_array('Others', $purpose_array) && $other_text !== '') {
    // Replace “Others” with the actual text entered
    $key = array_search('Others', $purpose_array);
    $purpose_array[$key] = $other_text;
}

$purpose = implode(", ", $purpose_array);

// Insert into DB
$stmt = $conn->prepare("INSERT INTO queue (student_id, full_name, year_section, purpose, queue_number) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $student_id, $full_name, $year_section, $purpose, $new_number);
$stmt->execute();

// Redirect to queue number page
header("Location: queue_number.php?num=$new_number");
exit;
?>
