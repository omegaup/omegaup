<?php

class ClarificationUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for answer
     */
    public function testUpdateAnswer() {
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create clarification
        $this->detourBroadcasterCalls($this->exactly(2));
        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Update answer
        $newAnswer = 'new answer';
        $response = \OmegaUp\Test\Factories\Clarification::answer(
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
