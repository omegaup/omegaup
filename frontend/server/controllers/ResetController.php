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
        self::validateCreateRequest($r);
        $email = $r['email'];
        $token = ApiUtils::GetRandomString();
        $reset_digest = hash('sha1', $token);
        $reset_sent_at = ApiUtils::GetStringTime();

        $user = UsersDAO::FindByEmail($email);
        $user->reset_digest = $reset_digest;
        $user->reset_sent_at = $reset_sent_at;
        UsersDAO::save($user);

        if (IS_TEST) {
            return ['status' => 'ok', 'token' => $token];
        }

        global $smarty;
        $subject = $smarty->getConfigVariable('wordsReset');
        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode($email) . '&reset_token=' . $token;
        $message = $smarty->getConfigVariable('wordsResetMessage');
        $body = str_replace('[link]', $link, $message);

        try {
            Email::sendEmail($email, $subject, $body);
        } catch (Exception $e) {
            self::$log->error('Failed to send reset password email ' . $e->getMessage());
            $user->reset_digest = null;
            $user->reset_sent_at = null;
            UsersDAO::save($user);
        }

        return [
            'status' => 'ok',
            'message' => $smarty->getConfigVariable('passwordResetRequestSuccess')
        ];
    }

    /**
     * Creates a reset operation, support team members can generate a valid
     * token and then they can send it to end user
     * @param Request $r
     * @return array
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    public static function apiGenerateToken(Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        if (!Authorization::isSupportTeamMember($r['current_user_id'])) {
            throw new ForbiddenAccessException();
        }
        self::validateCreateRequest($r);
        $email = $r['email'];
        $token = ApiUtils::GetRandomString();
        $reset_digest = hash('sha1', $token);
        $reset_sent_at = ApiUtils::GetStringTime();

        $user = UsersDAO::FindByEmail($email);
        $user->reset_digest = $reset_digest;
        $user->reset_sent_at = $reset_sent_at;
        UsersDAO::save($user);

        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode($email) . '&reset_token=' . $token;

        return ['status' => 'ok', 'token' => $token, 'link' => $link];
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
        $user->password = SecurityTools::hashString($r['password']);
        $user->reset_digest = null;
        $user->reset_sent_at = null;
        UsersDAO::save($user);

        global $smarty;
        return [
            'status' => 'ok',
            'message' =>  IS_TEST ? 'message' : $smarty->getConfigVariable('passwordResetResetSuccess')
        ];
    }

    private static function validateCreateRequest($r) {
        $user = UsersDAO::FindByEmail($r['email']);
        if (is_null($user)) {
            throw new InvalidParameterException('invalidUser');
        }

        if (!$user->verified) {
            throw new InvalidParameterException('unverifiedUser');
        }

        $seconds = Time::get() - strtotime($user->reset_sent_at);
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

        $seconds = Time::get() - strtotime($user->reset_sent_at);
        if ($seconds > PASSWORD_RESET_TIMEOUT) {
            throw new InvalidParameterException('passwordResetResetExpired');
        }
    }
}
