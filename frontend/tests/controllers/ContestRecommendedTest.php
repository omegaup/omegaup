<?php
/**
 * ContestRecommendedTest
 */

class ContestRecommendedTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests that the recommended flag can be set when creating a contest
     * by a support team member.
     */
    public function testCreateContestWithRecommendedFlag() {
        // Create a support team member
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Create a valid contest Request object
        $contestData = \OmegaUp\Test\Factories\Contest::getRequest(new \OmegaUp\Test\Factories\ContestParams(
            ['admissionMode' => 'private']
        ));
        $r = $contestData['request'];
        $r['recommended'] = true;

        // Log in the user and set the auth token in the new request
        $login = self::login($identity);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Contest::apiCreate($r);
        $this->assertSame('ok', $response['status']);

        // Verify the contest was created with recommended flag
        $contest = \OmegaUp\DAO\Contests::getByAlias($r['alias']);
        $this->assertTrue($contest->recommended);
    }

    /**
     * Tests that non-support team members cannot set the recommended flag
     * when creating a contest.
     */
    public function testCreateContestWithRecommendedFlagAsNonSupportTeamMember() {
        // Create a regular user (not a support team member)
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a valid contest Request object
        $contestData = \OmegaUp\Test\Factories\Contest::getRequest(new \OmegaUp\Test\Factories\ContestParams(
            ['admissionMode' => 'private']
        ));
        $r = $contestData['request'];
        $r['recommended'] = true;

        // Log in the user and set the auth token in the new request
        $login = self::login($identity);
        $r['auth_token'] = $login->auth_token;

        try {
            \OmegaUp\Controllers\Contest::apiCreate($r);
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Tests that the recommended flag can be updated when editing a contest
     * by a support team member.
     */
    public function testUpdateContestRecommendedFlag() {
        // Create a support team member
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Create a contest first
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
                'recommended' => false,
                'contestDirector' => $identity,
            ])
        );

        // Update the contest with recommended flag
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => $contestData['request']['title'],
            'description' => $contestData['request']['description'],
            'start_time' => $contestData['request']['start_time'],
            'finish_time' => $contestData['request']['finish_time'],
            'recommended' => true,
        ]));
        $this->assertSame('ok', $response['status']);

        // Verify the contest was updated with recommended flag
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertTrue($contest->recommended);
    }

    /**
     * Tests that non-support team members cannot update the recommended flag
     * when editing a contest.
     */
    public function testUpdateContestRecommendedFlagAsNonSupportTeamMember() {
        // Create a regular user (not a support team member)
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a contest first with a support team member as director
        ['identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
                'recommended' => false,
                'contestDirector' => $supportIdentity,
            ])
        );

        // Try to update the contest with recommended flag
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'title' => $contestData['request']['title'],
                'description' => $contestData['request']['description'],
                'start_time' => $contestData['request']['start_time'],
                'finish_time' => $contestData['request']['finish_time'],
                'recommended' => true,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        // Verify the contest was not updated
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertFalse($contest->recommended);
    }

    /**
     * Tests that the recommended flag can be toggled multiple times
     * by a support team member.
     */
    public function testToggleContestRecommendedFlag() {
        // Create a support team member
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Create a contest first
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
                'recommended' => false,
                'contestDirector' => $identity,
            ])
        );

        $login = self::login($identity);

        // Toggle recommended flag to true
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => $contestData['request']['title'],
            'description' => $contestData['request']['description'],
            'start_time' => $contestData['request']['start_time'],
            'finish_time' => $contestData['request']['finish_time'],
            'recommended' => true,
        ]));
        $this->assertSame('ok', $response['status']);

        // Verify the contest was updated with recommended flag
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertTrue($contest->recommended);

        // Toggle recommended flag back to false
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => $contestData['request']['title'],
            'description' => $contestData['request']['description'],
            'start_time' => $contestData['request']['start_time'],
            'finish_time' => $contestData['request']['finish_time'],
            'recommended' => false,
        ]));
        $this->assertSame('ok', $response['status']);

        // Verify the contest was updated with recommended flag
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertFalse($contest->recommended);
    }
}
