<?php

/**
 * Description of ContestDeleteContest
 *
 * @author joemmanuel
 */
class ContestDeleteTest extends \OmegaUp\Test\ControllerTestCase {
    public function testDeleteContest() {
        // Create 5 contests
        $numberOfContests = 5;
        foreach (range(0, $numberOfContests - 1) as $i) {
            $contestData[] = \OmegaUp\Test\Factories\Contest::createContest();
        }

        // Delete one contest.
        $login = self::login($contestData[0]['director']);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiDelete(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData[0]['request']['alias'],
            ])
        );

        $response = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );

        // Only 4 contests should remain visible
        $this->assertEquals(4, $response['number_of_results']);
    }

    public function testDeleteContestNonDirector() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            // Non-director users can not delete contests
            \OmegaUp\Controllers\Contest::apiDelete(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
