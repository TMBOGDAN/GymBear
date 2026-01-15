<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function send_email($to, $type, $data = []) {

    $subject = '';
    $body = '';
    $replyTo = '';

    switch ($type) {

        case 'contact':
            $subject = "Mesaj de contact GymBear";
            $body = "
                <strong>Nume:</strong> {$data['name']}<br>
                <strong>Email:</strong> {$data['email']}<br>
                <strong>Mesaj:</strong><br>
                {$data['message']}
            ";
            $replyTo = $data['email'];
            break;

       


        default:
            return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tmbogdan15@gmail.com';
        $mail->Password   = 'mcbj qxrl ilvy pbda';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('tmbogdan15@gmail.com', 'GymBear');
        $mail->addAddress($to);

        if (!empty($replyTo)) {
            $mail->addReplyTo($replyTo);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("PHPMailer error: {$mail->ErrorInfo}");
        return false;
    }
}
