<?php

namespace OmegaUp\Test\Factories;

class Clarification {
    /**
     * Creates a clarification in a problem inside a contest
     *
     * @param array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $contestant
     * @param null|string $message
     * @param null|string $receiver
     *
     * @return array{request: \OmegaUp\Request, response: array{clarification_id: int}}
     */
    public static function createClarification(
        array $problemData,
        array $contestData,
        \OmegaUp\DAO\VO\Identities $contestant,
        ?string $message = null,
        ?string $receiver = null
    ): array {
        // Our contestant has to open the contest before sending a clarification
        \OmegaUp\Test\Factories\Contest::openContest(
            $contestData['contest'],
            $contestant
        );

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        // Create the request for our api
        $r = new \OmegaUp\Request([
            'message' => (
                is_null($message) ?
                \OmegaUp\Test\Utils::createRandomString() :
                $message
            ),
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'username' => $receiver,
            'public' => '0',
        ]);

        // Log in our user and set the auth_token properly
        $login = \OmegaUp\Test\ControllerTestCase::login($contestant);
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
     * @param array{request: \OmegaUp\Request, response: array{clarification_id: int}} $clarificationData
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param string $message
     * @param null|string $receiver
     * @param string $public
     */
    public static function answer(
        array $clarificationData,
        array $contestData,
        string $message = 'lol',
        ?string $receiver = null,
        string $public = '0'
    ): void {
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        \OmegaUp\Controllers\Clarification::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'answer' => $message,
            'public' => $public,
            'username' => $receiver,
            'clarification_id' => $clarificationData['response']['clarification_id'],
        ]));
    }
}
