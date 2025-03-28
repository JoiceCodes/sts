<?php
 // This remains the same, getting case number from GET for display
 $caseNumber = isset($_GET['case']) && !empty($_GET['case']) ? htmlspecialchars(base64_decode($_GET['case'])) : '';
 $message = isset($_GET['success']) ? "Your rating has been successfully submitted." : "Thank you for your feedback!";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You!</title>
     <link rel="stylesheet" href="./assets/css/style.css">
     <style>
         /* Styling remains the same */
         body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
         .container { max-width: 500px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
         h2 { color: #28a745; margin-bottom: 15px; }
         p { color: #555; }
     </style>
</head>
<body>
    <div class="container">
        <h2>Thank You!</h2>
        <p><?php echo $message; ?></p>
        <?php if ($caseNumber): ?>
        <p>Regarding Case #: <strong><?php echo $caseNumber; ?></strong></p>
        <?php endif; ?>
        <p>We appreciate you taking the time to provide feedback.</p>
        <p>You can close this page now.</p>
    </div>
</body>
</html>