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
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        // Contest will start in the future:
        $adminLogin = self::login($contestAdmin);
        $request = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
            'start_time' => Utils::GetPhpUnixTimestamp() + 60 * 60,
        ]);
        $request['finish_time'] = $request['start_time'] + 60;
        ContestController::apiUpdate($request);

        // Contestant will try to open the contes, this should fail
        $contestant = UserFactory::createUser();

        $contestantLogin = self::login($contestant);
        $request2 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        try {
            $response = ContestController::apiOpen($request2);
            $this->AssertFalse(true, 'User gained access to contest even though its registration needed.');
        } catch (PreconditionFailedException $fae) {
            // Expected contestNotStarted exception. Continue.
        }

        $show_intro = ContestController::showContestIntro($request2)['shouldShowIntro'];
        $this->assertEquals($show_intro, ContestController::SHOW_INTRO);

        // Contest is going on right now
        $adminLogin = self::login($contestAdmin);
        $request = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
            'start_time' => Utils::GetPhpUnixTimestamp() - 1,
        ]);
        $request['finish_time'] = $request['start_time'] + 60;
        ContestController::apiUpdate($request);

        $show_intro = ContestController::showContestIntro($request2)['shouldShowIntro'];
        $this->assertEquals($show_intro, ContestController::SHOW_INTRO);

        $contestantLogin = self::login($contestant);
        $request2 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        // Join this contest
        $response = ContestController::apiOpen($request2);

        // Now that i have joined the contest, i should not see the intro
        $show_intro = ContestController::showContestIntro($request2)['shouldShowIntro'];
        $this->assertEquals($show_intro, !ContestController::SHOW_INTRO);
    }

    /**
     * Testing if intro must be shown
     */
    public function testShowIntro() {
        $contestant = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        ContestsFactory::addUser($contestData, $contestant);

        // user can now submit to contest
        $contestantLogin = self::login($contestant);
        $request = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        $show_intro = ContestController::showContestIntro($request)['shouldShowIntro'];

        $this->assertEquals(1, $show_intro);
    }

    //pruebas (público, privado) x (usuario mortal, admin, invitado)
    //pruebas extra para distinguir entre invitado y ya entrado al concurso
    public function testSimpleRegistrationActions() {
        // create a contest and its admin
        $contestAdmin = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(new ContestParams(['contestDirector' => $contestAdmin]));
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // make it "registrable"
        self::log('Update contest to make it registrable');
        $adminLogin = self::login($contestAdmin);
        $r1 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]);
        ContestController::apiUpdate($r1);

        // some user asks for contest
        $contestant = UserFactory::createUser();
        $contestantLogin = self::login($contestant);
        $r2 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        try {
            $response = ContestController::apiDetails($r2);
            $this->fail('User gained access to contest even though its registration needed.');
        } catch (ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        self::log('user registers into contest');
        ContestController::apiRegisterForContest($r2);

        // admin lists registrations
        $adminLogin = self::login($contestAdmin);
        $r3 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
        ]);
        $result = ContestController::apiRequests($r3);
        $this->assertEquals(sizeof($result['users']), 1);

        self::log('admin rejects registration');
        $r3['username'] = $contestant->username;
        $r3['resolution'] = false;
        ContestController::apiArbitrateRequest($r3);

        // ask for details again, this should fail again
        $contestantLogin = self::login($contestant);
        $r2 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        try {
            $response = ContestController::apiDetails($r2);
            $this->fail('User gained access to contest even though its registration needed.');
        } catch (ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        // admin admits user
        $r3['username'] = $contestant->username;
        $r3['resolution'] = true;
        ContestController::apiArbitrateRequest($r3);

        // user can now submit to contest
        $contestantLogin = self::login($contestant);
        $r2 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        // Explicitly join contest
        ContestController::apiOpen($r2);

        ContestController::apiDetails($r2);
    }

    public function testUserCannotSelfApprove() {
        // create a contest and its admin
        $contestData = ContestsFactory::createContest();
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // make it "registrable"
        $adminLogin = self::login($contestAdmin);
        $r1 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]);
        ContestController::apiUpdate($r1);

        // some user asks for contest
        $contestant = UserFactory::createUser();
        $contestantLogin = self::login($contestant);
        $r2 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        ContestController::apiRegisterForContest($r2);

        $r3 = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
            'username' => $contestant->username,
            'resolution' => true,
        ]);

        try {
            ContestController::apiArbitrateRequest($r3);
            $this->fail('Should have thrown an exception');
        } catch (ForbiddenAccessException $fae) {
            // Expected. Continue.
        }
    }

    /**
     * Test user cannot join the contest because he doesn't have registered
     * his basic profile information (country, state and school)
     *
     * @expectedException ForbiddenAccessException
     */
    public function testUserNotAllowedJoinTheContest() {
        // create a contest and its admin
        $contestAdmin = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(new ContestParams([
            'contestDirector' => $contestAdmin,
        ]));

        $adminLogin = self::login($contestAdmin);
        ContestController::apiUpdate(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'basic_information' => 1,
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Contestant will try to open the contest, it should fail
        $contestant = UserFactory::createUser();

        $contestantLogin = self::login($contestant);

        ContestController::apiOpen(new Request([
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
        $contestAdmin = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(new ContestParams([
            'contestDirector' => $contestAdmin,
        ]));

        // Updates contest, with basic information needed
        $adminLogin = self::login($contestAdmin);
        ContestController::apiUpdate(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'basic_information' => 1,
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Contestant will try to open the contes, this should fail
        $contestant = UserFactory::createUser();

        // Updates contestant, with basic information
        $contestantLogin = self::login($contestant);
        $states = StatesDAO::getByCountry('MX');
        UserController::apiUpdate(new Request([
            'auth_token' => $contestantLogin->auth_token,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_id' => $school['school']->school_id
        ]));

        $contest = ContestController::apiOpen(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]));

        $this->assertEquals($contest['status'], 'ok');
    }
}
