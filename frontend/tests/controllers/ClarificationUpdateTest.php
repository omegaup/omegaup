<?php

/**
 * Description of UpdateClarificationTest
 *
 * @author joemmanuel
 */

class UpdateClarificationTest extends OmegaupTestCase {
    /**
     * Basic test for answer
     *
     */
    public function testUpdateAnswer() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        $contestant = UserFactory::createUser();

        // Create clarification
        $this->detourBroadcasterCalls($this->exactly(2));
        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $contestant
        );

        // Update answer
        $newAnswer = 'new answer';
        $response = ClarificationsFactory::answer(
            $clarificationData,
            $contestData,
            $newAnswer
        );

        // Get clarification from DB
        $clarification = ClarificationsDAO::getByPK(
            $clarificationData['response']['clarification_id']
        );

        // Validate that clarification stays the same
        $this->assertEquals(
            $clarificationData['request']['message'],
            $clarification->message
        );
        $this->assertEquals(
            $clarificationData['request']['public'],
            $clarification->public
        );

        // Validate our update
        $this->assertEquals($newAnswer, $clarification->answer);
    }
}
