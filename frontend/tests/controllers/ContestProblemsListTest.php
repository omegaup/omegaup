<?php

/**
 * Description of ContestProblemsListTest
 *
 * @author joemmanuel
 */

class ContestProblemsListTest extends OmegaupTestCase {
    /**
     * Sets the context for a basic scoreboard test
     * @param  integer $numUsers
     * @param  integer $numProblems
     * @return array
     */
    private function prepareContestData($numUsers = 3, $numProblems = 9) {
        // Create the contest
        $contestData = ContestsFactory::createContest();

        $problemData = [];
        for ($i = 0; $i < $numProblems; $i++) {
            // Create the problems
            $problemData[] = ProblemsFactory::createProblem(new ProblemParams([
                'title' => 'Problem ' . ($i + 1),
            ]));

            // Add the problems to the contest
            ContestsFactory::addProblemToContest($problemData[$i], $contestData);
        }

        $contestants = [];
        for ($i = 0; $i < $numUsers; $i++) {
            // Create our contestants
            $contestants[] = UserFactory::createUser();

            // Add users to contest
            ContestsFactory::addUser($contestData, $contestants[$i]);
        }
        $contestDirector = $contestData['director'];
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        return [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestants' => $contestants,
            'contestAdmin' => $contestAdmin,
        ];
    }

    /**
     * Basic test in a contest to validate the order of the problems
     */
    public function testBasicContestWithProblems() {
        $testData = $this->prepareContestData();

        // Create request
        $login = self::login($testData['contestants'][0]);
        $adminLogin = self::login($testData['contestAdmin']);

        // Create API
        $scoreboardResponse = \OmegaUp\Controllers\Problemset::apiScoreboard(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['contestData']['contest']->problemset_id,
        ]));

        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $testData['contestData']['contest']->alias,
        ]));

        $problemsResponse = \OmegaUp\Controllers\Contest::apiProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'contest_alias' => $testData['contestData']['contest']->alias,
        ]));

        foreach ($scoreboardResponse['problems'] as $index => $problem) {
            $this->assertEquals($problem['alias'], $detailsResponse['problems'][$index]['alias']);
            $this->assertEquals($problem['alias'], $problemsResponse['problems'][$index]['alias']);
        }
    }
}
