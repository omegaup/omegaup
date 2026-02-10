<?php

namespace OmegaUp\Test\Factories;

class UserParams {
    /**
     * @readonly
     * @var string
     */
    public $username;

    /**
     * @readonly
     * @var string
     */
    public $name;

    /**
     * @readonly
     * @var string
     */
    public $password;

    /**
     * @readonly
     * @var null|string
     */
    public $email;

    /**
     * @readonly
     * @var null|string
     */
    public $parentEmail;

    /**
     * @readonly
     * @var bool
     */
    public $isPrivate;

    /**
     * @readonly
     * @var bool
     */
    public $verify;

    /**
     * @readonly
     * @var string|null
     */
    public $preferredLanguage;

    /**
     * @readonly
     * @var int
     */
    public $birthDate;

    /**
     * @param array{username?: string, name?: string, password?: string, email?: string, parentEmail?: string, isPrivate?: bool, verify?: bool, preferredLanguage?: string, birthDate?: int} $params
     */
    public function __construct(array $params = []) {
        $emailBase = \OmegaUp\Test\Utils::CreateRandomString();
        $this->username = $params['username'] ?? \OmegaUp\Test\Utils::CreateRandomString();
        $this->name = $params['name'] ?? \OmegaUp\Test\Utils::CreateRandomString();
        $this->password = $params['password'] ?? \OmegaUp\Test\Utils::CreateRandomPassword();
        $this->email = $params['email'] ?? "{$emailBase}@mail.com";
        $this->parentEmail = $params['email'] ?? "parent_{$emailBase}@mail.com";
        $this->isPrivate = $params['isPrivate'] ?? false;
        $this->verify = $params['verify'] ?? true;
        $this->preferredLanguage = $params['preferredLanguage'] ?? null;
        $this->birthDate = $params['birthDate'] ?? 946684800; // 01-01-2000
    }
}

/**
 * This class is a helper for creating users as needed in other places
 */
