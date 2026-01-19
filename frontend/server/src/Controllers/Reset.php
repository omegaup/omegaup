<?php

 namespace OmegaUp\Controllers;

class Reset extends \OmegaUp\Controllers\Controller {
    /**
     * Creates a reset operation, the first of two steps needed to reset a
     * password. The first step consist of sending an email to the user with
     * instructions to reset he's password, if and only if the email is valid.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{message?: string, token?: string}
     *
     * @omegaup-request-param string $email
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        self::validateCreateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['email'],
            'email'
        );
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
        $user->reset_sent_at = new \OmegaUp\Timestamp(\OmegaUp\Time::get());
        \OmegaUp\DAO\Users::update($user);

        /** @psalm-suppress TypeDoesNotContainType IS_TEST may be defined as true in tests. */
        if (IS_TEST) {
            return ['token' => $token];
        }

        $subject = \OmegaUp\Translations::getInstance()->get('wordsReset');
        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode($email) . '&reset_token=' . $token;
        $message = \OmegaUp\Translations::getInstance()->get(
            'wordsResetMessage'
        );
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
            'message' => \OmegaUp\Translations::getInstance()->get(
                'passwordResetRequestSuccess'
            )
        ];
    }

    /**
     * Creates a reset operation, support team members can generate a valid
     * token and then they can send it to end user
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{link: string, token: string}
     *
     * @omegaup-request-param string $email
     */
    public static function apiGenerateToken(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::validateCreateRequest($r);

        $email = $r->ensureString('email');

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
        $user->reset_sent_at = new \OmegaUp\Timestamp(\OmegaUp\Time::get());
        \OmegaUp\DAO\Users::update($user);

        $link = OMEGAUP_URL . '/login/password/reset/?';
        $link .= 'email=' . rawurlencode(
            $email
        ) . '&reset_token=' . rawurlencode(
            $token
        );

        return [
            'token' => $token,
            'link' => $link,
        ];
    }

    /**
     * Updates the password of a given user, this is the second and last step
     * in order to reset the password. This operation is done if and only if
     * the correct parameters are supplied.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{message: string}
     *
     * @omegaup-request-param string $email
     * @omegaup-request-param string $password
     * @omegaup-request-param string $password_confirmation
     * @omegaup-request-param string $reset_token
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['email'],
            'email'
        );
        $user = \OmegaUp\DAO\Users::findByEmail($r['email']);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['reset_token'],
            'reset_token'
        );
        $resetToken = $r['reset_token'];
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['password'],
            'password'
        );
        $password = $r['password'];
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['password_confirmation'],
            'password_confirmation'
        );
        $passwordConfirmation = $r['password_confirmation'];

        if ($user->reset_digest !== hash('sha1', $resetToken)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidResetToken'
            );
        }

        if ($password !== $passwordConfirmation) {
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
        $seconds = \OmegaUp\Time::get() - $user->reset_sent_at->time;
        if ($seconds > PASSWORD_RESET_TIMEOUT) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'passwordResetResetExpired'
            );
        }

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
        $identity->password = \OmegaUp\SecurityTools::hashString(
            $r['password']
        );

        \OmegaUp\DAO\Identities::update($identity);

        /** @psalm-suppress TypeDoesNotContainType IS_TEST may be defined as true in tests. */
        return [
            'message' => IS_TEST ?
                'message' :
                    \OmegaUp\Translations::getInstance()->get(
                        'passwordResetResetSuccess'
                    )
        ];
    }

    /**
     * @omegaup-request-param string $email
     */
    private static function validateCreateRequest(\OmegaUp\Request $r): void {
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

        // Support doesn't need wait to reset passwords
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
        $seconds = \OmegaUp\Time::get() - $user->reset_sent_at->time;
        if ($seconds < PASSWORD_RESET_MIN_WAIT) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'passwordResetMinWait'
            );
        }
    }
}
