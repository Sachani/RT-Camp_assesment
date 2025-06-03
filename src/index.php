<?php
require_once __DIR__ . '/functions.php';

session_start();
$messages = [];

if (!isset($_SESSION['pending_codes'])) {
    $_SESSION['pending_codes'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['verification_code'])) {
        $email = strtolower(trim($_POST['email']));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            $_SESSION['pending_codes'][$email] = $code;
            sendVerificationEmail($email, $code);
            $messages[] = "Verification code sent to $email.";
            $last_email = $email;
        } else {
            $messages[] = "Invalid email address.";
        }
    } elseif (isset($_POST['verification_code']) && isset($_POST['email'])) {
        $email = strtolower(trim($_POST['email']));
        $code = trim($_POST['verification_code']);
        if (
            isset($_SESSION['pending_codes'][$email]) &&
            $_SESSION['pending_codes'][$email] === $code
        ) {
            registerEmail($email);
            unset($_SESSION['pending_codes'][$email]);
            $messages[] = "Email successfully registered! You will receive XKCD comics.";
        } else {
            $messages[] = "Invalid verification code.";
        }
        $last_email = $email;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>XKCD Email Subscription</title>
</head>
<body>
    <h1>XKCD Daily Comic Subscription</h1>
    <?php foreach ($messages as $m): ?>
        <p style="color:green;"><?php echo htmlspecialchars($m); ?></p>
    <?php endforeach; ?>

  
    <form method="post">
        <label>Email to subscribe:</label><br>
        <input type="email" name="email" required value="<?php echo isset($last_email) ? htmlspecialchars($last_email) : ''; ?>">
        <button id="submit-email">Submit</button>
    </form>
    <br>

   
    <form method="post">
        <label>Verification code:</label><br>
        <input type="email" name="email" required value="<?php echo isset($last_email) ? htmlspecialchars($last_email) : ''; ?>">
        <input type="text" name="verification_code" maxlength="6" required>
        <button id="submit-verification">Verify</button>
    </form>
</body>
</html>