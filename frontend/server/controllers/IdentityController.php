<?php

/**
 *  IdentityController
 *
 * @author juan.pablo
 */
class IdentityController extends Controller {
    public static function convertFromUser(Users $user) {
        return IdentitiesDAO::save(new Identities([
            'identity_id' => $user->main_identity_id,
            'username' => $user->username,
            'password' => $user->password,
            'name' => $user->name,
            'user_id' => $user->user_id,
            'language_id' => $user->language_id,
            'country_id' => $user->country_id,
            'state_id' => $user->state_id,
            'school_id' => $user->school_id
        ]));
    }

    /**
     * Given a username or a email, returns the identity object
     *
     * @param type $userOrEmail
     * @return Identity
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function resolveIdentity($userOrEmail) {
        Validators::isStringNonEmpty($userOrEmail, 'usernameOrEmail');
        try {
            $identity = IdentitiesDAO::FindByEmail($userOrEmail);
            if (!is_null($identity)) {
                return $identity;
            }

            $identity = IdentitiesDAO::FindByUsername($userOrEmail);
            if (!is_null($identity)) {
                return $identity;
            }
            throw new NotFoundException('userOrMailNotFound');
        } catch (ApiException $apiException) {
            throw $apiException;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Get identity profile from cache
     * Requires $r["identity"] to be an actual Identity
     *
     * @param Request $r
     * @param array $response
     * @param Request $r
     * @return type
     */
    public static function getProfile(Request $r) {
        if (is_null($r['identity'])) {
            throw new InvalidParameterException('parameterNotFound', 'Identity');
        }

        $response = [];

        Cache::getFromCacheOrSet(
            Cache::USER_PROFILE,
            $r['identity']->username,
            $r,
            function (Request $r) {
                if (!is_null($r['user'])) {
                    return UserController::getProfileImpl($r['user'], $r['identity']);
                }
                return IdentityController::getProfileImpl($r['identity']);
            },
            $response
        );

        if (is_null($r['omit_rank']) || !$r['omit_rank']) {
            $response['userinfo']['rankinfo'] = UserController::getRankByProblemsSolved($r);
        } else {
            $response['userinfo']['rankinfo'] = [];
        }

        // Do not leak plain emails in case the request is for a profile other than
        // the logged identity's one. Admins can see emails
        if (Authorization::isSystemAdmin($r['current_identity_id'])
              || $r['identity']->identity_id == $r['current_identity_id']) {
            return $response;
        }

        // Mentors can see current coder of the month email.
        if (Authorization::canViewEmail($r['current_identity_id']) &&
              CoderOfTheMonthDAO::isLastCoderOfTheMonth($r['identity']->username)) {
            return $response;
        }
        unset($response['userinfo']['email']);
        return $response;
    }

    /**
     * Returns the profile of the identity given
     *
     * @param Identities $identity
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function getProfileImpl(Identities $identity) {
        $response = [];
        $response['userinfo'] = [];
        try {
            $extendedProfile = IdentitiesDAO::getExtendedProfileDataByPk($identity->identity_id);

            $response['userinfo'] = [
                'username' => $identity->username,
                'name' => $identity->name,
                'preferred_language' => null,
                'country' => $extendedProfile['country'],
                'country_id' => $identity->country_id,
                'state' => $extendedProfile['state'],
                'state_id' => $identity->state_id,
                'school' => $extendedProfile['school'],
                'school_id' => $identity->school_id,
                'is_private' => 0, # TODO: Review what value is setted
                'locale' => UserController::convertToSupportedLanguage($extendedProfile['locale']),
            ];
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        return $response;
    }
}
