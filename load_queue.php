<?php
// load_queue.php - Fetches queue data and outputs ONLY <tr> elements.

include('db.php');

// Ensure $conn is a valid mysqli connection object
if (!isset($conn) || $conn->connect_error) {
    // If DB fails, return a single error row spanning all 7 columns
    echo '<tr><td colspan="7" class="empty text-danger">Database connection failed.</td></tr>';
    exit;
}

// 1. Prepare and Execute the SELECT query
// We select all 7 necessary fields (including status, which is used for the badge)
$sql = "SELECT queue_number, student_id, full_name, year_section, purpose, status, created_at 
        FROM queue 
        ORDER BY id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // 2. Loop through results and generate table rows
    while ($row = $result->fetch_assoc()) {
        // Prepare data for secure and formatted output
        $display_purpose = nl2br(htmlspecialchars($row['purpose']));
        $status_class = strtolower(htmlspecialchars($row['status']));
        $display_status = htmlspecialchars($row['status']);

        echo "<tr>
                <td>" . htmlspecialchars($row['queue_number']) . "</td>
                <td>" . htmlspecialchars($row['student_id']) . "</td>
                <td>" . htmlspecialchars($row['full_name']) . "</td>
                <td>" . htmlspecialchars($row['year_section']) . "</td>
                <td>{$display_purpose}</td>
                <td><span class='status-badge {$status_class}'>{$display_status}</span></td>
                <td>" . htmlspecialchars($row['created_at']) . "</td>
              </tr>";
    }
} else {
    // 3. Output "No data" message row, spanning all 7 columns
    echo '<tr><td colspan="7" class="empty">No one in the queue yet.</td></tr>';
}

$stmt->close();
?>