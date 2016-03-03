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
        $request = new Request();
        $request['contest_alias'] = $contestData['request']['alias'];
        $request['auth_token'] = $this->login($contestAdmin);
        $request['start_time'] = Utils::GetPhpUnixTimestamp() + 60 * 60;
        $request['finish_time'] = $request['start_time'] + 60;
        ContestController::apiUpdate($request);

        // Contestant will try to open the contes, this should fail
        $contestant = UserFactory::createUser();

        $request2 = new Request();
        $request2['contest_alias'] = $contestData['request']['alias'];
        $request2['auth_token'] = $this->login($contestant);

        try {
            $response = ContestController::apiOpen($request2);
            $this->AssertFalse(true, 'User gained access to contest even though its registration needed.');
        } catch (PreconditionFailedException $fae) {
            // Expected contestNotStarted exception. Continue.
        }

        $show_intro = ContestController::showContestIntro($request2);
        $this->assertEquals($show_intro, ContestController::SHOW_INTRO);

        // Contest is going on right now
        $request = new Request();
        $request['contest_alias'] = $contestData['request']['alias'];
        $request['auth_token'] = $this->login($contestAdmin);
        $request['start_time'] = Utils::GetPhpUnixTimestamp() - 1 ;
        $request['finish_time'] = $request['start_time'] + 60;
        ContestController::apiUpdate($request);

        $show_intro = ContestController::showContestIntro($request2);
        $this->assertEquals($show_intro, ContestController::SHOW_INTRO);

        $request2 = new Request();
        $request2['contest_alias'] = $contestData['request']['alias'];
        $request2['auth_token'] = $this->login($contestant);

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
        $contestData = ContestsFactory::createContest(null, 1 /*public*/);
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        // make it "registrable"
        self::log('Udate contest to make it registrable');
        $r1 = new Request();
        $r1['contest_alias'] = $contestData['request']['alias'];
        $r1['contestant_must_register'] = true;
        $r1['auth_token'] = $this->login($contestAdmin);
        ContestController::apiUpdate($r1);

        // some user asks for contest
        $contestant = UserFactory::createUser();
        $r2 = new Request();
        $r2['contest_alias'] = $contestData['request']['alias'];
        $r2['auth_token'] = $this->login($contestant);
        try {
            $response = ContestController::apiDetails($r2);
            $this->AssertFalse(true, 'User gained access to contest even though its registration needed.');
        } catch (ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        self::log('user registers, into contest');
        ContestController::apiRegisterForContest($r2);

        // admin lists registrations
        $r3 = new Request();
        $r3['contest_alias'] = $contestData['request']['alias'];
        $r3['auth_token'] = $this->login($contestAdmin);
        $result = ContestController::apiRequests($r3);
        $this->assertEquals(sizeof($result['users']), 1);

        self::log('amin rejects registration');
        $r3['username'] = $contestant->username;
        $r3['resolution'] = false;
        ContestController::apiArbitrateRequest($r3);

        // ask for details again, this should fail again
        $r2 = new Request();
        $r2['contest_alias'] = $contestData['request']['alias'];
        $r2['auth_token'] = $this->login($contestant);
        try {
            $response = ContestController::apiDetails($r2);
            $this->AssertFalse(true);
        } catch (ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        // admin admits user
        $r3['username'] = $contestant->username;
        $r3['resolution'] = true;
        ContestController::apiArbitrateRequest($r3);

        // user can now submit to contest
        $r2 = new Request();
        $r2['contest_alias'] = $contestData['request']['alias'];
        $r2['auth_token'] = $this->login($contestant);

        // Explicitly join contest
        ContestController::apiOpen($r2);

        ContestController::apiDetails($r2);
    }
}
