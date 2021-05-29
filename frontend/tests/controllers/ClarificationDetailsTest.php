<?php

/**
 * Description of ClarificationDetailsTest
 */

class ClarificationDetailsTest extends \OmegaUp\Test\ControllerTestCase {
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

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
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

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
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
     */
    public function testClarificationsCreatedPrivateAsDefault() {
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
            'user' => $contestant,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Create our contestant who will try to view the clarification
        [
            'user' => $contestant2,
            'identity' => $identity2,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Prepare the request object

        // Log in with the author of the clarification
        $login = self::login($identity2);

        try {
            \OmegaUp\Controllers\Clarification::apiDetails(new \OmegaUp\Request([
                'clarification_id' => $clarificationData['response']['clarification_id'],
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testPublicClarificationsCanBeViewed() {
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

        // Create our contestant who will try to view the clarification
        ['user' => $contestant2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Create the clarification, note that contestant will create it
        $this->detourBroadcasterCalls();
        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
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
