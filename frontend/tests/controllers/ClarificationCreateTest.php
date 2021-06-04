<?php

/**
 * Description of ClarificationCreateTest
 */

class ClarificationCreateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Helper function to setup environment needed to create a clarification
     *
     * @return array{problemData: array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request}, contestData: array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}, contestant: \OmegaUp\DAO\VO\Identities}
     */
    private function setupContest(
        bool $isGraderExpectedToBeCalled
    ) {
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
        [
            'user' => $userContestant,
            'identity' => $contestant,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Call the API avoiding the broadcaster logic
        if ($isGraderExpectedToBeCalled) {
            $this->detourBroadcasterCalls();
        }

        return [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ];
    }

    /**
     * Creates a valid clarification
     */
    public function testCreateValidClarification() {
        [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ] = $this->setupContest(/*$isGraderExpectedToBeCalled=*/true);

        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestant
        );

        // Assert status of new contest
        $this->assertArrayHasKey(
            'clarification_id',
            $clarificationData['response']
        );

        // Verify that clarification was inserted in the database
        $clarification =
            \OmegaUp\DAO\Clarifications::getByPK(
                $clarificationData['response']['clarification_id']
            );

        // Verify our retreived clarificatoin
        $this->assertNotNull($clarification);
        $this->assertEquals(
            $clarificationData['request']['message'],
            $clarification->message
        );

        // We need to verify that the contest and problem IDs where properly saved
        // Extractiing the contest and problem from DB to check IDs
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $this->assertEquals(
            $contest->problemset_id,
            $clarification->problemset_id
        );
        $this->assertEquals($problem->problem_id, $clarification->problem_id);
    }

    /**
     * Creates a valid clarification, to a problem.
     */
    public function testProblemClarificationsAsAuthor() {
        [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ] = $this->setupContest(/*$isGraderExpectedToBeCalled=*/false);

        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestant
        );

        // Assert status of new contest
        $this->assertArrayHasKey(
            'clarification_id',
            $clarificationData['response']
        );

        // Get clarification as problem author.
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );
        $this->assertCount(1, $response['clarifications']);
        $this->assertEquals(
            $clarificationData['request']['message'],
            $response['clarifications'][0]['message']
        );
    }

    /**
     * Creates a valid clarification, to a problem.
     */
    public function testProblemClarificationsAsUser() {
        [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ] = $this->setupContest(/*$isGraderExpectedToBeCalled=*/false);

        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestant
        );

        // Assert status of new contest
        $this->assertArrayHasKey(
            'clarification_id',
            $clarificationData['response']
        );

        // Get clarification as another user.
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );
        $this->assertEmpty($response['clarifications']);
    }

    /**
     * Creates a valid clarification, to a problem.
     */
    public function testProblemClarificationsWithPublicAnswerAsUser() {
        [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ] = $this->setupContest(/*$isGraderExpectedToBeCalled=*/false);

        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestant
        );

        // Assert status of new contest
        $this->assertArrayHasKey(
            'clarification_id',
            $clarificationData['response']
        );

        // Answer the clarification publicly.
        \OmegaUp\Test\Factories\Clarification::answer(
            $clarificationData,
            $contestData,
            'answer to everyone',
            $contestData['director']->username,
            1
        );

        // Get clarification as another user.
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );
        $this->assertCount(1, $response['clarifications']);
        $this->assertEquals(
            $clarificationData['request']['message'],
            $response['clarifications'][0]['message']
        );
    }

    /**
     * Creates a valid clarification, to a problem.
     */
    public function testProblemClarificationsAsClarificationAuthor() {
        [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ] = $this->setupContest(/*$isGraderExpectedToBeCalled=*/false);

        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestant
        );

        // Assert status of new contest
        $this->assertArrayHasKey(
            'clarification_id',
            $clarificationData['response']
        );

        // Get clarification as the author of the clarification.
        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Problem::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );
        $this->assertCount(1, $response['clarifications']);
        $this->assertEquals(
            $clarificationData['request']['message'],
            $response['clarifications'][0]['message']
        );
    }

    /**
     * Creates a clarification with message too long
     */
    public function testCreateClarificationMessageTooLong() {
        [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ] = $this->setupContest(/*$isGraderExpectedToBeCalled=*/false);

        try {
            \OmegaUp\Test\Factories\Clarification::createClarification(
                $problemData,
                $contestData,
                $contestant,
                'Lorem ipsum dolor sit amet, mauris faucibus pede congue curae nullam, mauris maecenas tincidunt amet, nec wisi vestibulum ut cras in, velit in dolor. Elit hendrerit pede auctor tincidunt neque, lorem nunc sit a vivamus nibh. Auctor habitant, etiam ut nam'
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterStringTooLong', $e->getMessage());
        }
    }

    /**
     * Admin creates one message to everyone in the contest and
     * other one to a specific user
     */
    public function testCreateClarificationsSentByAdmin() {
        [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestant' => $contestant,
        ] = $this->setupContest(/*$isGraderExpectedToBeCalled=*/false);

        // Create 5 users
        $n = 5;
        $users = [];
        $identities = [];
        for ($i = 0; $i < $n; $i++) {
            // Create a user
            ['user' => $users[$i], 'identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();

            // Add it to the contest
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identities[$i]
            );
        }

        $messageToEveryone = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestData['director'],
            'Message to everyone',
            $contestData['director']->username
        );

        $messageToSpecificUser = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestData['director'],
            'Message to a specific user',
            $contestant->username
        );

        $messageToSpecificUserWithPublicAnswer = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestData['director'],
            'Message to a specific user with public answer',
            $contestant->username
        );

        $login = self::login($contestant);
        // Call API
        $response = \OmegaUp\Controllers\Contest::apiClarifications(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]));

        // Asserts that user has three clarifications (One to all the contestants and two privates)
        $this->assertEquals(3, count($response['clarifications']));

        for ($i = 0; $i < $n; $i++) {
            $logins[$i] = self::login($identities[$i]);

            $response = \OmegaUp\Controllers\Contest::apiClarifications(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $logins[$i]->auth_token,
            ]));

            // Asserts that user has only one clarification
            $this->assertEquals(1, count($response['clarifications']));
        }

        // Now, director answers one message, and it turns public
        $response = \OmegaUp\Test\Factories\Clarification::answer(
            $messageToSpecificUserWithPublicAnswer,
            $contestData,
            'answer to everyone',
            $contestData['director']->username,
            '1'
        );

        for ($i = 0; $i < $n; $i++) {
            $response = \OmegaUp\Controllers\Contest::apiClarifications(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $logins[$i]->auth_token,
            ]));

            // Asserts that user has two clarifications
            $this->assertEquals(2, count($response['clarifications']));
        }
    }
}
