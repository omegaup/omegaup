<?php

namespace OmegaUp\Test\Controllers;

/**
 * Test User Compare API
 */
class UserCompareTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test comparing two public profiles
     */
    public function testCompareTwoPublicProfiles() {
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'testuser1'])
        );
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'testuser2'])
        );

        // Create a problem and have user1 solve it
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login($identity1);
        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity1,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run);

        // Verify the run was graded as AC
        $runFromDb = \OmegaUp\DAO\Runs::getByPK(
            \OmegaUp\DAO\Submissions::getByGuid($run['response']['guid'])->current_run_id
        );
        $this->assertSame('AC', $runFromDb->verdict);

        // Login as user2 to call compare API
        $login2 = self::login($identity2);

        // Call compare API
        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
                'auth_token' => $login2->auth_token,
                'username1' => 'testuser1',
                'username2' => 'testuser2',
            ])
        );

        $this->assertNotNull($response['user1']);
        $this->assertNotNull($response['user2']);
        $this->assertSame(
            'testuser1',
            $response['user1']['profile']['username']
        );
        $this->assertSame(
            'testuser2',
            $response['user2']['profile']['username']
        );
        $this->assertSame(1, $response['user1']['solvedProblemsCount']);
        $this->assertSame(0, $response['user2']['solvedProblemsCount']);
    }

    /**
     * Test comparing with an invalid username
     */
    public function testCompareWithInvalidUsername() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'validuser'])
        );

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username1' => 'validuser',
                'username2' => 'nonexistentuser123',
            ])
        );

        $this->assertNotNull($response['user1']);
        $this->assertNull($response['user2']);
    }

    /**
     * Test comparing with empty usernames
     */
    public function testCompareWithEmptyUsernames() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertNull($response['user1']);
        $this->assertNull($response['user2']);
    }

    /**
     * Test comparing user with themselves
     */
    public function testCompareUserWithSelf() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'selfcompare']
            )
        );

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username1' => 'selfcompare',
                'username2' => 'selfcompare',
            ])
        );

        $this->assertNotNull($response['user1']);
        $this->assertNotNull($response['user2']);
        $this->assertSame(
            'selfcompare',
            $response['user1']['profile']['username']
        );
        $this->assertSame(
            'selfcompare',
            $response['user2']['profile']['username']
        );
    }

    /**
     * Test comparing with a private profile
     */
    public function testCompareWithPrivateProfile() {
        ['identity' => $publicIdentity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'publicuser'])
        );
        ['identity' => $privateIdentity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => 'privateuser',
                'isPrivate' => true,
            ])
        );

        // Have private user solve a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login($privateIdentity);
        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $privateIdentity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run);

        // Verify the run was graded as AC
        $runFromDb = \OmegaUp\DAO\Runs::getByPK(
            \OmegaUp\DAO\Submissions::getByGuid($run['response']['guid'])->current_run_id
        );
        $this->assertSame('AC', $runFromDb->verdict);

        // Login as public user to call compare API
        $publicLogin = self::login($publicIdentity);

        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
                'auth_token' => $publicLogin->auth_token,
                'username1' => 'publicuser',
                'username2' => 'privateuser',
            ])
        );

        $this->assertNotNull($response['user1']);
        $this->assertNotNull($response['user2']);
        // Private profile should have limited info
        $this->assertTrue($response['user2']['profile']['is_private']);
        // Verify that solved problems are hidden for private profiles (null instead of actual count)
        $this->assertNull(
            $response['user2']['solvedProblemsCount'],
            'Private profile should hide solved problems count'
        );
    }

    /**
     * Test getCompareDetailsForTypeScript returns correct payload structure
     */
    public function testGetCompareDetailsForTypeScript() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'testuser'])
        );

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::getCompareDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username1' => 'testuser',
            ])
        );

        $this->assertSame('user_compare', $response['entrypoint']);
        $this->assertArrayHasKey('payload', $response['templateProperties']);
        $this->assertArrayHasKey(
            'user1',
            $response['templateProperties']['payload']
        );
        $this->assertArrayHasKey(
            'user2',
            $response['templateProperties']['payload']
        );
        $this->assertArrayHasKey(
            'username1',
            $response['templateProperties']['payload']
        );
        $this->assertArrayHasKey(
            'username2',
            $response['templateProperties']['payload']
        );
    }

    /**
     * Test comparing users without authentication
     */
    public function testCompareWithoutAuthentication() {
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'publicuser1']
            )
        );
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'publicuser2']
            )
        );

        // Call compare API without authentication
        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
                'username1' => 'publicuser1',
                'username2' => 'publicuser2',
            ])
        );

        $this->assertNotNull($response['user1']);
        $this->assertNotNull($response['user2']);
        $this->assertSame(
            'publicuser1',
            $response['user1']['profile']['username']
        );
        $this->assertSame(
            'publicuser2',
            $response['user2']['profile']['username']
        );
    }
}
