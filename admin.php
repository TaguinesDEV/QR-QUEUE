<?php
// admin.php - Redesigned for Security, Clarity, and Correct Column Display

include('db.php');

// --- Function to safely fetch the queue data with all columns ---
function get_queue_data($conn) {
    // ⚠️ IMPORTANT: Select all 7 columns to be displayed in the table body.
    $sql = "SELECT 
                queue_number, student_id, full_name, year_section, 
                purpose, status, created_at 
            FROM queue 
            ORDER BY id ASC";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        error_log("Database Error in admin.php: " . $conn->error);
        return [];
    }
    
    // Fetch all results as an associative array
    return $result->fetch_all(MYSQLI_ASSOC);
}

$queue_data = get_queue_data($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrar Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="wrapper">
        <div class="admin-card">
            <h1>Registrar Dashboard</h1>
            
            <div class="actions">
                <button id="generateQRBtn">🧾 Generate QR</button>
                <button id="nextQueueBtn">▶ Next Student</button> 
                <button id="refreshQueueBtn">🔄 Refresh Queue</button>
            </div>
            
            <div id="qrDisplay" class="qr-container">
                <p class="text-muted">Click 'Generate QR' to create a new token link.</p>
            </div>
            
            <h2>Current Queue</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Queue #</th> 
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Year & Section</th>
                            <th>Purpose</th>
                            <th>Status</th> 
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="queueList">
                        <?php if (empty($queue_data)): ?>
                            <tr><td colspan="7" class="empty">No students in the queue.</td></tr>
                        <?php else: ?>
                            <?php foreach ($queue_data as $row): ?>
                                <?php
                                $display_purpose = nl2br(htmlspecialchars($row['purpose']));
                                $status_class = strtolower(htmlspecialchars($row['status']));
                                $display_status = htmlspecialchars($row['status']);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['queue_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['year_section']); ?></td>
                                    <td><?php echo $display_purpose; ?></td> 
                                    <td><span class='status-badge <?php echo $status_class; ?>'><?php echo $display_status; ?></span></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    // --- 1. startCountdown function definition (Must be accessible globally/locally) ---
    function startCountdown(seconds) {
        const countdownEl = document.getElementById('countdown');
        const btn = document.getElementById('generateQRBtn');
        
        // Safety check to prevent crashing if elements are momentarily missing
        if (!countdownEl || !btn) return; 

        // Actual countdown logic... (use the full code from section 2 above)
        // ...
        const interval = setInterval(() => {
            // ... timer logic ...
        }, 1000);
    }
    
    // --- 2. loadQueueList function definition (Must be accessible globally/locally) ---
    async function loadQueueList() {
        const queueListEl = document.getElementById('queueList');
        queueListEl.innerHTML = `<tr><td colspan="7" class="empty">Refreshing...</td></tr>`; 
        // ... (Your existing loadQueueList logic)
        try {
            const res = await fetch('load_queue.php');
            const html = await res.text();
            queueListEl.innerHTML = html;
        } catch (error) {
            queueListEl.innerHTML = `<tr><td colspan="7" class="empty text-danger">Failed to load queue.</td></tr>`;
        }
    }

    // --- 3. Attach all handlers only after the HTML is ready ---
    document.addEventListener('DOMContentLoaded', () => {
        
        // Attach Generate QR listener
        document.getElementById('generateQRBtn').addEventListener('click', async () => {
            const btn = document.getElementById('generateQRBtn');
            const qrDisplay = document.getElementById('qrDisplay');
            if (!btn || !qrDisplay) return; // Safety check
            
            btn.disabled = true;
            qrDisplay.innerHTML = `<p class="text-center text-muted py-2">Generating QR...</p>`;

            try {
                const res = await fetch('generate_qr.php');
                const data = await res.json();
                
                // ... (rest of your QR generation logic)
                if (data.exists || data.qr) {
                    const qrPath = data.qr;
                    const qrUrl = data.url;
                    const remainingSeconds = data.remaining;

                    qrDisplay.innerHTML = `
                        <img src="${qrPath}" alt="QR Code" style="max-width:200px;">
                        <p><b>Link:</b> <a href="${qrUrl}" target="_blank" rel="noopener noreferrer">${qrUrl}</a></p>
                        <p>⏳ Time left: <span id="countdown" style="color: red;"></span></p>
                    `;
                    startCountdown(remainingSeconds); // Calls the function defined above
                } else {
                    qrDisplay.innerHTML = `<p class="text-danger">Error generating QR. Please check the server logs.</p>`;
                    btn.disabled = false; // Re-enable on error
                }
            } catch (error) {
                qrDisplay.innerHTML = `<p class="text-danger">A network error occurred: ${error.message}</p>`;
                btn.disabled = false; // Re-enable on error
            }
        });

        // Attach Next Queue listener
        document.getElementById('nextQueueBtn').addEventListener('click', async () => {
            if (!confirm('Are you sure you want to call the next student?')) return;
            // ... (Your existing next queue logic)
            try {
                const res = await fetch('next_queue.php');
                const msg = await res.text();
                alert(msg);
                loadQueueList(); 
            } catch (error) {
                alert(`Error calling next student: ${error.message}`);
            }
        });
        
        // Attach Refresh listener
        document.getElementById('refreshQueueBtn').addEventListener('click', loadQueueList);
    });
</script>
</body>
</html>