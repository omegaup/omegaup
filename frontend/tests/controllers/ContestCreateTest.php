<?php

/**
 * CreateContestTest
 *
 * @author joemmanuel
 */

class CreateContestTest extends OmegaupTestCase {
    /**
     * Basic Create Contest scenario
     *
     */
    public function testCreateContestPositive() {
        // Create a valid contest Request object
        $contestData = ContestsFactory::getRequest();
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);

        // Assert status of new contest
        $this->assertEquals('ok', $response['status']);

        // Assert that the contest requested exists in the DB
        $this->assertContest($r);
    }

    /**
     * Tests that missing params throw exception
     */
    public function testMissingParameters() {
        // Array of valid keys
        $valid_keys = array(
            'title',
            'description',
            'start_time',
            'finish_time',
            'public',
            'alias',
            'points_decay_factor',
            'submissions_gap',
            'feedback',
            'scoreboard',
            'penalty_type',
        );

        foreach ($valid_keys as $key) {
            // Create a valid contest Request object
            $contestData = ContestsFactory::getRequest();
            $r = $contestData['request'];
            $contestDirector = $contestData['director'];

            $login = self::login($contestDirector);

            // unset the current key from request
            unset($r[$key]);

            // Set the valid auth token in the new request
            $r['auth_token'] = $login->auth_token;

            try {
                // Call the API
                $response = ContestController::apiCreate($r);
            } catch (InvalidParameterException $e) {
                // This exception is expected
                unset($_REQUEST);
                continue;
            }

            $this->fail('Exception was expected. Parameter: ' . $key);
        }
    }

    /**
     * Tests that 2 contests with same name cannot be created
     *
     * @expectedException DuplicatedEntryInDatabaseException
     */
    public function testCreate2ContestsWithSameAlias() {
        // Create a valid contest Request object
        $contestData = ContestsFactory::getRequest();
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);
        $this->assertEquals('ok', $response['status']);

        // Call the API for the 2nd time with same alias
        $response = ContestController::apiCreate($r);
    }

    /**
     * Tests very long contests
     *
     * @expectedException InvalidParameterException
     */
    public function testCreateVeryLongContest() {
        // Create a valid contest Request object
        $contestData = ContestsFactory::getRequest();
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Longer than a month
        $r['finish_time'] = $r['start_time'] + (60 * 60 * 24 * 32);

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);
    }

    /**
     * Public contest without problems is not valid.
     *
     * @expectedException InvalidParameterException
     */
    public function testCreatePublicContest() {
        // Create a valid contest Request object
        $contestData = ContestsFactory::getRequest();
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];
        $r['public'] = 1;

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);
    }

    /**
     * Public contest with problems is valid.
     */
    public function testCreatePublicContestWithProblems() {
        $problem = ProblemsFactory::createProblem();

        // Create a valid contest Request object
        $contestData = ContestsFactory::getRequest();
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];
        $r['public'] = 1;
        $r['problems'] = json_encode(array(array(
            'problem' => $problem['problem']->alias,
            'points' => 100,
        )));

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);

        // Assert status of new contest
        $this->assertEquals('ok', $response['status']);

        // Assert problem was added.
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ));
        $response = ContestController::apiProblems($r);
        $this->assertEquals(1, count($response['problems']));
        $this->assertEquals(
            $problem['problem']->alias,
            $response['problems'][0]['alias']
        );
    }

    /**
     * Public contest with private problems is not valid.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testCreatePublicContestWithPrivateProblems() {
        $problem = ProblemsFactory::createProblem(null, null, 0);

        // Create a valid contest Request object
        $contestData = ContestsFactory::getRequest();
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];
        $r['public'] = 1;
        $r['problems'] = json_encode(array(array(
            'problem' => $problem['problem']->alias,
            'points' => 100,
        )));

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);
    }
}
