<?php

/**
 * Description of DetailsClarificationTest
 *
 * @author joemmanuel
 */

class DetailsClarificationTest extends OmegaupTestCase {
    /**
     * Validates a clarification given the clarification ID
     *
     * @param int $clarification_id
     * @param array $response
     */
    private function assertClarification($clarification_id, $response) {
        // Get the actual clarification from DB to compare it with what we got
        $clarification = \OmegaUp\DAO\Clarifications::getByPK(
            $clarification_id
        );

        // Assert status of clarification
        $this->assertEquals($clarification->message, $response['message']);
        $this->assertEquals($clarification->answer, $response['answer']);
        $this->assertEquals($clarification->time, $response['time']);
        $this->assertEquals(
            $clarification->problem_id,
            $response['problem_id']
        );
        $this->assertEquals(
            $clarification->problemset_id,
            $response['problemset_id']
        );
    }

    /**
     * Basic test that contest director can view private
     * clarifications
     */
    public function testShowClarificationAsContestDirector() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Prepare the request object
        $r = new \OmegaUp\Request();
        $r['clarification_id'] = $clarificationData['response']['clarification_id'];

        // Log in with the contest director
        $login = self::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Clarification::apiDetails($r);

        // Check the data we got
        $this->assertClarification($r['clarification_id'], $response);
    }

    /**
     * Checks that the original creator of the clarification can actually
     * see it, even though it is private by default for everybody else
     */
    public function testShowClarificationAsOriginalContestant() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Prepare the request object
        $r = new \OmegaUp\Request();
        $r['clarification_id'] = $clarificationData['response']['clarification_id'];

        // Log in with the author of the clarification
        $login = self::login($identity);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Clarification::apiDetails($r);

        // Check the data we got
        $this->assertClarification($r['clarification_id'], $response);
    }

    /**
     * Checks that private clarifications cant be viewed by someone else
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testClarificationsCreatedPrivateAsDefault() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create our contestant who will try to view the clarification
        ['user' => $contestant2, 'identity' => $identity2] = UserFactory::createUser();

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Prepare the request object
        $r = new \OmegaUp\Request();
        $r['clarification_id'] = $clarificationData['response']['clarification_id'];

        // Log in with the author of the clarification
        $login = self::login($identity2);
        $r['auth_token'] = $login->auth_token;

        // Call API, will fail
        \OmegaUp\Controllers\Clarification::apiDetails($r);
    }

    public function testPublicClarificationsCanBeViewed() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant who will submit the clarification
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create our contestant who will try to view the clarification
        ['user' => $contestant2, 'identity' => $identity2] = UserFactory::createUser();

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = ClarificationsFactory::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Manually set the just created clarification to PUBLIC
        $clarification = \OmegaUp\DAO\Clarifications::getByPK(
            $clarificationData['response']['clarification_id']
        );
        $clarification->public = '1';
        \OmegaUp\DAO\Clarifications::update($clarification);

        // Prepare the request object
        $r = new \OmegaUp\Request();
        $r['clarification_id'] = $clarificationData['response']['clarification_id'];

        // Log in with the author of the clarification
        $login = self::login($identity2);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Clarification::apiDetails($r);

        // Check the data we got
        $this->assertClarification($r['clarification_id'], $response);
    }
}
