<?php

/**
 * Description of ClarificationsFactory
 *
 * @author joemmanuel
 */

class ClarificationsFactory {
    /**
     * Creates a clarification in a problem inside a contest
     *
     * @param type $problemData
     * @param type $contestData
     * @param type $contestant
     * @param type $message
     * @param type $receiver
     */
    public static function createClarification(
        $problemData,
        $contestData,
        $contestant,
        $message = null,
        $receiver = null
    ) {
        // Our contestant has to open the contest before sending a clarification
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        // Create the request for our api
        $r = new \OmegaUp\Request();
        $r['message'] = (
            is_null($message) ?
            Utils::CreateRandomString() :
            $message
        );
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['problem_alias'];
        $r['username'] = $receiver;
        $r['public'] = '0';

        // Log in our user and set the auth_token properly
        $login = OmegaupTestCase::login($contestant);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Clarification::apiCreate($r);

        // Clean up stuff
        unset($_REQUEST);

        return [
            'request' => $r,
            'response' => $response
        ];
    }

    /**
     * Answer a clarification
     *
     * @param type $clarificationData
     * @param type $contestData
     * @param type $message
     * @param type $public
     */
    public static function answer(
        $clarificationData,
        $contestData,
        $message = 'lol',
        $receiver = null,
        $public = '0'
    ) {
        // Prepare request
        $r = new \OmegaUp\Request();
        $r['clarification_id'] = $clarificationData['response']['clarification_id'];

        // Log in the user
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Update answer
        $r['answer'] = $message;
        $r['public'] = $public;
        $r['username'] = $receiver;

        // Call api
        \OmegaUp\Controllers\Clarification::apiUpdate($r);
    }
}
