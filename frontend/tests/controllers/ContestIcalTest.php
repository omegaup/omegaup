<?php

/**
 * Tests for Contest::apiIcal endpoint
 * These tests verify the iCal download functionality for contests
 */

class ContestIcalTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test that public contests can be downloaded anonymously
     */
    public function testPublicContestIcalAnonymous() {
        // Create a public contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
            ])
        );

        // Capture output
        ob_start();
        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ExitException to be thrown');
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected - the API throws ExitException after sending headers
            $this->assertTrue(true);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Test that public contests can be downloaded by authenticated users
     */
    public function testPublicContestIcalAuthenticated() {
        // Create a public contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
            ])
        );

        // Create a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Capture output
        ob_start();
        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ExitException to be thrown');
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected
            $this->assertTrue(true);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Test that private contests require authentication
     */
    public function testPrivateContestIcalRequiresAuth() {
        // Create a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected UnauthorizedException to be thrown');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Expected - private contests require authentication
            $this->assertTrue(true);
        }
    }

    /**
     * Test that unauthorized users cannot download private contest iCal
     */
    public function testPrivateContestIcalUnauthorizedUser() {
        // Create a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        // Create a user NOT added to the contest
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ForbiddenAccessException to be thrown');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test that authorized participants can download private contest iCal
     */
    public function testPrivateContestIcalAuthorizedParticipant() {
        // Create a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        // Create a user and add them to the contest
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $login = self::login($identity);

        // Capture output
        ob_start();
        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ExitException to be thrown');
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected - download succeeded
            $this->assertTrue(true);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Test that contest admins can download private contest iCal
     */
    public function testPrivateContestIcalContestAdmin() {
        // Create a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        // Login as contest director
        $login = self::login($contestData['director']);

        // Capture output
        ob_start();
        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ExitException to be thrown');
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected - download succeeded
            $this->assertTrue(true);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Test that invalid contest alias returns not found
     */
    public function testIcalInvalidContestAlias() {
        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'contest_alias' => 'nonexistent-contest-alias',
            ]));
            $this->fail('Expected NotFoundException to be thrown');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('contestNotFound', $e->getMessage());
        }
    }

    /**
     * Test that registration-mode contests work appropriately
     */
    public function testRegistrationContestIcalAccepted() {
        // Create a contest (starts as private by default in factory)
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add a problem to the contest (required for registration/public modes)
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Update contest to registration mode
        $directorLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $directorLogin->auth_token,
        ]));

        // Create a user, request access and accept
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Request access to contest
        $userLogin = self::login($identity);
        \OmegaUp\Controllers\Contest::apiRegisterForContest(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        // Accept the request as director
        \OmegaUp\Controllers\Contest::apiArbitrateRequest(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'username' => $identity->username,
            'resolution' => true,
        ]));

        // Now the user should be able to download iCal
        ob_start();
        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ExitException to be thrown');
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected - download succeeded
            $this->assertTrue(true);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Test that registration-mode contests deny access to non-accepted users
     */
    public function testRegistrationContestIcalNotAccepted() {
        // Create a contest (starts as private by default in factory)
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add a problem to the contest (required for registration/public modes)
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Update contest to registration mode
        $directorLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $directorLogin->auth_token,
        ]));

        // Create a user but don't request or accept
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ForbiddenAccessException to be thrown');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test private contest access via group membership
     */
    public function testPrivateContestIcalViaGroup() {
        // Create a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        // Create a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to contest via group
        $directorLogin = self::login($contestData['director']);
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup(
            login: $directorLogin
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identity,
            $directorLogin
        );
        \OmegaUp\Controllers\Contest::apiAddGroup(
            new \OmegaUp\Request([
                'contest_alias' => strval($contestData['request']['alias']),
                'group' => $groupData['group']->alias,
                'auth_token' => $directorLogin->auth_token,
            ])
        );

        // Now user should be able to download iCal
        $login = self::login($identity);
        ob_start();
        try {
            \OmegaUp\Controllers\Contest::apiIcal(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Expected ExitException to be thrown');
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected - download succeeded
            $this->assertTrue(true);
        } finally {
            ob_end_clean();
        }
    }
}
