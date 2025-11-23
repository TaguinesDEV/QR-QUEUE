<?php
// index.php - Redesigned for Security and Clarity

// Include the database connection (assuming $conn is the mysqli object)
include('db.php');

// --- 1. Basic Token Validation ---
if (!isset($_GET['token']) || empty($_GET['token'])) {
    http_response_code(403); // Use proper HTTP status code
    die("<h2>⚠️ Invalid access &mdash; No QR token provided.</h2>");
}

$token = $_GET['token'];

// --- 2. Secure Query with Prepared Statements ---
// Prevents SQL Injection by separating query structure from user input
$stmt = $conn->prepare("SELECT created_at FROM qr_codes WHERE qr_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404); // Use proper HTTP status code
    die("<h2>🚫 Invalid or expired QR code.</h2>");
}

// --- 3. Expiration Check (12 hours) ---
$row = $res->fetch_assoc();
$created_timestamp = strtotime($row['created_at']);
$twelve_hours_in_seconds = 43200;

if (time() - $created_timestamp > $twelve_hours_in_seconds) {
    // Optionally, you could mark the token as 'used' or 'expired' in the DB here.
    http_response_code(410); // Use proper HTTP status code (Gone)
    die("<h2>⏱️ QR code expired. Please scan a new one.</h2>");
}

// If all checks pass, the form HTML is rendered below.

// Clean up
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Queue Form</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <div class="form-card">
            <h1>Registrar Queue</h1>
            <p class="subtitle">Please fill in your details to get your queue number</p>

            <form action="process.php" method="POST" class="queue-form">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" required 
                           placeholder="e.g. 2025-12345" 
                           pattern="[0-9]{4}-[0-9]{5}">
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="e.g. Juan Dela Cruz">
                </div>

                <div class="form-group">
                    <label for="year_section">Year & Section</label>
                    <input type="text" id="year_section" name="year_section" required placeholder="e.g. BSIT 3-A">
                </div>

                <div class="form-group">
                    <label>Purpose</label>
                    <div class="checkbox-group" role="group" aria-labelledby="purpose-label">
                        <label><input type="checkbox" name="purpose[]" value="COR"> COR</label>
                        <label><input type="checkbox" name="purpose[]" value="Grades per Sem"> Grades per Sem</label>
                        <label><input type="checkbox" name="purpose[]" value="Prospectus"> Prospectus</label>
                        <label><input type="checkbox" name="purpose[]" value="Signature from Registrar"> Signature from Registrar</label>
                        
                        <label for="purpose_other_check">
                            <input type="checkbox" id="purpose_other_check" value="Others"> Others
                        </label>
                        
                        <input type="text" id="other_text" name="other_text" placeholder="Please specify..." style="display:none;" aria-expanded="false">
                    </div>
                </div>

                <button type="submit">Get Queue Number</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const otherCheckbox = document.getElementById('purpose_other_check');
            const otherInput = document.getElementById('other_text');

            // Use the actual checkbox's value for the form submission
            const otherHiddenInput = document.createElement('input');
            otherHiddenInput.type = 'hidden';
            otherHiddenInput.name = 'purpose[]';
            otherHiddenInput.value = 'Others';
            
            otherCheckbox.addEventListener('change', () => {
                const isChecked = otherCheckbox.checked;
                
                otherInput.style.display = isChecked ? 'block' : 'none';
                otherInput.setAttribute('aria-expanded', isChecked);
                
                if (isChecked) {
                    otherInput.focus();
                    // Append the 'Others' value to the form only when checked
                    otherCheckbox.closest('label').appendChild(otherHiddenInput);
                } else {
                    otherInput.value = ''; // Clear and hide the text
                    // Remove the 'Others' value from the purpose array when unchecked
                    if(otherHiddenInput.parentNode) {
                        otherHiddenInput.parentNode.removeChild(otherHiddenInput);
                    }
                }
            });
        });
    </script>
</body>
</html>