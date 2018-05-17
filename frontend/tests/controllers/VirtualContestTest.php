<?php

/**
 * VirtualContestTest
 *
 * @author SpaceWhite
 */

class VirtualContestTest extends OmegaupTestCase {
    public function testCreateVirtualContest() {
        //create a real contest
        $contestData = ContestsFactory::createContest();

        //create a problem
        $problemData = ProblemsFactory::createProblem();

        //Add problem to contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        //Let assume the original contest has been finished
        ContestsFactory::forceFinish($contestData);

        //Create a new contestant
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        $r = new Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = ContestController::apiCreateVirtual($r);

        //Get generated virtual contest alias
        $virtual_contest_alias = $response['alias'];

        $virtual_contest = ContestsDAO::getByAlias($virtual_contest_alias);

        $original_contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        //Assert virtual contest
        $this->assertEquals($original_contest->contest_id, $virtual_contest->rerun_id);
        $this->assertEquals($original_contest->title, $virtual_contest->title);
        $this->assertEquals($original_contest->description, $virtual_contest->description);
        $this->assertEquals(0, $virtual_contest->public); //Virtual contest must be private
        $this->assertEquals($original_contest->scoreboard, $virtual_contest->scoreboard);
        $this->assertEquals($original_contest->points_decay_factor, $virtual_contest->points_decay_factor);
        $this->assertEquals($original_contest->partial_score, $virtual_contest->partial_score);
        $this->assertEquals($original_contest->submissions_gap, $virtual_contest->submissions_gap);
        $this->assertEquals($original_contest->feedback, $virtual_contest->feedback);
        $this->assertEquals($original_contest->penalty, $virtual_contest->penalty);
        $this->assertEquals($original_contest->penalty_type, $virtual_contest->penalty_type);
        $this->assertEquals($original_contest->penalty_calc_policy, $virtual_contest->penalty_calc_policy);
        $this->assertEquals($original_contest->languages, $virtual_contest->languages);

        //Assert virtual contest problenset problems
        $original_problems = ProblemsetProblemsDAO::getProblems($original_contest->problemset_id);
        $virtual_problems = ProblemsetProblemsDAO::getProblems($virtual_contest->problemset_id);
        //Number of problems must be equal
        $this->assertEquals(count($original_problems), count($virtual_problems));

        //Because we only put one problem in contest we can assert only the first element
        $this->assertEquals($original_problems[0], $virtual_problems[0]);
    }
}
