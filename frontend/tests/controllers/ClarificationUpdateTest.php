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
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create clarification
        $this->detourBroadcasterCalls($this->exactly(2));
        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Update answer
        $newAnswer = 'new answer';
        $response = ClarificationsFactory::answer(
            $clarificationData,
            $contestData,
            $newAnswer
        );

        // Get clarification from DB
        $clarification = \OmegaUp\DAO\Clarifications::getByPK(
            $clarificationData['response']['clarification_id']
        );

        // Validate that clarification stays the same
        $this->assertEquals(
            $clarificationData['request']['message'],
            $clarification->message
        );
        $this->assertEquals(
            $clarificationData['request']['public'] == '1',
            $clarification->public
        );

        // Validate our update
        $this->assertEquals($newAnswer, $clarification->answer);
    }
}
