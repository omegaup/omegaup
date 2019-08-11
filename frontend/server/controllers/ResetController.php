<?php

require_once 'libs/Translations.php';

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
        UsersDAO::update($user);

        if (IS_TEST) {
            return ['status' => 'ok', 'token' => $token];
        }

        $subject = Translations::getInstance()->get('wordsReset');
        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode($email) . '&reset_token=' . $token;
        $message = Translations::getInstance()->get('wordsResetMessage');
        $body = str_replace('[link]', $link, $message);

        try {
            include_once 'libs/Email.php';
            Email::sendEmail($email, $subject, $body);
        } catch (Exception $e) {
            self::$log->error('Failed to send reset password email ' . $e->getMessage());
            $user->reset_digest = null;
            $user->reset_sent_at = null;
            UsersDAO::update($user);
        }

        return [
            'status' => 'ok',
            'message' => Translations::getInstance()->get('passwordResetRequestSuccess')
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
        self::authenticateRequest($r);

        if (!Authorization::isSupportTeamMember($r->identity)) {
            throw new ForbiddenAccessException();
        }

        self::validateCreateRequest($r);
        $email = $r['email'];

        $lastRequest = IdentitiesDAO::getExtraInformation($email);

        if (is_null($lastRequest)) {
            throw new InvalidParameterException('invalidUser');
        }

        if (!$lastRequest['within_last_day']) {
            throw new InvalidParameterException('userDoesNotHaveAnyPasswordChangeRequest');
        }

        $token = ApiUtils::GetRandomString();
        $reset_digest = hash('sha1', $token);
        $reset_sent_at = ApiUtils::GetStringTime();

        $user = UsersDAO::FindByEmail($email);
        $user->reset_digest = $reset_digest;
        $user->reset_sent_at = $reset_sent_at;
        UsersDAO::update($user);

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
        self::validateUpdateRequest($r);
        $user = UsersDAO::FindByEmail($r['email']);
        if (is_null($user)) {
            throw new InvalidParameterException('invalidUser');
        }
        $user->password = SecurityTools::hashString($r['password']);
        $user->reset_digest = null;
        $user->reset_sent_at = null;
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
        if (is_null($identity)) {
            throw new InvalidParameterException('invalidUser');
        }
        $identity->password = $user->password;
        try {
            DAO::transBegin();
            UsersDAO::update($user);
            IdentitiesDAO::update($identity);
            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            self::$log->error('Failed to reset password', $e);
            throw $e;
        }

        return [
            'status' => 'ok',
            'message' =>  IS_TEST ? 'message' : Translations::getInstance()->get('passwordResetResetSuccess')
        ];
    }

    private static function validateCreateRequest($r) {
        $user = UsersDAO::FindByEmail($r['email']);
        if (is_null($user)) {
            throw new InvalidParameterException('invalidUser');
        }
        $user->toUnixTime();

        if (!$user->verified) {
            throw new InvalidParameterException('unverifiedUser');
        }

        // Support doesn't need wait to resest passwords
        if (!is_null($r->identity) && Authorization::isSupportTeamMember($r->identity)) {
            return;
        }

        $seconds = Time::get() - $user->reset_sent_at;
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
        $user->toUnixtime();

        if ($user->reset_digest !== hash('sha1', $reset_token)) {
            throw new InvalidParameterException('invalidResetToken');
        }

        if ($password !== $password_confirmation) {
            throw new InvalidParameterException('passwordMismatch');
        }

        SecurityTools::testStrongPassword($password);

        $seconds = Time::get() - $user->reset_sent_at;
        if ($seconds > PASSWORD_RESET_TIMEOUT) {
            throw new InvalidParameterException('passwordResetResetExpired');
        }
    }
}
