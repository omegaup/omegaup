<?php

require_once 'libs/third_party/phpmailer/class.phpmailer.php';
require_once 'libs/third_party/phpmailer/class.smtp.php';

class Email {
    public static $log;
    private static $emailSender = null;

    /**
     * @param $email
     * @param $subject
     * @param $body
     * @throws EmailVerificationSendException
     */
    public static function sendEmail($emails, $subject, $body) {
        if (self::$emailSender != null) {
            self::$emailSender->sendEmail($emails, $subject, $body);
            return;
        }
        if (!OMEGAUP_EMAIL_SEND_EMAILS) {
            self::$log->info('Not sending email beacause OMEGAUP_EMAIL_SEND_EMAILS = FALSE, this is what I would have sent:');
            $mail = is_array($emails) ? join(',', $emails) : $emails;
            self::$log->info('     to = ' . $mail);
            self::$log->info('subject = ' . $subject);
            self::$log->info('   body = ' . $body);
            return;
        }

        self::$log->debug('Sending email to ' . (is_array($emails) ? join(',', $emails) : $emails));

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

    public static function setEmailSenderForTesting($emailSender) {
        Email::$emailSender = $emailSender;
    }
}

Email::$log = Logger::getLogger('email');
