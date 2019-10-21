<?php

/**
 * A contest might require registration to participate on it.
 *
 * @author alanboy@omegaup.com
 */
class RegisterToContestTest extends OmegaupTestCase {
    // Intro is the page that you see just before joining a contest
    // listing all the contest details.
    public function testIntro() {
        // Create contest
        $contestData = ContestsFactory::createContest();
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestIdentityAdmin);

        // Contest will start in the future:
        $adminLogin = self::login($contestIdentityAdmin);
        $request = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
            'start_time' => \OmegaUp\Time::get() + 60 * 60,
        ]);
        $request['finish_time'] = $request['start_time'] + 60;
        \OmegaUp\Controllers\Contest::apiUpdate($request);

        // Contestant will try to open the contes, this should fail
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        $contestantLogin = self::login($identity);
        $request2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        try {
            $response = \OmegaUp\Controllers\Contest::apiOpen($request2);
            $this->AssertFalse(
                true,
                'User gained access to contest even though its registration needed.'
            );
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $fae) {
            // Expected contestNotStarted exception. Continue.
        }

        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $request2,
            $contestData['contest']
        );
        $this->assertEquals(
            $showIntro,
            \OmegaUp\Controllers\Contest::SHOW_INTRO
        );

        // Contest is going on right now
        $adminLogin = self::login($contestIdentityAdmin);
        $request = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
            'start_time' => \OmegaUp\Time::get() - 1,
        ]);
        $request['finish_time'] = $request['start_time'] + 60;
        \OmegaUp\Controllers\Contest::apiUpdate($request);

        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $request2,
            $contestData['contest']
        );
        $this->assertEquals(
            $showIntro,
            \OmegaUp\Controllers\Contest::SHOW_INTRO
        );

        $contestantLogin = self::login($identity);
        $request2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        // Join this contest
        $response = \OmegaUp\Controllers\Contest::apiOpen($request2);

        // Now that i have joined the contest, i should not see the intro
        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $request2,
            $contestData['contest']
        );
        $this->assertEquals(
            $showIntro,
            !\OmegaUp\Controllers\Contest::SHOW_INTRO
        );
    }

    /**
     * Testing if intro must be shown
     */
    public function testShowIntro() {
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        ContestsFactory::addUser($contestData, $identity);

        // user can now submit to contest
        $contestantLogin = self::login($identity);
        $request = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $request,
            $contestData['contest']
        );

        $this->assertEquals(1, $showIntro);
    }

    //pruebas (público, privado) x (usuario mortal, admin, invitado)
    //pruebas extra para distinguir entre invitado y ya entrado al concurso
    public function testSimpleRegistrationActions() {
        // create a contest and its admin
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['contestDirector' => $contestIdentityAdmin]
            )
        );
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // make it "registrable"
        self::log('Update contest to make it registrable');
        $adminLogin = self::login($contestIdentityAdmin);
        $r1 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]);
        \OmegaUp\Controllers\Contest::apiUpdate($r1);

        // some user asks for contest
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        try {
            $response = \OmegaUp\Controllers\Contest::apiDetails($r2);
            $this->fail(
                'User gained access to contest even though its registration needed.'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        self::log('user registers into contest');
        \OmegaUp\Controllers\Contest::apiRegisterForContest($r2);

        // admin lists registrations
        $adminLogin = self::login($contestIdentityAdmin);
        $r3 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
        ]);
        $result = \OmegaUp\Controllers\Contest::apiRequests($r3);
        $this->assertEquals(sizeof($result['users']), 1);

        self::log('admin rejects registration');
        $r3['username'] = $contestant->username;
        $r3['resolution'] = false;
        \OmegaUp\Controllers\Contest::apiArbitrateRequest($r3);

        // ask for details again, this should fail again
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        try {
            $response = \OmegaUp\Controllers\Contest::apiDetails($r2);
            $this->fail(
                'User gained access to contest even though its registration needed.'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        // admin admits user
        $r3['username'] = $contestant->username;
        $r3['resolution'] = true;
        \OmegaUp\Controllers\Contest::apiArbitrateRequest($r3);

        // user can now submit to contest
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r2);

        \OmegaUp\Controllers\Contest::apiDetails($r2);
    }

    public function testUserCannotSelfApprove() {
        // create a contest and its admin
        $contestData = ContestsFactory::createContest();
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestIdentityAdmin);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // make it "registrable"
        $adminLogin = self::login($contestIdentityAdmin);
        $r1 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]);
        \OmegaUp\Controllers\Contest::apiUpdate($r1);

        // some user asks for contest
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        \OmegaUp\Controllers\Contest::apiRegisterForContest($r2);

        $r3 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
            'username' => $contestant->username,
            'resolution' => true,
        ]);

        try {
            \OmegaUp\Controllers\Contest::apiArbitrateRequest($r3);
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $fae) {
            // Expected. Continue.
        }
    }

    /**
     * Test user cannot join the contest because he doesn't have registered
     * his basic profile information (country, state and school)
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testUserNotAllowedJoinTheContest() {
        // create a contest and its admin
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(new ContestParams([
            'contestDirector' => $contestIdentityAdmin,
        ]));

        $adminLogin = self::login($contestIdentityAdmin);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'basic_information' => 1,
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Contestant will try to open the contest, it should fail
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        $contestantLogin = self::login($identity);

        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]));
    }

    /**
     * Test user joins the contest
     */
    public function testUserAllowedJoinTheContest() {
        // Create a school
        $school = SchoolsFactory::createSchool();

        // create a contest and its admin
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(new ContestParams([
            'contestDirector' => $contestIdentityAdmin,
        ]));

        // Updates contest, with basic information needed
        $adminLogin = self::login($contestIdentityAdmin);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'basic_information' => 1,
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Contestant will try to open the contes, this should fail
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Updates contestant, with basic information
        $contestantLogin = self::login($identity);
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $contestantLogin->auth_token,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_id' => $school['school']->school_id
        ]));

        $contest = \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]));

        $this->assertEquals($contest['status'], 'ok');
    }

    /**
     * Testing identities can access to contest, but there are invited
     * and no invited
     */
    public function testIdentitiesInvitedAndNoInvitedToContest() {
        // Creating 5 identities, and inviting them to the contest
        $numberOfInvitedContestants = 5;
        $invitedContestants = [];
        $invitedContestantIdentities = [];
        for ($i = 0; $i < $numberOfInvitedContestants; $i++) {
            [
                'user' => $invitedContestants[],
                'identity' => $invitedContestantIdentities[],
            ] = UserFactory::createUser();
        }
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'public']
            )
        );
        foreach ($invitedContestantIdentities as $contestant) {
            ContestsFactory::addUser($contestData, $contestant);
        }
        // Creating 3 identities without an invitation to join the contest
        $numberOfNotInvitedContestants = 3;
        $uninvitedContestants = [];
        $uninvitedContestantIdentities = [];
        for ($i = 0; $i < $numberOfNotInvitedContestants; $i++) {
            [
                'user' => $uninvitedContestants[],
                'identity' => $uninvitedContestantIdentities[],
            ] = UserFactory::createUser();
        }

        // All identities join the contest
        foreach ($uninvitedContestantIdentities as $contestant) {
            $contestantLogin = self::login($contestant);

            \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $contestantLogin->auth_token,
            ]));
        }

        $problemsetIdentities = \OmegaUp\DAO\ProblemsetIdentities::getIdentitiesByProblemset(
            $contestData['contest']->problemset_id
        );

        $this->assertEquals(
            count(
                $problemsetIdentities
            ),
            ($numberOfInvitedContestants + $numberOfNotInvitedContestants)
        );

        $this->assertIdentitiesAreInCorrectList(
            $invitedContestants,
            true /*isInvited*/,
            $problemsetIdentities
        );

        $this->assertIdentitiesAreInCorrectList(
            $uninvitedContestants,
            false /*isInvited*/,
            $problemsetIdentities
        );
    }

    private function assertIdentitiesAreInCorrectList(
        $contestants,
        bool $isInvited,
        $identities
    ) {
        foreach ($contestants as $contestant) {
            $this->assertArrayContainsWithPredicate(
                $identities,
                function ($identity) use ($contestant, $isInvited) {
                    return $identity['user_id'] == $contestant->user_id &&
                    $identity['is_invited'] == $isInvited;
                }
            );
        }
    }
}
