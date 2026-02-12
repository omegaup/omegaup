<?php
/**
 * Testing synchronization between User and Identity
 */
class UserIdentityAssociationTest extends \OmegaUp\Test\ControllerTestCase {
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
     * with a registered user
     */
    public function testAssociateIdentityWithUser() {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $associatedIdentities = \OmegaUp\Controllers\User::apiListAssociatedIdentities(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // User has one default associated identity when joins omegaUp
        $this->assertSame(1, count($associatedIdentities['identities']));
        $this->assertSame(
            $identity->username,
            $associatedIdentities['identities'][0]['username']
        );

        \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'password' => $password,
        ]));

        $associatedIdentities = \OmegaUp\Controllers\User::apiListAssociatedIdentities(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // User now has two associated identities
        $this->assertSame(2, count($associatedIdentities['identities']));
        $this->assertUsernameInArray(
            $username,
            $associatedIdentities['identities']
        );
        $this->assertUsernameInArray(
            $identity->username,
            $associatedIdentities['identities']
        );

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $password;
        $identityLogin = self::login($identity);

        $details = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $identityLogin->auth_token,
            ])
        );

        // apiProfile must show associated user's info
        $this->assertSame(
            $details['username'],
            $identity->username
        );
    }

    /**
     * Test for creating a single identity and associating it
     * with a registered user, but wrong username
     */
    public function testAssociateIdentityWithWrongUser() {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            $identityName = 'wrong_username';
            $username = "{$group['group']->alias}:{$identityName}";
            \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'password' => $password,
            ]));
            $this->fail('Identity should not be associated because identity ' .
                        'username does not match');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Exception expected
            $this->assertSame($e->getMessage(), 'parameterInvalid');
        }
    }

    /**
     * Test for creating a single identity and associating it with a user, but
     * used by another registered user
     */
    public function testAssociateUsedIdentityWithUser() {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'password' => $password,
        ]));

        // Create a new user to associate
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'password' => $password,
            ]));
            $this->fail('Identity should not be associated because identity ' .
                        'has been already used');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            // Exception expected
            $this->assertSame($e->getMessage(), 'identityAlreadyInUse');
        }
    }

    /**
     * Test for creating a single identity and associating it
     * with a registered user, but wrong password
     */
    public function testAssociateIdentityWithWrongPassword() {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'password' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail('Identity should not be associated because identity ' .
                        'password does not match');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Exception expected
            $this->assertSame($e->getMessage(), 'parameterInvalid');
        }
    }

    /**
     * Trying to associate two identities of the same group to a certain user
     * account
     */
    public function testAssociateDuplicatedIdentitiesOfAGroup() {
        // Identity creator group member will upload csv file
        ['identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );
        $password = \OmegaUp\Test\Utils::createRandomString();

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                $group['group']->alias,
                $password
            ),
            'group_alias' => $group['group']->alias,
        ]));

        // Getting all identity members associated to the group
        $membersResponse = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Trying to associate first identity to the logged user
        \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $membersResponse['identities'][0]['username'],
            'password' => $password,
        ]));

        // Trying to associate second identity to the logged user
        try {
            \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $membersResponse['identities'][1]['username'],
                'password' => $password,
            ]));
            $this->fail('Identity should not be associated because user has ' .
                        'already another identity of the same group');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            // Exception expected
            $this->assertSame($e->getMessage(), 'identityAlreadyAssociated');
        }
    }
}
