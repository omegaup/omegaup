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
     * Entry point for Create a User API
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(Request $r) {
        // Validate request
        Validators::isValidUsername($r['username'], 'username');

        // Does identity already exists?
        try {
            $identity = IdentitiesDAO::FindByUsername($r['username']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!is_null($identity)) {
            throw new DuplicatedEntryInDatabaseException('usernameInUse');
        }

        // Check password
        SecurityTools::testStrongPassword($r['password']);
        $hashedPassword = SecurityTools::hashString($r['password']);

        if (!is_null($r['name'])) {
            $r['name'] = trim($r['name']);
            Validators::isStringNonEmpty($r['name'], 'name', true);
            Validators::isStringOfMaxLength($r['name'], 'name', 50);
        }

        $state = null;
        if (!is_null($r['country_id']) || !is_null($r['state_id'])) {
            // Both state and country must be specified together.
            $r['country_id'] = trim($r['country_id']);
            $r['state_id'] = trim($r['state_id']);
            Validators::isStringNonEmpty($r['country_id'], 'country_id', false);
            Validators::isStringNonEmpty($r['state_id'], 'state_id', false);

            try {
                $state = StatesDAO::getByPK($r['country_id'], $r['state_id']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($state)) {
                throw new InvalidParameterException('parameterInvalid', 'state_id');
            }
        }
        $r['gender'] = trim($r['gender']);
        if (!empty($r['gender'])) {
            Validators::isInEnum($r['gender'], 'gender', UserController::ALLOWED_GENDER_OPTIONS, false);
        }
        $r['school_name'] = trim($r['school_name']);
        if (empty($r['school_name'])) {
            $r['school_id'] = null;
        } else {
            try {
                $response = SchoolController::apiCreate(new Request([
                    'name' => $r['school_name'],
                    'country_id' => $state != null ? $state->country_id : null,
                    'state_id' => $state != null ? $state->state_id : null,
                    'auth_token' => $r['auth_token'],
                ]));
                $r['school_id'] = $response['school_id'];
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        // Prepare DAOs
        $user_data = [
            'username' => $r['username'],
            'name' => $r['name'],
            'password' => $hashedPassword,
            'country_id' => $r['country_id'],
            'state_id' => $r['state_id'],
            'gender' => $r['gender'],
            'school_id' => $r['school_id']
        ];

        if (isset($r['name'])) {
            $user_data['name'] = $r['name'];
        }

        $identity = new Identities($user_data);

        // Save objects into DB
        try {
            DAO::transBegin();

            IdentitiesDAO::save($identity);
            $r['usernameOrEmail'] = $r['username'];

            if (!empty($r['group_alias'])) {
                GroupController::apiAddUser($r);
            }

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
            'username' => $identity->username,
        ];
    }

    /**
     * Entry point for Create bulk Identities API
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiBulkCreate(Request $r) {
        self::authenticateRequest($r);
        if (!Authorization::isOrganizer($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }
        GroupController::validateGroup(new Request(['group_alias' => $r['group_alias']]));
        if (!is_array($r['identities'])) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
        // Save objects into DB
        try {
            DAO::transBegin();

            foreach ($r['identities'] as $identity) {
                $group_alias = explode(':', $identity['username'])[0];
                if ($group_alias != $r['group_alias']) {
                    throw new InvalidParameterException('parameterInvalid', 'username');
                }
                $identity['group_alias'] = $r['group_alias'];
                self::apiCreate(new Request($identity));
            }

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok'
        ];
    }
}
