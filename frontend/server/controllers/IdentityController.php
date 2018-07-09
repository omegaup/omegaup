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
            'school_id' => $user->school_id,
            'gender' => $user->gender,
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
     * Entry point for Create an Identity API
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(Request $r) {
        self::validateRequest($r);

        // Prepare DAOs
        $identity = self::createIdentity($r);

        // Save in DB
        self::saveIdentityGroup($r, $identity);

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
        self::validateRequest($r);

        // Save objects into DB
        try {
            DAO::transBegin();

            foreach ($r['identities'] as $identity) {
                $identity_group = new Request($identity);
                $identity_group['group_alias'] = $r['group_alias'];
                $identity_group['auth_token'] = $r['auth_token'];

                // Prepare DAOs
                $identity = self::createIdentity($identity_group);

                self::saveIdentityGroup($identity_group, $identity);
            }

            DAO::transEnd();
        } catch (DuplicatedEntryInDatabaseException $e) {
            DAO::transRollback();
            throw new DuplicatedEntryInDatabaseException('usernameInUse');
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * @param Request $r
     * @throws InvalidParameterException
     */
    private static function validateRequest(Request $r) {
        self::authenticateRequest($r);
        if (!Authorization::isOrganizer($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }
        GroupController::validateGroup($r);
        if (!is_array($r['identities'])) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
    }

    public static function validateIdentity(Request $r) {
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

        if (!is_null($r['name'])) {
            $r['name'] = trim($r['name']);
            Validators::isStringNonEmpty($r['name'], 'name', true);
            Validators::isStringOfMaxLength($r['name'], 'name', 50);
        }

        if (!is_null($r['gender'])) {
            $r['gender'] = trim($r['gender']);
        }
        if (!empty($r['gender'])) {
            Validators::isInEnum($r['gender'], 'gender', UserController::ALLOWED_GENDER_OPTIONS, false);
        }

        $state = SchoolController::isValidCountryAndState($r, true);

        $r['school_id'] = SchoolController::createSchool($state, trim($r['school_name']));
    }

    private static function createIdentity(Request $r) {
        self::validateIdentity($r);

        // Check group is present
        $group_alias = explode(':', $r['username'])[0];
        if ($group_alias != $r['group_alias']) {
            throw new InvalidParameterException('parameterInvalid', 'username');
        }

        // Check password
        SecurityTools::testStrongPassword($r['password']);
        $hashedPassword = SecurityTools::hashString($r['password']);

        return new Identities([
            'username' => $r['username'],
            'name' => $r['name'],
            'password' => $hashedPassword,
            'country_id' => $r['country_id'],
            'state_id' => $r['state_id'],
            'gender' => $r['gender'],
            'school_id' => $r['school_id'],
        ]);
    }

    /**
     * Save object Identities in DB, and add user into group.
     * This function is called inside a transaction.
     * @param Request $r
     * @param Identties $identity
     * @throws InvalidDatabaseOperationException
     */
    private static function saveIdentityGroup(Request $r, Identities $identity) {
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
    }
}
