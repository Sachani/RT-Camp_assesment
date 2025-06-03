<?php

function generateVerificationCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $body = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: prajapatikrish132005@gmail.com\r\n";
    return mail($email, $subject, $body, $headers);
}

function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $email = strtolower(trim($email));
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
        return true;
    }
    return false;
}

function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $email = strtolower(trim($email));
    $emails = array_filter($emails, function($e) use ($email) {
        return strtolower(trim($e)) !== $email;
    });
    file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL, LOCK_EX);
    return true;
}

function fetchAndFormatXKCDData(): string {
    $latest = json_decode(@file_get_contents("https://xkcd.com/info.0.json"), true);
    if (!$latest || !isset($latest['num'])) return '';
    $max = (int)$latest['num'];
    $rand = random_int(1, $max);
    $comic = json_decode(@file_get_contents("https://xkcd.com/$rand/info.0.json"), true);
    if (!$comic) return '';
    $img = htmlspecialchars($comic['img']);
    $alt = htmlspecialchars($comic['alt']);
    $title = htmlspecialchars($comic['title']);
    $html = "<h2>XKCD Comic</h2>";
    $html .= "<img src=\"$img\" alt=\"XKCD Comic\">";
    $html .= "<p><em>$title</em></p>";
    $html .= "<p>$alt</p>";
    return $html;
}

function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $bodyContent = fetchAndFormatXKCDData();
    if (!$bodyContent) return;
    foreach ($emails as $email) {
        $unsubscribe_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
        "://{$_SERVER['HTTP_HOST']}/src/unsubscribe.php?email=" . urlencode($email);
        $body = $bodyContent . '<p><a href="' . $unsubscribe_link . '" id="unsubscribe-button">Unsubscribe</a></p>';
        $subject = "Your XKCD Comic";
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: prajapatikrish132005@gmail.com\r\n";
        mail($email, $subject, $body, $headers);
    }
}