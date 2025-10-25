<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\OAuth;
    use League\OAuth2\Client\Provider\Google;
    require 'vendor/autoload.php';

    function sendEmail($to_email, $subject, $message) {
        $mail = new PHPMailer(true);

        

        try {
           

            // Account settings
            $from_email = '';
            $clientId = '';
            $clientSecret = '';
            $refreshToken = '';
            $mail->setFrom($from_email, 'Firstname Lastname');
            // Create a new OAuth2 provider instance
            $provider = new Google([
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ]);

            // Server settings
            $mail->isSMTP();
            $mail->SMTPDebug  = 0;
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->AuthType   = 'XOAUTH2';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->MessageID  = "<" . md5('HELLO'.(idate("U")-1000000000).uniqid()) . '@gmail.com>';
            $mail->XMailer    = 'Google Gmail';
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;

            // Pass the OAuth provider instance to PHPMailer
            $mail->setOAuth(new OAuth([
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $from_email,
            ]));

            // Recipients
            $mail->addAddress($to_email);


            // Randomly select one of two images
            $imgFiles = [
                __DIR__ . '/fe/img/hehe.jpg',
                __DIR__ . '/fe/img/hehe2.jpg'
            ];
            
            $imgIndex = rand(0, 1);
            $imgPath = $imgFiles[$imgIndex];
            $cid = 'randomimg' . uniqid();
            if (file_exists($imgPath)) {
                $mail->addEmbeddedImage($imgPath, $cid, basename($imgPath), 'base64', 'image/png');
                $imgHtml = '<div style="text-align:center;margin:20px 0;"><img src="cid:' . $cid . '" alt="Random Image" style="max-width:300px;border-radius:8px;box-shadow:0 2px 8px #bbb;"></div>';
            } else {
                $imgHtml = '';
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = '<!DOCTYPE html>' .
                '<html><head><meta charset="UTF-8"><title>' . htmlspecialchars($subject) . '</title></head>' .
                '<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">' .
                '<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;box-shadow:0 0 10px #ccc;padding:30px;">' .
                '<h2 style="color:#007bff;margin-top:0;">Message from Ibanking</h2>' .
                $imgHtml .
                '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                '<div style="font-size:1.1em;color:#333;">' . nl2br(htmlspecialchars($message)) . '</div>' .
                '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                '<div style="font-size:0.9em;color:#888;">Thank you for reading!<br>Visit our website for more news.</div>' .
                '</div></body></html>';
            $mail->AltBody = strip_tags($message);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
