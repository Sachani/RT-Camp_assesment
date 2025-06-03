<?php
require_once __DIR__ . '/functions.php';

session_start();
$messages = [];

if (!isset($_SESSION['pending_unsubs'])) {
    $_SESSION['pending_unsubs'] = [];
}

if (isset($_GET['email'])) {
    $pre_email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) ? strtolower($_GET['email']) : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && !isset($_POST['verification_code'])) {
        $email = strtolower(trim($_POST['unsubscribe_email']));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            $_SESSION['pending_unsubs'][$email] = $code;
            $subject = "Confirm Un-subscription";
            $body = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: no-reply@example.com\r\n";
            mail($email, $subject, $body, $headers);
            $messages[] = "Unsubscription verification code sent to $email.";
            $last_email = $email;
        } else {
            $messages[] = "Invalid email address.";
        }
    } elseif (isset($_POST['verification_code']) && isset($_POST['unsubscribe_email'])) {
        $email = strtolower(trim($_POST['unsubscribe_email']));
        $code = trim($_POST['verification_code']);
        if (
            isset($_SESSION['pending_unsubs'][$email]) &&
            $_SESSION['pending_unsubs'][$email] === $code
        ) {
            unsubscribeEmail($email);
            unset($_SESSION['pending_unsubs'][$email]);
            $messages[] = "You have been unsubscribed successfully.";
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
    <title>XKCD Unsubscribe</title>
</head>
<body>
    <h1>Unsubscribe from XKCD Comics</h1>
    <?php foreach ($messages as $m): ?>
        <p style="color:green;"><?php echo htmlspecialchars($m); ?></p>
    <?php endforeach; ?>
   
    <form method="post">
        <label>Email to unsubscribe:</label><br>
        <input type="email" name="unsubscribe_email" required value="<?php
            echo isset($last_email) ? htmlspecialchars($last_email) : (isset($pre_email) ? htmlspecialchars($pre_email) : '');
        ?>">
        <button id="submit-unsubscribe">Unsubscribe</button>
    </form>
    <br>
    
    <form method="post">
        <label>Verification code:</label><br>
        <input type="email" name="unsubscribe_email" required value="<?php
            echo isset($last_email) ? htmlspecialchars($last_email) : (isset($pre_email) ? htmlspecialchars($pre_email) : '');
        ?>">
        <input type="text" name="verification_code" maxlength="6" required>
        <button id="submit-verification">Verify</button>
    </form>
</body>
</html>