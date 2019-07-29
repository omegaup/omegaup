<?php

/**
 * Any admin can accept / reject participant requests for a public contest with
 * registration
 *
 * @author juan.pablo@omegaup.com
 */
class ContestRequestsTest extends OmegaupTestCase {
    private function preparePublicContestWithRegistration() : array {
        // create a contest and its admin
        $contestAdmin = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(new ContestParams([
            'contestDirector' => $contestAdmin,
        ]));
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        $adminLogin = self::login($contestAdmin);
        ContestController::apiUpdate(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]));

        $result = [
            'mainAdmin' => $contestAdmin,
            'contestData' => $contestData,
        ];

        return $result;
    }

    private function registerUserForContest(
        Users $contestant,
        Request $contest
    ) : void {
        $contestantLogin = self::login($contestant);

        ContestController::apiRegisterForContest(new Request([
            'contest_alias' => $contest['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]));
    }

    private function assertDefaultParamsInRequest(
        array $userRequest,
        bool $hasRequestResponse = false
    ) : void {
        $this->assertArrayHasKey('username', $userRequest);
        $this->assertArrayHasKey('country', $userRequest);
        $this->assertArrayHasKey('request_time', $userRequest);
        $this->assertArrayHasKey('accepted', $userRequest);
        $this->assertArrayHasKey('last_update', $userRequest);

        if (!$hasRequestResponse) {
            // No one has accepted or rejected the request
            $this->assertArrayNotHasKey('admin', $userRequest);
            $this->assertNull($userRequest['accepted']);
            $this->assertNotEmpty($userRequest['request_time']);
        }
    }

    private function assertParamsInRequest(
        int $numberOfContestants,
        array $contestants,
        array $result,
        Users $mainAdmin,
        array $arbitratedUsers,
        array $acceptedUsers
    ) {
        // Asserting request results
        for ($i = 0; $i < $numberOfContestants; $i++) {
            $hasRequestResponse = false;
            if (in_array($contestants[$i]->username, $arbitratedUsers)) {
                if (in_array($contestants[$i]->username, $acceptedUsers)) {
                    $this->assertEquals(1, $result['users'][$i]['accepted']);
                }
                $this->assertEquals(
                    $result['users'][$i]['admin']['user_id'],
                    $mainAdmin->user_id
                );
                $hasRequestResponse = true;
            }
            $this->assertDefaultParamsInRequest(
                $result['users'][$i],
                $hasRequestResponse
            );
        }
    }

    public function testSimpleContestRequestWithoutResolution() {
        [
            'mainAdmin' => $admin,
            'contestData' => $contestData,
        ] = $this->preparePublicContestWithRegistration();

        // some user asks for contest
        $contestant = UserFactory::createUser();

        $this->registerUserForContest($contestant, $contestData['request']);

        // admin lists registrations
        $adminLogin = self::login($admin);
        $result = ContestController::apiRequests(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
        ]));
        $this->assertEquals(count($result['users']), 1);
        [$userRequest] = $result['users'];

        $this->assertDefaultParamsInRequest($userRequest);
    }

    public function testRejectedAndAcceptedRequestsByTwoDifferentAdmins() {
        [
            'mainAdmin' => $mainAdmin,
            'contestData' => $contestData,
        ] = $this->preparePublicContestWithRegistration();

        // Adding secondary admin
        $secondaryAdminLogin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $secondaryAdminLogin);

        // some users ask for contest
        $contestants = [];
        $numberOfContestants = 4;
        $arbitratedUsers = [];
        $acceptedUsers = [];
        for ($i = 0; $i < $numberOfContestants; $i++) {
            $contestants[$i] = UserFactory::createUser();
            $this->registerUserForContest(
                $contestants[$i],
                $contestData['request']
            );
        }
        [
            $acceptedContestantByMainAdmin,
            $rejectedContestantByMainAdminAndAcceptedByTheSecondOne,
            $rejectedContestantAndThenAcceptedByTheSameAdmin,
            $nonAcceptedNorRejectedContestant,
        ] = $contestants;

        // admin lists registrations
        $adminLogin = self::login($mainAdmin);
        $contestRequest = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
        ]);
        $result = ContestController::apiRequests($contestRequest);
        $this->assertEquals(count($result['users']), $numberOfContestants);

        for ($i = 0; $i < $numberOfContestants; $i++) {
            $this->assertDefaultParamsInRequest($result['users'][$i]);
        }

        // Main admin arbitrates some requests
        $contestRequest['username'] = $acceptedContestantByMainAdmin->username;
        $contestRequest['resolution'] = true;
        ContestController::apiArbitrateRequest($contestRequest);
        $arbitratedUsers[] = $contestRequest['username'];
        $acceptedUsers[] = $contestRequest['username'];

        $contestRequest['username'] =
            $rejectedContestantByMainAdminAndAcceptedByTheSecondOne->username;
        $contestRequest['resolution'] = false;
        ContestController::apiArbitrateRequest($contestRequest);
        $arbitratedUsers[] = $contestRequest['username'];

        $contestRequest['username'] =
            $rejectedContestantAndThenAcceptedByTheSameAdmin->username;
        $contestRequest['resolution'] = false;
        ContestController::apiArbitrateRequest($contestRequest);
        $arbitratedUsers[] = $contestRequest['username'];

        $result = ContestController::apiRequests($contestRequest);

        $this->assertParamsInRequest(
            $numberOfContestants,
            $contestants,
            $result,
            $mainAdmin,
            $arbitratedUsers,
            $acceptedUsers
        );

        // Second round of arbitrate requests
        $contestRequest['resolution'] = true;
        ContestController::apiArbitrateRequest($contestRequest);
        $acceptedUsers[] = $contestRequest['username'];

        // Now we login with the secondary admin
        $adminLogin = self::login($secondaryAdminLogin);
        $ua = $rejectedContestantByMainAdminAndAcceptedByTheSecondOne->username;
        ContestController::apiArbitrateRequest(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
            'username' => $ua,
            'resolution' => true,
        ]));
        $acceptedUsers[] = $ua;

        $result = ContestController::apiRequests(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
        ]));

        $this->assertParamsInRequest(
            $numberOfContestants,
            $contestants,
            $result,
            $mainAdmin,
            $arbitratedUsers,
            $acceptedUsers
        );
    }
}
