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
        $contestData = ContestsFactory::createContest(null, 1 /*public*/);
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        // Contest will start in the future:
        $request = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestAdmin),
            'start_time' => Utils::GetPhpUnixTimestamp() + 60 * 60,
        ));
        $request['finish_time'] = $request['start_time'] + 60;
        ContestController::apiUpdate($request);

        // Contestant will try to open the contes, this should fail
        $contestant = UserFactory::createUser();

        $request2 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestant),
        ));

        try {
            $response = ContestController::apiOpen($request2);
            $this->AssertFalse(true, 'User gained access to contest even though its registration needed.');
        } catch (PreconditionFailedException $fae) {
            // Expected contestNotStarted exception. Continue.
        }

        $show_intro = ContestController::showContestIntro($request2);
        $this->assertEquals($show_intro, ContestController::SHOW_INTRO);

        // Contest is going on right now
        $request = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestAdmin),
            'start_time' => Utils::GetPhpUnixTimestamp() - 1,
        ));
        $request['finish_time'] = $request['start_time'] + 60;
        ContestController::apiUpdate($request);

        $show_intro = ContestController::showContestIntro($request2);
        $this->assertEquals($show_intro, ContestController::SHOW_INTRO);

        $request2 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestant),
        ));

        // Join this contest
        $response = ContestController::apiOpen($request2);

        // Now that i have joined the contest, i should not see the intro
        $show_intro = ContestController::showContestIntro($request2);
        $this->assertEquals($show_intro, !ContestController::SHOW_INTRO);
    }

    //pruebas (público, privado) x (usuario mortal, admin, invitado)
    //pruebas extra para distinguir entre invitado y ya entrado al concurso
    public function testSimpleRegistrationActions() {
        // create a contest and its admin
        $contestAdmin = UserFactory::createUser();
        $contestData = ContestsFactory::createContest(null, 1 /*public*/, $contestAdmin);

        // make it "registrable"
        self::log('Update contest to make it registrable');
        $r1 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'contestant_must_register' => true,
            'auth_token' => self::login($contestAdmin),
        ));
        ContestController::apiUpdate($r1);

        // some user asks for contest
        $contestant = UserFactory::createUser();
        $r2 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestant),
        ));
        try {
            $response = ContestController::apiDetails($r2);
            $this->fail('User gained access to contest even though its registration needed.');
        } catch (ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        self::log('user registers into contest');
        ContestController::apiRegisterForContest($r2);

        // admin lists registrations
        $r3 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestAdmin),
        ));
        $result = ContestController::apiRequests($r3);
        $this->assertEquals(sizeof($result['users']), 1);

        self::log('admin rejects registration');
        $r3['username'] = $contestant->username;
        $r3['resolution'] = false;
        ContestController::apiArbitrateRequest($r3);

        // ask for details again, this should fail again
        $r2 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestant),
        ));
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
        $r2 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestant),
        ));

        // Explicitly join contest
        ContestController::apiOpen($r2);

        ContestController::apiDetails($r2);
    }

    public function testUserCannotSelfApprove() {
        // create a contest and its admin
        $contestData = ContestsFactory::createContest(null, 1 /*public*/);
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        // make it "registrable"
        $r1 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'contestant_must_register' => true,
            'auth_token' => self::login($contestAdmin),
        ));
        ContestController::apiUpdate($r1);

        // some user asks for contest
        $contestant = UserFactory::createUser();
        $login = self::login($contestant);
        $r2 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestant),
        ));
        ContestController::apiRegisterForContest($r2);

        $r3 = new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => self::login($contestant),
            'username' => $contestant->username,
            'resolution' => true,
        ));

        try {
            ContestController::apiArbitrateRequest($r3);
            $this->fail('Should have thrown an exception');
        } catch (ForbiddenAccessException $fae) {
            // Expected. Continue.
        }
    }
}
