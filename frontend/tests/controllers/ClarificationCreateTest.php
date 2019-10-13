<?php

/**
 * Description of CreateClarificationTest
 *
 * @author joemmanuel
 */

class CreateClarificationTest extends OmegaupTestCase {
    /**
     * Helper function to setup environment needed to create a clarification
     */
    private function setupContest(
        &$problemData,
        &$contestData,
        &$contestant,
        $isGraderExpectedToBeCalled = true
    ) {
         // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        $contestant = UserFactory::createUser();

        // Call the API avoiding the broadcaster logic
        if ($isGraderExpectedToBeCalled) {
            $this->detourBroadcasterCalls();
        }
    }

    /**
     * Creates a valid clarification
     */
    public function testCreateValidClarification() {
        $problemData = null;
        $contestData = null;
        $contestant = null;

        // Setup contest is required to submit a clarification
        $this->setupContest($problemData, $contestData, $contestant);

        $clarificationData = ClarificationsFactory::createClarification(
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
    * Creates a clarification with message too long
    *
    * @expectedException \OmegaUp\Exceptions\InvalidParameterException
    */
    public function testCreateClarificationMessageTooLong() {
        $problemData = null;
        $contestData = null;
        $contestant = null;

        // Setup contest is required to submit a clarification
        $this->setupContest(
            $problemData,
            $contestData,
            $contestant,
            false /*isGraderExpectedToBeCalled*/
        );

        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $contestant,
            'Lorem ipsum dolor sit amet, mauris faucibus pede congue curae nullam, mauris maecenas tincidunt amet, nec wisi vestibulum ut cras in, velit in dolor. Elit hendrerit pede auctor tincidunt neque, lorem nunc sit a vivamus nibh. Auctor habitant, etiam ut nam'
        );
    }

    /**
     * Admin creates one message to everyone in the contest and
     * other one to a specific user
     */
    public function testCreateClarificationsSentByAdmin() {
        $problemData = null;
        $contestData = null;
        $contestant = null;

        // Setup contest is required to submit a clarification
        $this->setupContest(
            $problemData,
            $contestData,
            $contestant,
            false /*isGraderExpectedToBeCalled*/
        );
        $directorIdentity = \OmegaUp\DAO\Identities::getByPK(
            $contestData['director']->main_identity_id
        );
        // Create 5 users
        $n = 5;
        $users = [];
        for ($i = 0; $i < $n; $i++) {
            // Create a user
            $users[$i] = UserFactory::createUser();

            // Add it to the contest
            ContestsFactory::addUser($contestData, $users[$i]);
        }

        $messageToEveryone = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $contestData['director'],
            'Message to everyone',
            $directorIdentity->username
        );

        $messageToSpecificUser = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $contestData['director'],
            'Message to a specific user',
            $contestant->username
        );

        $messageToSpecificUserWithPublicAnswer = ClarificationsFactory::createClarification(
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
            $logins[$i] = self::login($users[$i]);

            $response = \OmegaUp\Controllers\Contest::apiClarifications(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $logins[$i]->auth_token,
            ]));

            // Asserts that user has only one clarification
            $this->assertEquals(1, count($response['clarifications']));
        }

        // Now, director answers one message, and it turns public
        $response = ClarificationsFactory::answer(
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
