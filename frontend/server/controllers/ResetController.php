<?php
class ResetController extends Controller {
    /**
     * Creates a reset operation, the first of two steps needed to reset a
     * password. The first step consist of sending an email to the user with
     * instructions to reset he's password, if and only if the email is valid.
     * @param Request $r
     * @return array
     * @throws InvalidParameterException
     */
    public static function apiCreate(Request $r) {
        self::ValidateCreateRequest($r);
        $email = $r['email'];
        $token = ApiUtils::GetRandomString();
        $reset_digest = hash('sha1', $token);
        $reset_sent_at = ApiUtils::GetStringTime();

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host = OMEGAUP_EMAIL_SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Password = OMEGAUP_EMAIL_SMTP_PASSWORD;
        $mail->From = OMEGAUP_EMAIL_SMTP_FROM;
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->Username = OMEGAUP_EMAIL_SMTP_FROM;

        $mail->FromName = OMEGAUP_EMAIL_SMTP_FROM;
        $mail->AddAddress($email);
        $mail->isHTML(true);

        $user = UsersDAO::FindByEmail($email);
        $user->setResetDigest($reset_digest);
        $user->setResetSentAt($reset_sent_at);
        UsersDAO::save($user);

        if (IS_TEST) {
            return array('status' => 'ok', 'token' => $token);
        }

        global $smarty;
        $mail->Subject = $smarty->getConfigVariable('wordsReset');
        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode($email) . '&reset_token=' . $token;
        $message = $smarty->getConfigVariable('wordsResetMessage');
        $mail->Body = str_replace('[link]', $link, $message);

        if (!$mail->Send()) {
            self::$log->error('Failed to send mail:'. $mail->ErrorInfo);
            $user->setResetDigest(null);
            $user->setResetSentAt(null);
            UsersDAO::save($user);
        }

        return array(
            'status' => 'ok',
            'message' => $smarty->getConfigVariable('passwordResetRequestSuccess')
        );
    }

    /**
     * Updates the password of a given user, this is the second and last step
     * in order to reset the password. This operation is done if and only if
     * the correct parameters are suplied.
     * @param Request $r
     * @return array
     * @throws InvalidParameterException
     */
    public static function apiUpdate(Request $r) {
        self::ValidateUpdateRequest($r);
        $user = UsersDAO::FindByEmail($r['email']);
        $user->setPassword(SecurityTools::hashString($r['password']));
        $user->setResetDigest(null);
        $user->setResetSentAt(null);
        UsersDAO::save($user);

        global $smarty;
        return array(
            'status' => 'ok',
            'message' =>  IS_TEST ? 'message' : $smarty->getConfigVariable('passwordResetResetSuccess')
        );
    }

    private static function validateCreateRequest($r) {
        $user = UsersDAO::FindByEmail($r['email']);
        if (is_null($user)) {
            throw new InvalidParameterException('invalidUser');
        }

        if (!$user->verified) {
            throw new InvalidParameterException('unverifiedUser');
        }

        $seconds = time() - strtotime($user->reset_sent_at);
        if ($seconds < PASSWORD_RESET_MIN_WAIT) {
            throw new InvalidParameterException('passwordResetMinWait');
        }
    }

    private static function validateUpdateRequest($r) {
        $user = UsersDAO::FindByEmail($r['email']);
        $reset_token = $r['reset_token'];
        $password = $r['password'];
        $password_confirmation = $r['password_confirmation'];
        if (is_null($user)
            || is_null($reset_token)
            || is_null($password)
            || is_null($password_confirmation)) {
            throw new InvalidParameterException('invalidParameters');
        }

        if ($user->reset_digest !== hash('sha1', $reset_token)) {
            throw new InvalidParameterException('invalidResetToken');
        }

        if ($password !== $password_confirmation) {
            throw new InvalidParameterException('passwordMismatch');
        }

        SecurityTools::testStrongPassword($password);

        $seconds = time() - strtotime($user->reset_sent_at);
        if ($seconds > PASSWORD_RESET_TIMEOUT) {
            throw new InvalidParameterException('passwordResetResetExpired');
        }
    }
}
