<?php

/**
 * Testing synchronization between User and Identity
 *
 * @author juan.pablo
 */
class UserIdentityAssociationTest extends OmegaupTestCase {
    private function assertUsernameInArray($username, array $identities) {
        foreach ($identities as $identity) {
            if ($identity['username'] == $username) {
                $this->assertTrue(true);
                return;
            }
        }
        $this->assertTrue(false, 'Username is not associated with user');
    }

    /**
     * Basic test for creating a single identity and associating it
     * with a registred user
     */
    public function testAssociateIdentityWithUser() {
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(Utils::CreateRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = Utils::CreateRandomString();
        // Call api using identity creator group member
        IdentityController::apiCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
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

        $associatedIdentities = UserController::apiListAssociatedIdentities(new Request([
            'auth_token' => $login->auth_token,
        ]));

        // User has one default associated identity when joins omegaUp
        $this->assertEquals(1, count($associatedIdentities['identities']));
        $this->assertEquals(
            $user->username,
            $associatedIdentities['identities'][0]['username']
        );

        $response = UserController::apiAssociateIdentity(new Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'password' => $password,
        ]));

        $associatedIdentities = UserController::apiListAssociatedIdentities(new Request([
            'auth_token' => $login->auth_token,
        ]));

        // User now has two associated identities
        $this->assertEquals(2, count($associatedIdentities['identities']));
        $this->assertUsernameInArray(
            $username,
            $associatedIdentities['identities']
        );
        $this->assertUsernameInArray(
            $user->username,
            $associatedIdentities['identities']
        );

        $identity = IdentityController::resolveIdentity($username);
        $identity->password = $password;
        $identityLogin = self::login($identity);

        $details = UserController::apiProfile(new Request([
            'auth_token' => $login->auth_token,
        ]));

        // apiProfile must show associated user's info
        $this->assertEquals($details['userinfo']['username'], $user->username);
    }

    /**
     * Test for creating a single identity and associating it
     * with a registered user, but wrong username
     */
    public function testAssociateIdentityWithWrongUser() {
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(Utils::CreateRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = Utils::CreateRandomString();
        // Call api using identity creator group member
        IdentityController::apiCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
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
            $username = "{$group['group']->alias}:{$identityName}";
            $response = UserController::apiAssociateIdentity(new Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'password' => $password,
            ]));
            $this->fail('Identity should not be associated because identity ' .
                        'username does not match');
        } catch (InvalidParameterException $e) {
            // Exception expected
            $this->assertEquals($e->getMessage(), 'parameterInvalid');
        }
    }

    /**
     * Test for creating a single identity and associating it
     * with a registered user, but wrong password
     */
    public function testAssociateIdentityWithWrongPassword() {
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(Utils::CreateRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        // Call api using identity creator group member
        IdentityController::apiCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
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
                'username' => $username,
                'password' => Utils::CreateRandomString(),
            ]));
            $this->fail('Identity should not be associated because identity ' .
                        'password does not match');
        } catch (InvalidParameterException $e) {
            // Exception expected
            $this->assertEquals($e->getMessage(), 'parameterInvalid');
        }
    }

    /**
     * Trying to associate two identities of the same group to a certain user
     * account
     */
    public function testAssociateDuplicatedIdentitiesOfAGroup() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );
        $password = Utils::CreateRandomString();

        // Call api using identity creator group member
        IdentityController::apiBulkCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => IdentityFactory::getCsvData(
                'identities.csv',
                $group['group']->alias,
                $password
            ),
            'group_alias' => $group['group']->alias,
        ]));

        // Getting all identity members associated to the group
        $membersResponse = GroupController::apiMembers(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Trying to associate first identity to the logged user
        $response = UserController::apiAssociateIdentity(new Request([
            'auth_token' => $login->auth_token,
            'username' => $membersResponse['identities'][0]['username'],
            'password' => $password,
        ]));

        // Trying to associate second identity to the logged user
        try {
            $response = UserController::apiAssociateIdentity(new Request([
                'auth_token' => $login->auth_token,
                'username' => $membersResponse['identities'][1]['username'],
                'password' => $password,
            ]));
            $this->fail('Identity should not be associated because user has ' .
                        'already another identity of the same group');
        } catch (DuplicatedEntryInDatabaseException $e) {
            // Exception expected
            $this->assertEquals($e->getMessage(), 'identityAlreadyAssociated');
        }
    }
}
