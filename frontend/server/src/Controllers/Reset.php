<?php

 namespace OmegaUp\Controllers;

class Reset extends \OmegaUp\Controllers\Controller {
    /**
     * Creates a reset operation, the first of two steps needed to reset a
     * password. The first step consist of sending an email to the user with
     * instructions to reset he's password, if and only if the email is valid.
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        self::validateCreateRequest($r);
        $email = strval($r['email']);
        $token = \OmegaUp\ApiUtils::getRandomString();
        $reset_digest = hash('sha1', $token);

        $user = \OmegaUp\DAO\Users::findByEmail($email);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }
        $user->reset_digest = $reset_digest;
        $user->reset_sent_at = \OmegaUp\Time::get();
        \OmegaUp\DAO\Users::update($user);

        if (IS_TEST) {
            return ['status' => 'ok', 'token' => $token];
        }

        $subject = \OmegaUp\Translations::getInstance()->get('wordsReset')
            ?: 'wordsReset';
        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode($email) . '&reset_token=' . $token;
        $message = \OmegaUp\Translations::getInstance()->get(
            'wordsResetMessage'
        )
            ?: 'wordsResetMessage';
        $body = str_replace('[link]', $link, $message);

        try {
            \OmegaUp\Email::sendEmail([$email], $subject, $body);
        } catch (\Exception $e) {
            self::$log->error(
                'Failed to send reset password email ' . $e->getMessage()
            );
            $user->reset_digest = null;
            $user->reset_sent_at = null;
            \OmegaUp\DAO\Users::update($user);
        }

        return [
            'status' => 'ok',
            'message' => \OmegaUp\Translations::getInstance()->get(
                'passwordResetRequestSuccess'
            )
        ];
    }

    /**
     * Creates a reset operation, support team members can generate a valid
     * token and then they can send it to end user
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiGenerateToken(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::validateCreateRequest($r);
        $email = $r['email'];

        $lastRequest = \OmegaUp\DAO\Identities::getExtraInformation($email);

        if (is_null($lastRequest)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }

        if (!$lastRequest['within_last_day']) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'userDoesNotHaveAnyPasswordChangeRequest'
            );
        }

        $token = \OmegaUp\ApiUtils::getRandomString();
        $reset_digest = hash('sha1', $token);

        $user = \OmegaUp\DAO\Users::findByEmail($email);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }
        $user->reset_digest = $reset_digest;
        $user->reset_sent_at = \OmegaUp\Time::get();
        \OmegaUp\DAO\Users::update($user);

        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode($email) . '&reset_token=' . $token;

        return ['status' => 'ok', 'token' => $token, 'link' => $link];
    }

    /**
     * Updates the password of a given user, this is the second and last step
     * in order to reset the password. This operation is done if and only if
     * the correct parameters are suplied.
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        self::validateUpdateRequest($r);
        $user = \OmegaUp\DAO\Users::findByEmail($r['email']);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }
        $user->password = \OmegaUp\SecurityTools::hashString($r['password']);
        $user->reset_digest = null;
        $user->reset_sent_at = null;
        if (is_null($user->main_identity_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }
        $identity->password = $user->password;
        try {
            \OmegaUp\DAO\DAO::transBegin();
            \OmegaUp\DAO\Users::update($user);
            \OmegaUp\DAO\Identities::update($identity);
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            self::$log->error('Failed to reset password', $e);
            throw $e;
        }

        return [
            'status' => 'ok',
            'message' =>  IS_TEST ? 'message' : \OmegaUp\Translations::getInstance()->get(
                'passwordResetResetSuccess'
            )
        ];
    }

    private static function validateCreateRequest($r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['email'], 'email');
        $user = \OmegaUp\DAO\Users::findByEmail($r['email']);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }

        if (!$user->verified) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'unverifiedUser'
            );
        }

        // Support doesn't need wait to resest passwords
        if (
            !is_null($r->identity) &&
            \OmegaUp\Authorization::isSupportTeamMember(
                $r->identity
            )
        ) {
            return;
        }

        if (is_null($user->reset_sent_at)) {
            return;
        }
        $seconds = \OmegaUp\Time::get() - $user->reset_sent_at;
        if ($seconds < PASSWORD_RESET_MIN_WAIT) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'passwordResetMinWait'
            );
        }
    }

    private static function validateUpdateRequest($r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['email'], 'email');
        $user = \OmegaUp\DAO\Users::findByEmail($r['email']);
        $reset_token = $r['reset_token'];
        $password = $r['password'];
        $password_confirmation = $r['password_confirmation'];
        if (
            is_null($user)
            || is_null($reset_token)
            || is_null($password)
            || is_null($password_confirmation)
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        if ($user->reset_digest !== hash('sha1', $reset_token)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidResetToken'
            );
        }

        if ($password !== $password_confirmation) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'passwordMismatch'
            );
        }

        \OmegaUp\SecurityTools::testStrongPassword($password);

        if (is_null($user->reset_sent_at)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'passwordResetResetExpired'
            );
        }
        $seconds = \OmegaUp\Time::get() - $user->reset_sent_at;
        if ($seconds > PASSWORD_RESET_TIMEOUT) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'passwordResetResetExpired'
            );
        }
    }
}
