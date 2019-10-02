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
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateParameter($parameter, &$array, $required = true, $default = null) {
        if (!isset($array[$parameter])) {
            if ($required) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('ParameterEmpty', $parameter);
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
    * @return \OmegaUp\DAO\VO\Identities
    * @psalm-return \OmegaUp\DAO\VO\Identities
    */
    public static function createUser($params = null) : \OmegaUp\DAO\VO\Identities {
        if (!($params instanceof UserParams)) {
            $params = new UserParams($params);
        }

        // Populate a new Request to pass to the API
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => $params['username'],
            'name' => $params['name'],
            'password' => $params['password'],
            'email' => $params['email'],
            'is_private' => $params['is_private'],
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call the API
        $response = \OmegaUp\Controllers\User::apiCreate($r);

        // If status is not OK
        if (strcasecmp($response['status'], 'ok') !== 0) {
            throw new Exception('UserFactory::createUser failed');
        }

        // Get user from db
        $user = \OmegaUp\DAO\Users::FindByUsername($params['username']);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        if (is_null($user) || is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
        }

        if ($params['verify']) {
            \OmegaUp\Controllers\User::$redirectOnVerify = false;
            $user = self::verifyUser($user);
        } else {
            $user->verified = false;
            \OmegaUp\DAO\Users::update($user);
        }

        // Password came hashed from DB. Set password in plaintext
        $identity->password = strval($params['password']);

        return $identity;
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
     */
    public static function verifyUser(
        \OmegaUp\DAO\VO\Users $user
    ) : \OmegaUp\DAO\VO\Users {
        \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request([
            'id' => $user->verification_id
        ]));
        $user->verified = true;
        return $user;
    }

    /**
     * Creates a new user and elevates his priviledges
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return \OmegaUp\DAO\VO\Identities
     * @psalm-return \OmegaUp\DAO\VO\Identities
     */
    public static function createAdminUser($params = null) : \OmegaUp\DAO\VO\Identities {
        $identity = self::createUser($params);

        self::addSystemRole($identity, \OmegaUp\Authorization::ADMIN_ROLE);

        return $identity;
    }

    /**
     * Creates a new identity with mentor role
     *
     * @param UserParams|null $params
     * @return \OmegaUp\DAO\VO\Identities
     * @psalm-return \OmegaUp\DAO\VO\Identities
     */
    public static function createMentorIdentity(UserParams $params = null) : \OmegaUp\DAO\VO\Identities {
        $identity = self::createUser($params);

        self::addMentorRole($identity);

        return $identity;
    }

    /**
     * Creates a new user with support role
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User
     */
    public static function createSupportUser($params = null) : \OmegaUp\DAO\VO\Identities {
        $identity = self::createUser($params);

        self::addSupportRole($identity);

        return $identity;
    }

    /**
     * Creates a new user with contest organizer role
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User
     */
    public static function createGroupIdentityCreator($params = null) : \OmegaUp\DAO\VO\Identities {
        $identity = self::createUser($params);

        self::addGroupIdentityCreator($identity);

        return $identity;
    }

    /**
     * Adds a system role to the user.
     *
     * @param \OmegaUp\DAO\VO\Users $user
     * @param int $role_id
     */
    public static function addSystemRole(\OmegaUp\DAO\VO\Identities $identity, $role_id) {
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $identity->user_id,
            'role_id' => $role_id,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));
    }

    /**
     * Adds mentor role to the identity
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     */
    public static function addMentorRole(\OmegaUp\DAO\VO\Identities $identity) {
        $mentor_group = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::MENTOR_GROUP_ALIAS
        );

        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'identity_id' => $identity->identity_id,
            'group_id' => $mentor_group->group_id,
        ]));
    }

    /**
     * Adds support role to the identity
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     */
    public static function addSupportRole(\OmegaUp\DAO\VO\Identities $identity) {
        $support_group = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::SUPPORT_GROUP_ALIAS
        );

        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'identity_id' => $identity->identity_id,
            'group_id' => $support_group->group_id,
        ]));
    }

    /**
     * Adds group identity creator
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     */
    public static function addGroupIdentityCreator(\OmegaUp\DAO\VO\Identities $identity) {
        $groupIdentityCreator = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::IDENTITY_CREATOR_GROUP_ALIAS
        );

        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
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
        return \OmegaUp\DAO\PrivacyStatements::create(new \OmegaUp\DAO\VO\PrivacyStatements([
            'git_object_id' => Utils::CreateRandomString(),
            'type' => $type,
        ]));
    }
}
