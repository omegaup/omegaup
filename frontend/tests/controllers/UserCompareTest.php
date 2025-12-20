<?php
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
        ['identity' => $_] = \OmegaUp\Test\Factories\User::createUser(
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

        // Call compare API
        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
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
        ['identity' => $_] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'validuser'])
        );

        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
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
        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([])
        );

        $this->assertNull($response['user1']);
        $this->assertNull($response['user2']);
    }

    /**
     * Test comparing user with themselves
     */
    public function testCompareUserWithSelf() {
        ['identity' => $_] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'selfcompare']
            )
        );

        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
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
        ['identity' => $_] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'publicuser'])
        );
        ['identity' => $_] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => 'privateuser',
                'isPrivate' => true,
            ])
        );

        $response = \OmegaUp\Controllers\User::apiCompare(
            new \OmegaUp\Request([
                'username1' => 'publicuser',
                'username2' => 'privateuser',
            ])
        );

        $this->assertNotNull($response['user1']);
        $this->assertNotNull($response['user2']);
        // Private profile should have limited info
        $this->assertTrue($response['user2']['profile']['is_private']);
        $this->assertSame(0, $response['user2']['solvedProblemsCount']);
    }

    /**
     * Test getCompareDetailsForTypeScript returns correct payload structure
     */
    public function testGetCompareDetailsForTypeScript() {
        ['identity' => $_] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'testuser'])
        );

        $response = \OmegaUp\Controllers\User::getCompareDetailsForTypeScript(
            new \OmegaUp\Request([
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
}
