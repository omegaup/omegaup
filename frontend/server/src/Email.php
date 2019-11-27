<?php

namespace OmegaUp;

class Email {
    /** @var \Logger */
    public static $log;

    /** @var null|\OmegaUp\EmailSender */
    private static $emailSender = null;

    /**
     * @param string[] $emails
     * @param string $subject
     * @param string $body
     * @throws \OmegaUp\Exceptions\EmailVerificationSendException
     */
    public static function sendEmail(
        array $emails,
        string $subject,
        string $body
    ): void {
        if (!is_null(self::$emailSender)) {
            self::$emailSender->sendEmail($emails, $subject, $body);
            return;
        }
        if (!OMEGAUP_EMAIL_SEND_EMAILS) {
            self::$log->info(
                'Not sending email beacause OMEGAUP_EMAIL_SEND_EMAILS = FALSE, this is what I would have sent:'
            );
            self::$log->info('     to = ' . join(',', $emails));
            self::$log->info('subject = ' . $subject);
            self::$log->info('   body = ' . $body);
            return;
        }

        require_once 'libs/third_party/phpmailer/class.phpmailer.php';
        require_once 'libs/third_party/phpmailer/class.smtp.php';

        self::$log->debug('Sending email to ' . join(',', $emails));

        $mail = new \PHPMailer();
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
        foreach ($emails as $email) {
            $mail->AddAddress($email);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        if (!$mail->Send()) {
            self::$log->error("Failed to send mail: {$mail->ErrorInfo}");
            throw new \OmegaUp\Exceptions\EmailVerificationSendException();
        }
    }

    public static function setEmailSenderForTesting(
        ?\OmegaUp\EmailSender $emailSender
    ): void {
        self::$emailSender = $emailSender;
    }
}

Email::$log = \Logger::getLogger('email');