class User {
   /**
    * Creates a native user in Omegaup and returns the DAO populated
    * @return array{user: \OmegaUp\DAO\VO\Users, identity: \OmegaUp\DAO\VO\Identities}
    */
    public static function createUser(
        ?UserParams $params = null
    ): array {
        if (is_null($params)) {
            $params = new UserParams();
        }

        $createUserParams = [
            'username' => $params->username,
            'name' => $params->name,
            'password' => $params->password,
            'email' => $params->email,
            'parent_email' => $params->parentEmail,
            'is_private' => strval($params->isPrivate),
            'birth_date' => $params->birthDate,
        ];

        if ($params->birthDate < strtotime('-13 year', \OmegaUp\Time::get())) {
            unset($createUserParams['parent_email']);
        } else {
            unset($createUserParams['email']);
        }

        // Call the API
        \OmegaUp\Controllers\User::createUser(
            new \OmegaUp\CreateUserParams($createUserParams),
            ignorePassword: false,
            forceVerification: true
        );

        // Get user from db
        $user = \OmegaUp\DAO\Users::FindByUsername($params->username);
        if (is_null($user) || is_null($user->main_identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $needsUpdate = false;
        if ($params->verify) {
            $user = self::verifyUser($user);
        } else {
            $user->verified = false;
            $needsUpdate = true;
        }

        if (!empty($params->preferredLanguage)) {
            $user->preferred_language = $params->preferredLanguage;
            $needsUpdate = true;
        }

        if ($needsUpdate) {
            \OmegaUp\DAO\Users::update($user);
        }

        // Password came hashed from DB. Set password in plaintext
        $identity->password = strval($params->password);

        return ['user' => $user, 'identity' => $identity];
    }

    /**
     * Creates a native user in Omegaup and returns an array with the data used
     * to create the user.
     *
     * @return array{username: string, password: string, email: string}
     */
    public static function generateUser(bool $verify = true): array {
        $username = \OmegaUp\Test\Utils::createRandomString();
        $password = \OmegaUp\Test\Utils::createRandomPassword();
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
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
     * @return array{user: \OmegaUp\DAO\VO\Users, identity: \OmegaUp\DAO\VO\Identities}
     */
    public static function createUserWithoutVerify(): array {
        return self::createUser(new UserParams(['verify' => false]));
    }

    /**
     * Verifies a user and returns its DAO
     */
    public static function verifyUser(
        \OmegaUp\DAO\VO\Users $user
    ): \OmegaUp\DAO\VO\Users {
        \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request([
            'id' => $user->verification_id
        ]));
        $user->verified = true;
        return $user;
    }

    /**
     * Creates a new user and elevates their privileges
     *
     * @return array{user: \OmegaUp\DAO\VO\Users, identity: \OmegaUp\DAO\VO\Identities}
     */
    public static function createAdminUser(?UserParams $params = null): array {
        ['user' => $user, 'identity' => $identity] = self::createUser($params);
        self::addSystemRole($user, \OmegaUp\Authorization::ADMIN_ROLE);

        return ['user' => $user, 'identity' => $identity];
    }

    /**
     * Creates a new identity with mentor role
     *
     * @return array{user: \OmegaUp\DAO\VO\Users, identity: \OmegaUp\DAO\VO\Identities}
     */
    public static function createMentorIdentity(
        ?UserParams $params = null
    ): array {
        ['user' => $user, 'identity' => $identity] = self::createUser($params);
        self::addMentorRole($identity);

        return ['user' => $user, 'identity' => $identity];
    }

    /**
     * Creates a new user with support role
     *
     * @return array{user: \OmegaUp\DAO\VO\Users, identity: \OmegaUp\DAO\VO\Identities}
     */
    public static function createSupportUser(
        ?UserParams $params = null
    ): array {
        ['user' => $user, 'identity' => $identity] = self::createUser($params);
        self::addSupportRole($identity);

        return ['user' => $user, 'identity' => $identity];
    }

    /**
     * Creates a new user with contest organizer role
     *
     * @return array{user: \OmegaUp\DAO\VO\Users, identity: \OmegaUp\DAO\VO\Identities}
     */
    public static function createGroupIdentityCreator(
        ?UserParams $params = null
    ): array {
        ['user' => $user, 'identity' => $identity] = self::createUser($params);
        self::addGroupIdentityCreator($identity);

        return ['user' => $user, 'identity' => $identity];
    }

    /**
     * Adds a system role to the user.
     */
    public static function addSystemRole(
        \OmegaUp\DAO\VO\Users $user,
        int $roleId
    ): void {
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $user->user_id,
            'role_id' => $roleId,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));
    }

    /**
     * Adds mentor role to the identity
     */
    public static function addMentorRole(\OmegaUp\DAO\VO\Identities $identity): void {
        $mentorGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::MENTOR_GROUP_ALIAS
        );
        if (is_null($mentorGroup) || is_null($mentorGroup->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'identity_id' => $identity->identity_id,
            'group_id' => $mentorGroup->group_id,
        ]));
    }

    public static function addSupportRole(\OmegaUp\DAO\VO\Identities $identity): void {
        $supportGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::SUPPORT_GROUP_ALIAS
        );
        if (is_null($supportGroup) || is_null($supportGroup->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'identity_id' => $identity->identity_id,
            'group_id' => $supportGroup->group_id,
        ]));
    }

    public static function addGroupIdentityCreator(\OmegaUp\DAO\VO\Identities $identity): void {
        $groupIdentityCreator = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::IDENTITY_CREATOR_GROUP_ALIAS
        );
        if (
            is_null($groupIdentityCreator) ||
            is_null($groupIdentityCreator->group_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        $role = \OmegaUp\DAO\Roles::getByName('GroupIdentityCreator');
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $identity->user_id,
            'role_id' => $role->role_id,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));
    }

    public static function createPrivacyStatement(string $type = 'privacy_policy'): int {
        return \OmegaUp\DAO\PrivacyStatements::create(new \OmegaUp\DAO\VO\PrivacyStatements([
            'git_object_id' => \OmegaUp\Test\Utils::createRandomString(),
            'type' => $type,
        ]));
    }
}
