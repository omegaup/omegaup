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

        // Save objects into DB
        try {
            DAO::transBegin();

            // Prepare DAOs
            $identity = self::createIdentity(
                $r['username'],
                $r['name'],
                $r['password'],
                $r['country_id'],
                $r['state_id'],
                $r['gender'],
                $r['school_name'],
                $r['group_alias']
            );

            // Save in DB
            self::saveIdentityGroup($identity, $r['group']->group_id);

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
        self::validateRequest($r);

        // Save objects into DB
        try {
            DAO::transBegin();

            foreach ($r['identities'] as $identity) {
                // Prepare DAOs
                $identity = self::createIdentity(
                    $identity['username'],
                    $identity['name'],
                    $identity['password'],
                    $identity['country_id'],
                    $identity['state_id'],
                    $identity['gender'],
                    $identity['school_name'],
                    $r['group_alias']
                );

                self::saveIdentityGroup($identity, $r['group']->group_id);
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
        if (!Authorization::isGroupIdentityCreator($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }
        GroupController::validateGroup($r);
        if (!is_array($r['identities']) && (!isset($r['username']) && !isset($r['name']) && !isset($r['group_alias']))) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
    }

    public static function validateIdentity($username, $name, $gender, $aliasGroup) {
        // Check group is present
        $usernameIdentity = explode(':', $username);
        if (count($usernameIdentity) != 2) {
            throw new InvalidParameterException('parameterInvalid', 'username');
        }
        $groupAlias = $usernameIdentity[0];
        if ($groupAlias != $aliasGroup) {
            throw new InvalidParameterException('parameterInvalid', 'group_alias');
        }
        // Validate request
        Validators::isValidUsernameIdentity($usernameIdentity[1], 'username');

        // Does identity already exists?
        try {
            $identity = IdentitiesDAO::FindByUsername($username);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!is_null($identity)) {
            throw new DuplicatedEntryInDatabaseException('usernameInUse');
        }

        if (!is_null($name)) {
            $name = trim($name);
            Validators::isStringNonEmpty($name, 'name', true);
            Validators::isStringOfMaxLength($name, 'name', 50);
        }

        if (!is_null($gender)) {
            $gender = trim($gender);
        }
        if (!empty($gender)) {
            Validators::isInEnum($gender, 'gender', UserController::ALLOWED_GENDER_OPTIONS, false);
        }
    }

    private static function createIdentity(
        $username,
        $name,
        $password,
        $countryId,
        $stateId,
        $gender,
        $school,
        $aliasGroup
    ) {
        self::validateIdentity($username, $name, $gender, $aliasGroup);

        $state = SchoolController::getStateIdFromCountryAndState($countryId, $stateId);
        $schoolId = SchoolController::createSchool(trim($school), $state);

        // Check password
        SecurityTools::testStrongPassword($password);
        $hashedPassword = SecurityTools::hashString($password);

        return new Identities([
            'username' => $username,
            'name' => $name,
            'password' => $hashedPassword,
            'country_id' => $countryId,
            'state_id' => $stateId,
            'gender' => $gender,
            'school_id' => $schoolId,
        ]);
    }

    /**
     * Save object Identities in DB, and add user into group.
     * This function is called inside a transaction.
     * @param Identities $identity
     * @param $groupId
     * @throws InvalidDatabaseOperationException
     */
    private static function saveIdentityGroup(Identities $identity, $groupId) {
        IdentitiesDAO::save($identity);

        GroupsIdentitiesDAO::save(new GroupsIdentities([
            'group_id' => $groupId,
            'identity_id' => $identity->identity_id
        ]));
    }
}
