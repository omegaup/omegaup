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
    public static function createUser($username = null, $password = null, $email = null, $verify = true) {
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
        $r = new Request(array(
            'username' => $username,
            'name' => $username,
            'password' => $password,
            'email' => $email,
            'permission_key' => UserController::$permissionKey
        ));

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
        return array(
            'username' => $username,
            'password' => $password,
            'email' => $email
        );
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
        UserController::apiVerifyEmail(new Request(array(
            'id' => $user->verification_id
        )));

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

        $userRoles = new UserRoles(array(
            'user_id' => $user->user_id,
            'role_id' => Authorization::ADMIN_ROLE,
            'acl_id' => Authorization::SYSTEM_ACL,
        ));
        UserRolesDAO::save($userRoles);

        return $user;
    }

    public static function createInterviewerUser() {
        $user = self::createUser(null, null, null, null, true);

        $ur = new UserRoles(array(
            'user_id' => $user->user_id,
            'role_id' => Authorization::INTERVIEWER_ROLE,
            'acl_id' => Authorization::SYSTEM_ACL,
        ));
        UserRolesDAO::save($ur);

        return $user;
    }
}
