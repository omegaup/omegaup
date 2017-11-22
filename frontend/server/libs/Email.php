<?php
class Email {
    public static $log;

    /**
     * @param $email
     * @param $subject
     * @param $body
     * @throws EmailVerificationSendException
     */
    public static function sendEmail($emails, $subject, $body) {
        if (!OMEGAUP_EMAIL_SEND_EMAILS) {
            self::$log->info('Not sending email beacause OMEGAUP_EMAIL_SEND_EMAILS = FALSE, this is what I would have sent:');
            $mail = is_array($emails) ? join(',', $emails) : $emails;
            self::$log->info('     to = ' . $mail);
            self::$log->info('subject = ' . $subject);
            self::$log->info('   body = ' . $body);
            return;
        }

        self::$log->info('Really sending email.');

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host = OMEGAUP_EMAIL_SMTP_HOST;
        $mail->CharSet = 'utf-8';
        $mail->SMTPAuth = true;
        $mail->Password = OMEGAUP_EMAIL_SMTP_PASSWORD;
        $mail->From = OMEGAUP_EMAIL_SMTP_FROM;
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->Username = OMEGAUP_EMAIL_SMTP_FROM;

        $mail->FromName = OMEGAUP_EMAIL_SMTP_FROM;
        if (is_array($emails)) {
            foreach ($emails as $email) {
                $mail->AddAddress($email);
            }
        } else {
            $mail->AddAddress($emails);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        if (!$mail->Send()) {
            self::$log->error('Failed to send mail: ' . $mail->ErrorInfo);
            throw new EmailVerificationSendException();
        }
    }
}

Email::$log = Logger::getLogger('email');
