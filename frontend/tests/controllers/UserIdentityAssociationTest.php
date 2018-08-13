<?php

/**
 * Testing synchronization betwen User and Identity
 *
 * @author juan.pablo
 */
class UserIdentityAssociationTest extends OmegaupTestCase {
    /**
     * Basic test for creating a single identity and associating it
     * with a registred user
     */
    public function testAssociateIdentityWithUser() {
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);

        $identityName = substr(Utils::CreateRandomString(), - 10);
        $password = Utils::CreateRandomString();
        // Call api using identity creator group member
        IdentityController::apiCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'name' => $identityName,
            'password' => $password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => Utils::CreateRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        $user = UserFactory::createUser();
        $login = self::login($user);

        $response = UserController::apiAssociateIdentity(new Request([
            'auth_token' => $login->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'password' => $password,
        ]));

        $this->assertEquals('ok', $response['status']);
    }

    /**
     * Test for creating a single identity and associating it
     * with a registered user, but wrong username
     */
    public function testAssociateIdentityWithWrongUser() {
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);

        $identityName = substr(Utils::CreateRandomString(), - 10);
        $password = Utils::CreateRandomString();
        // Call api using identity creator group member
        IdentityController::apiCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'name' => $identityName,
            'password' => $password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => Utils::CreateRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        $user = UserFactory::createUser();
        $login = self::login($user);

        try {
            $identityName = 'wrong_username';
            $response = UserController::apiAssociateIdentity(new Request([
                'auth_token' => $login->auth_token,
                'username' => "{$group['group']->alias}:{$identityName}",
                'password' => $password,
            ]));
            $this->fail('Identity should not be associated because identity username does not match');
        } catch (InvalidParameterException $e) {
            // Exception expected
        }
    }

    /**
     * Test for creating a single identity and associating it
     * with a registered user, but wrong password
     */
    public function testAssociateIdentityWithWrongPassword() {
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);

        $identityName = substr(Utils::CreateRandomString(), - 10);
        // Call api using identity creator group member
        IdentityController::apiCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'name' => $identityName,
            'password' => Utils::CreateRandomString(),
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => Utils::CreateRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        $user = UserFactory::createUser();
        $login = self::login($user);

        try {
            $response = UserController::apiAssociateIdentity(new Request([
                'auth_token' => $login->auth_token,
                'username' => "{$group['group']->alias}:{$identityName}",
                'password' => Utils::CreateRandomString(),
            ]));
            $this->fail('Identity should not be associated because identity password does not match');
        } catch (InvalidParameterException $e) {
            // Exception expected
        }
    }
}
