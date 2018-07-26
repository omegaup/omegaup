<?php

/**
 * UserFactory
 *
 * This class is a helper for creating users as needed in other places
 *
 * @author joemmanuel
 */
class UserParams implements ArrayAccess {
    public $params;

    public function __construct($params = null) {
        if (!is_object($params)) {
            $this->params = [];
            if (is_array($params)) {
                $this->params = array_merge([], $params);
            }
        } else {
            $this->params = clone $params;
        }
        $username = Utils::CreateRandomString();
        UserParams::validateParameter('username', $this->params, false, $username);
        UserParams::validateParameter('name', $this->params, false, $username);
        UserParams::validateParameter('password', $this->params, false, Utils::CreateRandomString());
        UserParams::validateParameter('email', $this->params, false, Utils::CreateRandomString() . '@mail.com');
        UserParams::validateParameter('is_private', $this->params, false, false);
        UserParams::validateParameter('verify', $this->params, false, true);
    }

    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    /**
     * Checks if array contains a key defined by $parameter
     * @param string $parameter
     * @param array $array
     * @param boolean $required
     * @param $default
     * @return boolean
     * @throws InvalidParameterException
     */
    private static function validateParameter($parameter, &$array, $required = true, $default = null) {
        if (!isset($array[$parameter])) {
            if ($required) {
                throw new InvalidParameterException('ParameterEmpty', $parameter);
            }
            $array[$parameter] = $default;
        }

        return true;
    }
}

class UserFactory {
   /**
    * Creates a native user in Omegaup and returns the DAO populated
    *
    * @param string $username optional
    * @param string $password optional
    * @param string $email optional
    * @return user (DAO)
    */
    public static function createUser($params = null) {
        if (!($params instanceof UserParams)) {
            $params = new UserParams($params);
        }

        // Populate a new Request to pass to the API
        UserController::$permissionKey = uniqid();
        $r = new Request([
            'username' => $params['username'],
            'name' => $params['name'],
            'password' => $params['password'],
            'email' => $params['email'],
            'is_private' => $params['is_private'],
            'permission_key' => UserController::$permissionKey
        ]);

        // Call the API
        $response = UserController::apiCreate($r);

        // If status is not OK
        if (strcasecmp($response['status'], 'ok') !== 0) {
            throw new Exception('UserFactory::createUser failed');
        }

        // Get user from db
        $user = UsersDAO::FindByUsername($params['username']);

        if ($params['verify']) {
            UserController::$redirectOnVerify = false;
            $user = self::verifyUser($user);
        } else {
            $user->verified = 0;
            UsersDAO::save($user);
        }

        // Password came hashed from DB. Set password in plaintext
        $user->password = $params['password'];

        return $user;
    }

    /**
     * Creates a native user in Omegaup and returns an array with the data used
     * to create the user.
     * @param $verify
     * @return array
     */
    public static function generateUser($verify = true) {
        $username = Utils::CreateRandomString();
        $password = Utils::CreateRandomString();
        $email = Utils::CreateRandomString().'@mail.com';
        self::createUser(new UserParams([
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'verify' => $verify
        ]));
        return [
            'username' => $username,
            'password' => $password,
            'email' => $email
        ];
    }

    /**
     * Creates a user using self::createUser with verify = false
     *
     * @return user (DAO)
     */
    public static function createUserWithoutVerify() {
        return self::createUser(new UserParams(['verify' => false]));
    }

    /**
     * Verifies a user and returns its DAO
     *
     * @param Users $user
     * @return type
     */
    public static function verifyUser(Users $user) {
        UserController::apiVerifyEmail(new Request([
            'id' => $user->verification_id
        ]));

        // Get user from db again to pick up verification changes
        return UsersDAO::FindByUsername($user->username);
    }

    /**
     * Creates a new user and elevates his priviledges
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User
     */
    public static function createAdminUser($params = null) {
        $user = self::createUser($params);

        self::addSystemRole($user, Authorization::ADMIN_ROLE);

        return $user;
    }

    /**
     * Creates a new identity with mentor role
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return Identity
     */
    public static function createMentorIdentity($params = null) {
        $user = self::createUser($params);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);

        self::addMentorRole($identity);

        return $user;
    }

    /**
     * Creates a new user with support role
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User
     */
    public static function createSupportUser($params = null) {
        $user = self::createUser($params);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);

        self::addSupportRole($identity);

        return $user;
    }

    /**
     * Creates a new user with contest organizer role
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User
     */
    public static function createGroupIdentityCreator($params = null) {
        $user = self::createUser($params);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);

        self::addGroupIdentityCreator($identity);

        return $user;
    }

    /**
     * Adds a system role to the user.
     *
     * @param Users $user
     * @param int $role_id
     */
    public static function addSystemRole(Users $user, $role_id) {
        UserRolesDAO::save(new UserRoles([
            'user_id' => $user->user_id,
            'role_id' => $role_id,
            'acl_id' => Authorization::SYSTEM_ACL,
        ]));
    }

    /**
     * Adds mentor role to the identity
     *
     * @param Identities $identity
     */
    public static function addMentorRole(Identities $identity) {
        $mentor_group = GroupsDAO::findByAlias(
            Authorization::MENTOR_GROUP_ALIAS
        );

        GroupsIdentitiesDao::save(new GroupsIdentities([
            'identity_id' => $identity->identity_id,
            'group_id' => $mentor_group->group_id,
        ]));
    }

    /**
     * Adds support role to the identity
     *
     * @param Identities $identity
     */
    public static function addSupportRole(Identities $identity) {
        $support_group = GroupsDAO::findByAlias(
            Authorization::SUPPORT_GROUP_ALIAS
        );

        GroupsIdentitiesDao::save(new GroupsIdentities([
            'identity_id' => $identity->identity_id,
            'group_id' => $support_group->group_id,
        ]));
    }

    /**
     * Adds group identity creator
     *
     * @param Identities $identity
     */
    public static function addGroupIdentityCreator(Identities $identity) {
        $groupIdentityCreator = GroupsDAO::findByAlias(
            Authorization::IDENTITY_CREATOR_GROUP_ALIAS
        );

        GroupsIdentitiesDao::save(new GroupsIdentities([
            'identity_id' => $identity->identity_id,
            'group_id' => $groupIdentityCreator->group_id,
        ]));
    }

    /**
     * creates privacy statement
     * @param $type
     * @return Boolean
     */
    public static function createPrivacyStatement($type = 'privacy_policy') {
        return PrivacyStatementsDAO::save(new PrivacyStatements([
            'git_object_id' => Utils::CreateRandomString(),
            'type' => $type,
        ]));
    }
}
