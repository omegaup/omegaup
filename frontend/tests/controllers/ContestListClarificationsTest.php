<?php

/**
 * Description of ListClarificationsContest
 *
 * @author joemmanuel
 */

class ListClarificationsContest extends OmegaupTestCase {
    /**
     * Basic test for getting the list of clarifications of a contest.
     * Create 4 clarifications in a contest with one user, then another 3 clarifications
     * with another user.
     * Get the list for the first user, will see only his 4
     */
    public function testListPublicClarificationsForContestant() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        $contestant1 = UserFactory::createUser();

        // Create 4 clarifications with this contestant
        $clarificationData1 = array();
        $this->detourBroadcasterCalls($this->exactly(9));
        for ($i = 0; $i < 4; $i++) {
            $clarificationData1[$i] =
                ClarificationsFactory::createClarification(
                    $problemData,
                    $contestData,
                    $contestant1
                );
        }

        // Answer clarification 0 and 2
        ClarificationsFactory::answer($clarificationData1[0], $contestData);
        ClarificationsFactory::answer($clarificationData1[2], $contestData);

        // Create another contestant
        $contestant2 = UserFactory::createUser();

        // Create 3 clarifications with this contestant
        $clarificationData2 = array();
        for ($i = 0; $i < 3; $i++) {
            $clarificationData2[$i] =
                ClarificationsFactory::createClarification(
                    $problemData,
                    $contestData,
                    $contestant2
                );
        }

        // Prepare the request
        $login = self::login($contestant1);
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ));

        // Call API
        $response = ContestController::apiClarifications($r);

        // Check that we got all clarifications
        $this->assertEquals(count($clarificationData1), count($response['clarifications']));

        // Check that the clarifications came in the order we expect
        // First we expect clarifications not answered
        $this->assertEquals($clarificationData1[3]['request']['message'], $response['clarifications'][0]['message']);
        $this->assertEquals($clarificationData1[1]['request']['message'], $response['clarifications'][1]['message']);

        // Then clarifications answered, newer first
        $this->assertEquals($clarificationData1[2]['request']['message'], $response['clarifications'][2]['message']);
        $this->assertEquals($clarificationData1[0]['request']['message'], $response['clarifications'][3]['message']);
    }
}
