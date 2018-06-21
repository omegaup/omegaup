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
                return IdentityController::getProfileImpl($r['user'], $r['identity']);
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
     * Returns the profile of the user or identity given
     *
     * @param Users $user
     * @param Identities $identity
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function getProfileImpl(Users $user = null, Identities $identity = null) {
        $response = [];
        $response['userinfo'] = [];
        $response['problems'] = [];

        if (is_null($user)) {
            try {
                $identity_db = IdentitiesDAO::getExtendedProfileDataByPk($identity->identity_id);

                $response['userinfo']['username'] = $identity->username;
                $response['userinfo']['name'] = $identity->name;
                $response['userinfo']['preferred_language'] = null;
                $response['userinfo']['country'] = $identity_db['country'];
                $response['userinfo']['country_id'] = $identity->country_id;
                $response['userinfo']['state'] = $identity_db['state'];
                $response['userinfo']['state_id'] = $identity->state_id;
                $response['userinfo']['school'] = $identity_db['school'];
                $response['userinfo']['school_id'] = $identity->school_id;
                $response['userinfo']['is_private'] = 0; # TODO: Review what value is setted
                $response['userinfo']['locale'] = UserController::convertToSupportedLanguage($identity_db['locale']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
            return $response;
        }

        $response['userinfo']['username'] = $user->username;
        $response['userinfo']['name'] = $user->name;
        $response['userinfo']['birth_date'] = is_null($user->birth_date) ? null : strtotime($user->birth_date);
        $response['userinfo']['gender'] = $user->gender;
        $response['userinfo']['graduation_date'] = is_null($user->graduation_date) ? null : strtotime($user->graduation_date);
        $response['userinfo']['scholar_degree'] = $user->scholar_degree;
        $response['userinfo']['preferred_language'] = $user->preferred_language;
        $response['userinfo']['is_private'] = $user->is_private;
        $response['userinfo']['verified'] = $user->verified == '1';
        $response['userinfo']['hide_problem_tags'] = is_null($user->hide_problem_tags) ? null : $user->hide_problem_tags;

        if (!is_null($user->language_id)) {
            $query = LanguagesDAO::getByPK($user->language_id);
            if (!is_null($query)) {
                $response['userinfo']['locale'] =
                    UserController::convertToSupportedLanguage($query->name);
            }
        }

        try {
            $user_db = UsersDAO::getExtendedProfileDataByPk($user->user_id);

            $response['userinfo']['email'] = $user_db['email'];
            $response['userinfo']['country'] = $user_db['country'];
            $response['userinfo']['country_id'] = $user->country_id;
            $response['userinfo']['state'] = $user_db['state'];
            $response['userinfo']['state_id'] = $user->state_id;
            $response['userinfo']['school'] = $user_db['school'];
            $response['userinfo']['school_id'] = $user->school_id;

            if (!is_null($user->language_id)) {
                $response['userinfo']['locale'] = UserController::convertToSupportedLanguage($user_db['locale']);
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response['userinfo']['gravatar_92'] = 'https://secure.gravatar.com/avatar/' . md5($response['userinfo']['email']) . '?s=92';

        return $response;
    }
}
