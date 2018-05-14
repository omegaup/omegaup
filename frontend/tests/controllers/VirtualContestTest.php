<?php

/**
 * Unit test related to Virtual contest / Ghostmode
 *
 * @author SpaceWhite
 */

class VirtualContestTest extends OmegaupTestCase {
    public function testCreateVirtualContestFromPublic() {
        //create past real contest
        $contestData = ContestsFactory::createContest(new ContestParams([
            'start_time' => strtotime('2000-01-01 01:00:00'),
            'finish_time' => strtotime('2000-01-01 02:00:00')
        ]));

        //Add problem to real contest
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        $login = self::login($contestData['director']);
        $r = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'virtual' => 1,
            'auth_token' => $login->auth_token
        ]);

        $response = ContestController::apiCreate($r);

        //Assert status of new virtual contest
        $this->assertEquals('ok', $response['status']);

        //get the data of the virtual contest
        $virtual_contest = ContestsDAO::getVirtualByContestAndUser($contestData['contest'], $contestData['director']);
        $real_contest = $contestData['contest'];

        $this->assertTrue(ContestsDAO::isVirtual($virtual_contest));

        //Assert delta time in virtual contest is same to real contest
        $delta_virtual = strtotime($virtual_contest->finish_time) - strtotime($virtual_contest->start_time);
        $delta_real= strtotime($real_contest->finish_time) - strtotime($real_contest->start_time);
        $this->assertEquals($delta_real, $delta_virtual);

        //Assert virtual contest rerun_id
        $this->assertEquals($real_contest->contest_id, $virtual_contest->rerun_id);

        //Asset virtual contest problems and real contest problems
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $real_contest->alias,
            'virtual' => 1
        ]);
        $response = ContestController::apiProblems($r);
        $problems = $response['problems'];
        $this->assertEquals($problemData['problem']->problem_id, $problems[0]['problem_id']);
    }

    public function testCreateVirtualContestNotFinished() {
        //create past real contest
        $contestData = ContestsFactory::createContest(new ContestParams([
            'start_time' => Time::get(),
            'finish_time' => Time::get() + 3600 //one hour contest
        ]));
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['virtual'] = 1;

        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        $this->expectException(ForbiddenAccessException::class);

        $response = ContestController::apiCreate($r);
    }

    public function testVirtualContestScoreboardEvent() {
        //Get a problem
        $problemData = ProblemsFactory::createProblem();

        //Get a real contest
        $real_contestData = ContestsFactory::createContest(); //public contest

        //Add the problem to the real contest
        ContestsFactory::addProblemToContest($problemData, $real_contestData);

        //Create real contest contestant
        $contestant = UserFactory::createUser();

        //Create a run
        $runData = RunsFactory::createRun($problemData, $real_contestData, $contestant);

        //Grade the run
        RunsFactory::gradeRun($runData);

        //Create request
        $login = self::login($real_contestData['director']);

        $r = new Request([
            'contest_alias' => $real_contestData['contest']->alias,
            'auth_token' => $login->auth_token
        ]);
        $response = ContestController::apiScoreboardEvents($r);

        //force finish real contest
        ContestsFactory::forceFinish($real_contestData);

        $login = self::login($contestant);

        //Create virtual contest
        $r = new Request([
            'contest_alias' => $real_contestData['contest']->alias,
            'auth_token' => $login->auth_token,
            'virtual' => 1
        ]);
        ContestController::apiCreate($r);

        $virtual_contest = ContestsDAO::getVirtualByContestAndUser($real_contestData['contest'], $contestant);

        //Real contest scoreboard event should appear in virtual contest
        $response = ContestController::apiScoreboardEvents($r);
        $event = $response['events'][0];
        $this->assertEquals($contestant->username, $event['username']);
        $this->assertEquals($problemData['problem']->alias, $event['problem']['alias']);
    }
}
