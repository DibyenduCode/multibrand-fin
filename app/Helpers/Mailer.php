<?php
require_once __DIR__ . '/../Models/Setting.php';

class Mailer {

    public static function isSmtpConfigured(): bool {
        $host = Setting::get('smtp_host');
        $username = Setting::get('smtp_username');
        return !empty($host) && !empty($username);
    }

    public static function isEnabled(): bool {
        return (Setting::get('smtp_enabled', '1') === '1') && self::isSmtpConfigured();
    }

    /**
     * Send an email using configured SMTP settings or PHP mail() fallback
     *
     * @param string $toEmail
     * @param string $subject
     * @param string $htmlBody
     * @param string $recipientName
     * @return array ['success' => bool, 'message' => string]
     */
    public static function send(string $toEmail, string $subject, string $htmlBody, string $recipientName = ''): array {
        $toEmail = trim($toEmail);
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => "Invalid recipient email address: {$toEmail}"];
        }

        $host = trim((string)Setting::get('smtp_host', ''));
        $port = (int)Setting::get('smtp_port', 587);
        $username = trim((string)Setting::get('smtp_username', ''));
        $password = (string)Setting::get('smtp_password', '');
        $encryption = strtolower(trim((string)Setting::get('smtp_encryption', 'tls')));
        $fromEmail = trim((string)Setting::get('smtp_from_email', $username));
        $fromName = trim((string)Setting::get('smtp_from_name', 'Fin App Notifications'));

        if (empty($fromEmail)) {
            $fromEmail = $username ?: 'no-reply@localhost';
        }

        if (!self::isEnabled()) {
            // Attempt standard PHP mail() fallback if SMTP disabled or not configured
            return self::sendPhpMail($toEmail, $subject, $htmlBody, $fromEmail, $fromName, $recipientName);
        }

        return self::sendSmtpSocket($host, $port, $username, $password, $encryption, $fromEmail, $fromName, $toEmail, $recipientName, $subject, $htmlBody);
    }

    /**
     * Socket-based SMTP Mail sender supporting SSL, TLS (STARTTLS), AUTH LOGIN / AUTH PLAIN
     */
    private static function sendSmtpSocket(
        string $host,
        int $port,
        string $username,
        string $password,
        string $encryption,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $recipientName,
        string $subject,
        string $htmlBody
    ): array {
        $timeout = 15;
        $remoteHost = $host;

        if ($encryption === 'ssl') {
            $remoteHost = 'ssl://' . $host;
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client("{$remoteHost}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$socket) {
            return ['success' => false, 'message' => "SMTP Connection failed to {$host}:{$port} - ({$errno}) {$errstr}"];
        }

        stream_set_timeout($socket, $timeout);

        $readResponse = function() use ($socket) {
            $response = '';
            while ($str = fgets($socket, 512)) {
                $response .= $str;
                if (substr($str, 3, 1) === ' ') {
                    break;
                }
            }
            return $response;
        };

        $sendCommand = function(string $cmd) use ($socket, $readResponse) {
            fputs($socket, $cmd . "\r\n");
            return $readResponse();
        };

        // 1. Initial Greeting
        $res = $readResponse();
        if (substr($res, 0, 3) !== '220') {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP Greeting error: {$res}"];
        }

        // 2. EHLO
        $clientHost = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $res = $sendCommand("EHLO {$clientHost}");
        if (substr($res, 0, 3) !== '250') {
            $res = $sendCommand("HELO {$clientHost}");
            if (substr($res, 0, 3) !== '250') {
                fclose($socket);
                return ['success' => false, 'message' => "SMTP HELO failed: {$res}"];
            }
        }

        // 3. STARTTLS if TLS encryption specified
        if ($encryption === 'tls') {
            $res = $sendCommand("STARTTLS");
            if (substr($res, 0, 3) === '220') {
                $cryptoResult = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
                if (!$cryptoResult) {
                    // Try general TLS client method
                    $cryptoResult = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                }
                if (!$cryptoResult) {
                    fclose($socket);
                    return ['success' => false, 'message' => "SMTP STARTTLS negotiation failed."];
                }
                // Re-send EHLO after TLS negotiation
                $res = $sendCommand("EHLO {$clientHost}");
            }
        }

        // 4. Authenticate if username is set
        if (!empty($username)) {
            $res = $sendCommand("AUTH LOGIN");
            if (substr($res, 0, 3) === '334') {
                $res = $sendCommand(base64_encode($username));
                if (substr($res, 0, 3) === '334') {
                    $res = $sendCommand(base64_encode($password));
                    if (substr($res, 0, 3) !== '235') {
                        fclose($socket);
                        return ['success' => false, 'message' => "SMTP Authentication failed for user '{$username}': {$res}"];
                    }
                } else {
                    fclose($socket);
                    return ['success' => false, 'message' => "SMTP Username submission failed: {$res}"];
                }
            } else {
                // Try AUTH PLAIN
                $authPlain = base64_encode("\0" . $username . "\0" . $password);
                $res = $sendCommand("AUTH PLAIN " . $authPlain);
                if (substr($res, 0, 3) !== '235') {
                    fclose($socket);
                    return ['success' => false, 'message' => "SMTP AUTH PLAIN failed: {$res}"];
                }
            }
        }

        // 5. MAIL FROM
        $res = $sendCommand("MAIL FROM: <{$fromEmail}>");
        if (substr($res, 0, 3) !== '250') {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP MAIL FROM failed: {$res}"];
        }

        // 6. RCPT TO
        $res = $sendCommand("RCPT TO: <{$toEmail}>");
        if (substr($res, 0, 3) !== '250' && substr($res, 0, 3) !== '251') {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP RCPT TO failed for '{$toEmail}': {$res}"];
        }

        // 7. DATA
        $res = $sendCommand("DATA");
        if (substr($res, 0, 3) !== '354') {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP DATA command failed: {$res}"];
        }

        // Construct MIME Headers & Payload
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $fromFormatted = !empty($fromName) ? ('=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>') : $fromEmail;
        $toFormatted = !empty($recipientName) ? ('=?UTF-8?B?' . base64_encode($recipientName) . '?= <' . $toEmail . '>') : $toEmail;

        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: {$fromFormatted}";
        $headers[] = "To: {$toFormatted}";
        $headers[] = "Subject: {$encodedSubject}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: base64";
        $headers[] = "X-Mailer: FinGroup/1.0 PHP-SMTP";

        $emailContent = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";

        $res = $sendCommand($emailContent);
        if (substr($res, 0, 3) !== '250') {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP Email transmission failed: {$res}"];
        }

        $sendCommand("QUIT");
        fclose($socket);

        return ['success' => true, 'message' => "Email sent successfully to {$toEmail} via SMTP!"];
    }

    /**
     * Fallback standard PHP mail() function
     */
    private static function sendPhpMail(string $toEmail, string $subject, string $htmlBody, string $fromEmail, string $fromName, string $recipientName): array {
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $fromFormatted = !empty($fromName) ? ('=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>') : $fromEmail;

        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: {$fromFormatted}";
        $headers[] = "Reply-To: {$fromEmail}";
        $headers[] = "X-Mailer: PHP/" . phpversion();

        $sent = @mail($toEmail, $encodedSubject, $htmlBody, implode("\r\n", $headers));

        if ($sent) {
            return ['success' => true, 'message' => "Email sent successfully to {$toEmail} via PHP mail()!"];
        } else {
            return ['success' => false, 'message' => "Failed to send email to {$toEmail} via PHP mail(). Please configure valid SMTP settings."];
        }
    }

    /**
     * Send test email to verify SMTP credentials
     */
    public static function testConnection(string $testEmail): array {
        $subject = "SMTP Test Email - Fin Group Management";
        $timeStr = date('Y-m-d H:i:s');
        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                <div style='background-color: #0f172a; padding: 16px; text-align: center; border-radius: 8px;'>
                    <h2 style='color: #38bdf8; margin: 0; font-size: 20px;'>SMTP Configuration Test</h2>
                </div>
                <div style='padding: 20px; color: #334155; line-height: 1.6;'>
                    <p style='font-size: 16px;'>Hello,</p>
                    <p>This is a test email sent from your <strong>Fin Group Management System</strong> to verify that your SMTP server settings are correctly configured and working!</p>
                    <div style='background-color: #f8fafc; border-left: 4px solid #0284c7; padding: 12px 16px; margin: 16px 0;'>
                        <p style='margin: 0; font-size: 13px; color: #64748b;'>Timestamp: <strong>{$timeStr}</strong></p>
                        <p style='margin: 4px 0 0 0; font-size: 13px; color: #64748b;'>Recipient: <strong>{$testEmail}</strong></p>
                    </div>
                    <p>If you received this message, your SMTP settings are active and ready to deliver fixed expense notifications to brand admins.</p>
                </div>
                <div style='text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 12px;'>
                    &copy; " . date('Y') . " Fin Group Management. All rights reserved.
                </div>
            </div>
        ";

        return self::send($testEmail, $subject, $html);
    }
}
