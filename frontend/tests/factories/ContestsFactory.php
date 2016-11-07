<?php

/**
 * ContestsFactory
 *
 * @author joemmanuel
 */

class ContestsFactory {
    /**
     * Returns a Request object with complete context to create a contest.
     * By default, contest duration is 1HR.
     *
     * @param string $title
     * @param string $public
     * @param Users $contestDirector
     * @return Request
     */
    public static function getRequest($title = null, $public = 0, Users $contestDirector = null, $languages = null, $finish_time = null, $penalty_calc_policy = null) {
        if (is_null($contestDirector)) {
            $contestDirector = UserFactory::createUser();
        }

        if (is_null($title)) {
            $title = Utils::CreateRandomString();
        }

        // Set context
        $r = new Request();
        $r['title'] = $title;
        $r['description'] = 'description';
        $r['start_time'] = Utils::GetPhpUnixTimestamp() - 60 * 60;
        $r['finish_time'] = ($finish_time == null ? (Utils::GetPhpUnixTimestamp() + 60 * 60) : $finish_time);
        $r['window_length'] = null;
        $r['public'] = $public;
        $r['alias'] = substr($title, 0, 20);
        $r['points_decay_factor'] = '.02';
        $r['partial_score'] = '0';
        $r['submissions_gap'] = '0';
        $r['feedback'] = 'yes';
        $r['penalty'] = 100;
        $r['scoreboard'] = 100;
        $r['penalty_type'] = 'contest_start';
        if ($penalty_calc_policy == null) {
            $r['penalty_calc_policy'] = 'sum';
        } else {
            $r['penalty_calc_policy'] = $penalty_calc_policy;
        }
        $r['languages'] = $languages;
        $r['recommended'] = 0; // This is just a default value, it is not honored by apiCreate.

        return array(
            'request' => $r,
            'director' => $contestDirector);
    }

    public static function createContest($title = null, $public = 1, Users $contestDirector = null, $languages = null, $finish_time = null, $penalty_calc_policy = null) {
        // Create a valid contest Request object
        $contestData = ContestsFactory::getRequest($title, 0, $contestDirector, $languages, $finish_time, $penalty_calc_policy);
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = OmegaupTestCase::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);

        if ($public === 1) {
            self::forcePublic($contestData);
            $r['public'] = 1;
        }

        $contest = ContestsDAO::getByAlias($r['alias']);

        return array(
            'director' => $contestData['director'],
            'request' => $r,
            'contest' => $contest
        );
    }

    public static function addProblemToContest($problemData, $contestData) {
        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['alias'];
        $r['points'] = 100;
        $r['order_in_contest'] = 1;

        // Call API
        $response = ContestController::apiAddProblem($r);

        // Clean up
        unset($_REQUEST);
    }

    public static function removeProblemFromContest($problemData, $contestData) {
        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);

        $r = new Request(
            array(
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['alias']
            )
        );

        // Call API
        $response = ContestController::apiRemoveProblem($r);

        // Clean up
        unset($_REQUEST);

        return $response;
    }

    public static function openContest($contestData, $user) {
        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($user);
        $r['auth_token'] = $login->auth_token;

        // Prepare our request
        $r['contest_alias'] = $contestData['request']['alias'];

        // Call api
        ContestController::apiOpen($r);

        unset($_REQUEST);
    }

    public static function openProblemInContest($contestData, $problemData, $user) {
        // Prepare our request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['alias'];

        // Log in the user
        $login = OmegaupTestCase::login($user);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ProblemController::apiDetails($r);

        unset($_REQUEST);
    }

    public static function addUser($contestData, $user) {
        // Prepare our request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['usernameOrEmail'] = $user->username;

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ContestController::apiAddUser($r);

        unset($_REQUEST);
    }

    public static function addAdminUser($contestData, $user) {
        // Prepare our request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['usernameOrEmail'] = $user->username;

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ContestController::apiAddAdmin($r);

        unset($_REQUEST);
    }

    public static function addGroupAdmin($contestData, Groups $group) {
        // Prepare our request
        $r = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'group' => $group->alias,
        ));

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ContestController::apiAddGroupAdmin($r);
    }

    public static function makeContestWindowLength($contestData, $windowLength = 20) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->window_length = $windowLength;
        ContestsDAO::save($contest);
    }

    public static function forcePublic($contestData) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->public = 1;
        ContestsDAO::save($contest);
    }

    public static function setScoreboardPercentage($contestData, $percentage) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->scoreboard = $percentage;
        ContestsDAO::save($contest);
    }
}
