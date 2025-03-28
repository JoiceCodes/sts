<?php
session_start();
require_once "./config/database.php"; // Adjust path if needed

// Check if required parameters are present in the URL
if (!isset($_GET['id']) || !isset($_GET['case'])) { // Still expect both for context
    die("Invalid rating link. Required parameters are missing.");
}

// Decode parameters
$engineerIdEncoded = $_GET['id'];
$caseNumberEncoded = $_GET['case']; // Still decode for display
$engineerId = base64_decode($engineerIdEncoded);
$caseNumber = base64_decode($caseNumberEncoded);

// Validate decoded values (basic check)
if (!$engineerId || !$caseNumber) {
    die("Invalid rating link. Parameters could not be decoded.");
}

// --- REMOVED: Check if already rated (No longer possible with this table structure) ---

// --- Fetch Engineer Name (for display) ---
$engineerName = 'the Engineer'; // Default
$getEngNameStmt = mysqli_prepare($connection, "SELECT full_name FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($getEngNameStmt, "i", $engineerId); // Assuming engineer_id is integer
mysqli_stmt_execute($getEngNameStmt);
$result = mysqli_stmt_get_result($getEngNameStmt);
if ($row = mysqli_fetch_assoc($result)) {
    $engineerName = htmlspecialchars($row['full_name']);
}
mysqli_stmt_close($getEngNameStmt);
mysqli_close($connection); // Close connection

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Your Engineer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Basic styling - same as before */
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        p { color: #555; }
        .rating-question { font-weight: bold; margin-bottom: 20px; font-size: 1.1em; }
        .rating-options { display: flex; justify-content: space-around; margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .rating-options label { display: flex; flex-direction: column; align-items: center; cursor: pointer; text-align: center; padding: 5px; }
        .rating-options input[type="radio"] { margin-bottom: 8px; transform: scale(1.2); }
        .rating-options span { font-size: 0.9em; color: #666; }
        .submit-btn { display: block; width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer; transition: background-color 0.3s ease; }
        .submit-btn:hover { background-color: #0056b3; }
        .case-info { font-size: 0.9em; text-align: center; color: #777; margin-bottom: 25px;}
        .error-message { color: red; text-align: center; margin-bottom: 15px;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Rate Your Support Experience</h2>
        <p class="case-info">Regarding Case #: <strong><?php echo htmlspecialchars($caseNumber); ?></strong></p>

        <?php
            if (isset($_GET['error'])) {
                echo '<p class="error-message">'.htmlspecialchars($_GET['error']).'</p>';
            }
        ?>

        <form action="submit_rating.php" method="POST" onsubmit="return validateRating();">
            <input type="hidden" name="engineer_id_encoded" value="<?php echo $engineerIdEncoded; ?>">
            <input type="hidden" name="case_number_encoded" value="<?php echo $caseNumberEncoded; ?>">


            <p class="rating-question">How satisfied were you with the support provided by <strong><?php echo $engineerName; ?></strong>?</p>

            <div class="rating-options" id="ratingGroup">
                <label>
                    <input type="radio" name="rating" value="1" required>
                    <span>Very Dissatisfied</span>
                </label>
                <label>
                    <input type="radio" name="rating" value="2">
                    <span>Dissatisfied</span>
                </label>
                <label>
                    <input type="radio" name="rating" value="3">
                    <span>Neutral</span>
                </label>
                <label>
                    <input type="radio" name="rating" value="4">
                    <span>Satisfied</span>
                </label>
                <label>
                    <input type="radio" name="rating" value="5">
                    <span>Very Satisfied</span>
                </label>
            </div>
            <p id="ratingError" class="error-message" style="display: none;">Please select a rating.</p>

            <button type="submit" class="submit-btn">Submit Rating</button>
        </form>
    </div>

    <script>
        // Basic client-side validation - same as before
        function validateRating() {
            const ratingGroup = document.getElementById('ratingGroup');
            const selectedRating = ratingGroup.querySelector('input[name="rating"]:checked');
            const errorP = document.getElementById('ratingError');
            if (!selectedRating) {
                errorP.style.display = 'block';
                return false; // Prevent form submission
            }
             errorP.style.display = 'none';
            return true; // Allow form submission
        }
    </script>
</body>
</html>