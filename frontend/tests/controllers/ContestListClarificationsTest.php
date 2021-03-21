<?php

class ContestListClarificationsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for getting the list of clarifications of a contest.
     * Create 4 clarifications in a contest with one user, then another 3 clarifications
     * with another user.
     * Get the list for the first user, will see only his 4
     */
    public function testListPublicClarificationsForContestant() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant who will submit the clarification
        ['user' => $contestant1, 'identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();

        // Create 4 clarifications with this contestant
        $clarificationData1 = [];
        $this->detourBroadcasterCalls($this->exactly(9));
        for ($i = 0; $i < 4; $i++) {
            $clarificationData1[$i] =
                \OmegaUp\Test\Factories\Clarification::createClarification(
                    $problemData,
                    $contestData,
                    $identity1
                );
        }

        // Answer clarification 0 and 2
        \OmegaUp\Test\Factories\Clarification::answer(
            $clarificationData1[0],
            $contestData
        );
        \OmegaUp\Test\Factories\Clarification::answer(
            $clarificationData1[2],
            $contestData
        );

        // Create another contestant
        ['user' => $contestant2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Create 3 clarifications with this contestant
        $clarificationData2 = [];
        for ($i = 0; $i < 3; $i++) {
            $clarificationData2[$i] =
                \OmegaUp\Test\Factories\Clarification::createClarification(
                    $problemData,
                    $contestData,
                    $identity2
                );
        }

        // Prepare the request
        $login = self::login($identity1);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiClarifications($r);

        // Check that we got all clarifications
        $this->assertEquals(
            count(
                $clarificationData1
            ),
            count(
                $response['clarifications']
            )
        );

        // Check that the clarifications came in the order we expect
        // First we expect clarifications not answered
        $this->assertEquals(
            $clarificationData1[3]['request']['message'],
            $response['clarifications'][0]['message']
        );
        $this->assertEquals(
            $clarificationData1[1]['request']['message'],
            $response['clarifications'][1]['message']
        );

        // Then clarifications answered, newer first
        $this->assertEquals(
            $clarificationData1[2]['request']['message'],
            $response['clarifications'][2]['message']
        );
        $this->assertEquals(
            $clarificationData1[0]['request']['message'],
            $response['clarifications'][3]['message']
        );
    }

    /**
     * Create 4 clarifications in a contest with one user, then another 3 clarifications
     * with another user.
     * Get the list for the first user, will see only his 4
     */
    public function testListProblemClarifications() {
        // Create two problems for a contest:
        // - Create two clarifications for first problem
        // - Create one clarifications for second problem
        $problemsData = [
            \OmegaUp\Test\Factories\Problem::createProblem(),
            \OmegaUp\Test\Factories\Problem::createProblem(),
        ];

        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemsData[0],
            $contestData
        );

        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemsData[1],
            $contestData
        );

        // Create our contestant who will submit the clarification
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create 4 clarifications with this contestant
        $firstProblemClarifications = [];
        for ($i = 0; $i < 2; $i++) {
            $firstProblemClarifications[$i] =
                \OmegaUp\Test\Factories\Clarification::createClarification(
                    $problemsData[0],
                    $contestData,
                    $identity
                );
        }

        $secondProblemClarification = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemsData[1],
            $contestData,
            $identity
        );

        \OmegaUp\Test\Factories\Clarification::answer(
            $firstProblemClarifications[1],
            $contestData
        );

        \OmegaUp\Test\Factories\Clarification::answer(
            $secondProblemClarification,
            $contestData
        );

        $login = self::login($identity);

        // First problem clarifications
        $response = \OmegaUp\Controllers\Contest::apiProblemClarifications(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemsData[0]['problem']->alias
            ])
        );
        $this->assertCount(2, $response['clarifications']);
        $this->assertEquals(
            $firstProblemClarifications[0]['request']['message'],
            $response['clarifications'][0]['message']
        );
        $this->assertEquals(
            $firstProblemClarifications[1]['request']['message'],
            $response['clarifications'][1]['message']
        );

        // Second problem clarification
        $response = \OmegaUp\Controllers\Contest::apiProblemClarifications(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemsData[1]['problem']->alias
            ])
        );
        $this->assertCount(1, $response['clarifications']);
        $this->assertEquals(
            $secondProblemClarification['request']['message'],
            $response['clarifications'][0]['message']
        );
    }
}
