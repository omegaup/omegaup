<?php

namespace OmegaUp;

class Email {
    /** @var \Monolog\Logger */
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
                'Not sending email because OMEGAUP_EMAIL_SEND_EMAILS = FALSE, this is what I would have sent:'
            );
            self::$log->info('     to = ' . join(',', $emails));
            self::$log->info('subject = ' . $subject);
            self::$log->info('   body = ' . $body);
            return;
        }

        self::$log->debug('Sending email to ' . join(',', $emails));

        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                self::sendEmailSingle($emails, $subject, $body);
                return; // Success, exit retry loop
            } catch (\Exception $e) {
                $retryCount++;

                if ($retryCount >= $maxRetries) {
                    self::$log->error(
                        "Failed to send email after {$maxRetries} attempts: {$e->getMessage()}"
                    );
                    throw new \OmegaUp\Exceptions\EmailVerificationSendException();
                }

                // Wait before retry
                sleep(1);
                self::$log->warning(
                    "Retrying email send ({$retryCount}/{$maxRetries}) after error: {$e->getMessage()}"
                );
            }
        }
    }

    /**
     * Single email send attempt
     */
    private static function sendEmailSingle(
        array $emails,
        string $subject,
        string $body
    ): void {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->Host = OMEGAUP_EMAIL_SMTP_HOST;
        $mail->CharSet = 'utf-8';
        $mail->SMTPAuth = true;
        $mail->Password = OMEGAUP_EMAIL_SMTP_PASSWORD;
        $mail->From = OMEGAUP_EMAIL_SMTP_FROM;
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->Username = OMEGAUP_EMAIL_SMTP_FROM;
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = true;

        $mail->FromName = OMEGAUP_EMAIL_SMTP_FROM;
        foreach ($emails as $email) {
            $mail->AddAddress($email);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        if (!$mail->Send()) {
            throw new \RuntimeException("PHPMailer error: {$mail->ErrorInfo}");
        }
    }

    public static function setEmailSenderForTesting(
        ?\OmegaUp\EmailSender $emailSender
    ): void {
        self::$emailSender = $emailSender;
    }
}

Email::$log = \Monolog\Registry::omegaup()->withName('email');
