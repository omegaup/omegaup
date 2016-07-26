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
     */
    public static function createClarification(
        $problemData,
        $contestData,
        $contestant,
        $message = null
    ) {
        // Our contestant has to open the contest before sending a clarification
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

        // Create the request for our api
        $r = new Request();
        $r['message'] = ($message === null ? Utils::CreateRandomString() : $message);
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['alias'];
        $r['public'] = '0';

        // Log in our user and set the auth_token properly
        $r['auth_token'] = OmegaupTestCase::login($contestant);

        // Call the API
        $response = ClarificationController::apiCreate($r);

        // Clean up stuff
        unset($_REQUEST);

        return array(
            'request' => $r,
            'response' => $response
        );
    }

    /**
     * Answer a clarification
     *
     * @param type $clarificationData
     * @param type $contestData
     * @param type $message
     */
    public static function answer(
        $clarificationData,
        $contestData,
        $message = 'lol'
    ) {
        // Prepare request
        $r = new Request();
        $r['clarification_id'] = $clarificationData['response']['clarification_id'];

        // Log in the user
        $r['auth_token'] = OmegaupTestCase::login($contestData['director']);

        // Update answer
        $r['answer'] = $message;

        // Call api
        ClarificationController::apiUpdate($r);
    }
}
