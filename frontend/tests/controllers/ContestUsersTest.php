<?php

/**
 * Description of ContestUsersTest
 *
 * @author joemmanuel
 */

class ContestUsersTest extends OmegaupTestCase {
    public function testContestUsersValid() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Create 10 users
        $n = 10;
        $users = [];
        for ($i = 0; $i < $n; $i++) {
            // Create a user
            $users[$i] = UserFactory::createUser();

            // Add it to the contest
            ContestsFactory::addUser($contestData, $users[$i]);
        }

        // Create a n+1 user who will just join to the contest without being
        // added via API. For public contests, by entering to the contest, the user should be in
        // the list of contest's users.
        $nonRegisteredUser = UserFactory::createUser();
        ContestsFactory::openContest($contestData, $nonRegisteredUser);

        // Log in with the admin of the contest
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call API
        $response = ContestController::apiUsers($r);

        // Check that we have n+1 users
        $this->assertEquals($n+1, count($response['users']));
    }

    public function testContestActivityReport() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        $user = UserFactory::createUser();
        ContestsFactory::openContest($contestData, $user);

        $userLogin = self::login($user);
        ContestController::apiDetails(new Request([
            'auth_token' => $userLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        // Call API
        $directorLogin = self::login($contestData['director']);
        $response = ContestController::apiActivityReport(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        // Check that we have entries in the log.
        $this->assertEquals(1, count($response['events']));
        $this->assertEquals($user->username, $response['events'][0]['username']);
        $this->assertEquals(0, $response['events'][0]['ip']);
        $this->assertEquals('open', $response['events'][0]['event']['name']);
    }

    public function testContestParticipantsReport() {
        // Get a contest
        $contestData = ContestsFactory::createContest(new ContestParams(['requests_user_information' => 'optional']));

        for ($i = 0; $i < 3; $i++) {
            // Create users
            $user[$i] = UserFactory::createUser();

            // Add users to our private contest
            ContestsFactory::addUser($contestData, $user[$i]);
        }

        $userLogin = self::login($user[0]);

        $contestDetails =
            ContestController::getContestDetailsForSmartyAndShouldShowintro(
                new Request([
                    'auth_token' => $userLogin->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            );

        // Explicitly join contest
        ContestController::apiOpen(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $userLogin->auth_token,
            'privacy_git_object_id' =>
                $contestDetails['smartyProperties']['privacyStatement']['gitObjectId'],
            'statement_type' =>
                $contestDetails['smartyProperties']['privacyStatement']['statementType'],
            'share_user_information' => 1,
        ]));

        // Call API
        $directorLogin = self::login($contestData['director']);

        $r = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token
        ]);

        $response = ContestController::apiContestants($r);

        // There are three participants in the current contest
        $this->assertEquals(3, count($response['contestants']));

        // But only one participant has accepted share user information
        $this->assertEquals(1, self::usersSharingUserInformation($response['contestants']));

        $userLogin = self::login($user[1]);

        // Explicitly join contest
        ContestController::apiOpen(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $userLogin->auth_token,
            'privacy_git_object_id' =>
                $contestDetails['smartyProperties']['privacyStatement']['gitObjectId'],
            'statement_type' =>
                $contestDetails['smartyProperties']['privacyStatement']['statementType'],
            'share_user_information' => 0,
        ]));

        $response = ContestController::apiContestants($r);

        // The number of participants sharing their information still remains the same
        $this->assertEquals(1, self::usersSharingUserInformation($response['contestants']));

        $userLogin = self::login($user[2]);

        // Explicitly join contest
        ContestController::apiOpen(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $userLogin->auth_token,
            'privacy_git_object_id' =>
                $contestDetails['smartyProperties']['privacyStatement']['gitObjectId'],
            'statement_type' =>
                $contestDetails['smartyProperties']['privacyStatement']['statementType'],
            'share_user_information' => 1,
        ]));

        $response = ContestController::apiContestants($r);

        // Now there are two participants sharing their information
        $this->assertEquals(2, self::usersSharingUserInformation($response['contestants']));
    }

    private static function usersSharingUserInformation($contestants) {
        $numberOfContestants = 0;
        foreach ($contestants as $contestant) {
            if ($contestant['email']) {
                $numberOfContestants++;
            }
        }
        return $numberOfContestants;
    }

    public function testContestCanBeSeenByUnloggedUsers() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        $contestDetails =
            ContestController::getContestDetailsForSmartyAndShouldShowintro(
                new Request([
                    'contest_alias' => $contestData['request']['alias'],
                ])
            );

        $this->assertEquals(1, $contestDetails['shouldShowIntro']);
    }
}
