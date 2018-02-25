<?php

/**
 * UserFactory
 *
 * This class is a helper for creating users as needed in other places
 *
 * @author joemmanuel
 */
class UserFactory {
   /**
    * Creates a native user in Omegaup and returns the DAO populated
    *
    * @param string $username optional
    * @param string $password optional
    * @param string $email optional
    * @return user (DAO)
    */
    public static function createUser($username = null, $password = null, $email = null, $verify = true, $is_private = false) {
        // If data is not provided, generate it randomly
        if (is_null($username)) {
            $username = Utils::CreateRandomString();
        }

        if (is_null($password)) {
            $password = Utils::CreateRandomString();
        }

        if (is_null($email)) {
            $email = Utils::CreateRandomString().'@mail.com';
        }

        // Populate a new Request to pass to the API
        UserController::$permissionKey = uniqid();
        $r = new Request([
            'username' => $username,
            'name' => $username,
            'password' => $password,
            'email' => $email,
            'is_private' => $is_private,
            'permission_key' => UserController::$permissionKey
        ]);

        // Call the API
        $response = UserController::apiCreate($r);

        // If status is not OK
        if (strcasecmp($response['status'], 'ok') !== 0) {
            throw new Exception('UserFactory::createUser failed');
        }

        // Get user from db
        $user = UsersDAO::FindByUsername($username);

        if ($verify) {
            UserController::$redirectOnVerify = false;
            $user = self::verifyUser($user);
        } else {
            $user->verified = 0;
            UsersDAO::save($user);
        }

        // Password came hashed from DB. Set password in plaintext
        $user->password = $password;

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
        self::createUser($username, $password, $email, $verify);
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
        return self::createUser(null, null, null, false);
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
    public static function createAdminUser($username = null, $password = null, $email = null) {
        $user = self::createUser($username, $password, $email);

        self::addSystemRole($user, Authorization::ADMIN_ROLE);

        return $user;
    }

    /**
     * Creates a new user with mentor role
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User
     */
    public static function createMentorUser($username = null, $password = null, $email = null) {
        $user = self::createUser($username, $password, $email);

        self::addMentorRole($user);

        return $user;
    }

    /**
     * Adds a system role to the user.
     *
     * @param Users $user
     * @param int $role_id
     */
    public static function addSystemRole(Users $user, $role_id) {
        $userRoles = new UserRoles([
            'user_id' => $user->user_id,
            'role_id' => $role_id,
            'acl_id' => Authorization::SYSTEM_ACL,
        ]);
        UserRolesDAO::save($userRoles);
    }

    /**
     * Adds mentor role to the user
     *
     * @param Users $user
     */
    public static function addMentorRole(Users $user) {
        $mentor_group = GroupsDAO::findByAlias(
            Authorization::MENTOR_GROUP_ALIAS
        );

        $groupUser = new GroupsUsers([
            'user_id' => $user->user_id,
            'group_id' => $mentor_group->group_id,
        ]);
        GroupsUsersDao::save($groupUser);
    }
}
