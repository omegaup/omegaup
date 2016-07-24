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
    private function setupContest(&$problemData, &$contestData, &$contestant, $isGraderExpectedToBeCalled = true) {
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
        $this->assertArrayHasKey('clarification_id', $clarificationData['response']);

        // Verify that clarification was inserted in the database
        $clarification =
            ClarificationsDAO::getByPK($clarificationData['response']['clarification_id']);

        // Verify our retreived clarificatoin
        $this->assertNotNull($clarification);
        $this->assertEquals(
            $clarificationData['request']['message'],
            $clarification->getMessage()
        );

        // We need to verify that the contest and problem IDs where properly saved
        // Extractiing the contest and problem from DB to check IDs
        $problem = ProblemsDAO::getByAlias($problemData['request']['alias']);
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        $this->assertEquals($contest->getContestId(), $clarification->getContestId());
        $this->assertEquals($problem->getProblemId(), $clarification->getProblemId());
    }

    /**
    * Creates a clarification with message too long
    *
    * @expectedException InvalidParameterException
    */
    public function testCreateClarificationMessageTooLong() {
        $problemData = null;
        $contestData = null;
        $contestant = null;

        // Setup contest is required to submit a clarification
        $this->setupContest($problemData, $contestData, $contestant, false /*isGraderExpectedToBeCalled*/);

        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $contestant,
            'Lorem ipsum dolor sit amet, mauris faucibus pede congue curae nullam, mauris maecenas tincidunt amet, nec wisi vestibulum ut cras in, velit in dolor. Elit hendrerit pede auctor tincidunt neque, lorem nunc sit a vivamus nibh. Auctor habitant, etiam ut nam'
        );
    }
}
