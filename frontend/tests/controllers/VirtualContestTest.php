<?php

/**
 * VirtualContestTest
 *
 * @author SpaceWhite
 */

class VirtualContestTest extends OmegaupTestCase {
    public function testCreateVirtualContest() {
        // create a real contest
        $contestData = ContestsFactory::createContest();

        // Create a problem
        $problemData = ProblemsFactory::createProblem();

        // Add problem to contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Let assume the original contest has been finished
        Time::setTimeForTesting(Time::get() + 3600);

        // Create a new contestant
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        $r = new Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = ContestController::apiCreateVirtual($r);

        // Get generated virtual contest alias
        $virtualContestAlias = $response['alias'];

        $virtualContest = ContestsDAO::getByAlias($virtualContestAlias);

        $originalContest = ContestsDAO::getByAlias($contestData['request']['alias']);

        // Assert virtual contest
        $this->assertEquals($originalContest->contest_id, $virtualContest->rerun_id);
        $this->assertEquals($originalContest->title, $virtualContest->title);
        $this->assertEquals($originalContest->description, $virtualContest->description);
        $this->assertEquals('private', $virtualContest->admission_mode); // Virtual contest must be private
        $this->assertEquals($originalContest->scoreboard, $virtualContest->scoreboard);
        $this->assertEquals($originalContest->points_decay_factor, $virtualContest->points_decay_factor);
        $this->assertEquals($originalContest->partial_score, $virtualContest->partial_score);
        $this->assertEquals($originalContest->submissions_gap, $virtualContest->submissions_gap);
        $this->assertEquals($originalContest->feedback, $virtualContest->feedback);
        $this->assertEquals($originalContest->penalty, $virtualContest->penalty);
        $this->assertEquals($originalContest->penalty_type, $virtualContest->penalty_type);
        $this->assertEquals($originalContest->penalty_calc_policy, $virtualContest->penalty_calc_policy);
        $this->assertEquals($originalContest->languages, $virtualContest->languages);

        // Assert virtual contest problenset problems
        $originalProblems = ProblemsetProblemsDAO::getProblems($originalContest->problemset_id);
        $virtualProblems = ProblemsetProblemsDAO::getProblems($virtualContest->problemset_id);
        // Number of problems must be equal
        $this->assertEquals(count($originalProblems), count($virtualProblems));

        // Because we only put one problem in contest we can assert only the first element
        $this->assertEquals($originalProblems[0], $virtualProblems[0]);
    }

    public function testCreateVirtualContestBeforeTheOriginalEnded() {
        // Create a real contest
        $contestData = ContestsFactory::createContest();

        // Create a new contestant
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        $r = new Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        Time::setTimeForTesting(Time::get() - 100);

        try {
            $response = ContestController::apiCreateVirtual($r);
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'originalContestHasNotEnded');
        }
    }

    public function testVirtualContestRestrictedApiAddProblem() {
        // Create a real contest
        $contestData = ContestsFactory::createContest();

        // Create a problem
        $problemData = ProblemsFactory::createProblem();

        // Create a new contestant
        $contestant = UserFactory::createUser();

        // Lets assume the original contest has been finished
        Time::setTimeForTesting(Time::get() + 3600);

        $login = self::login($contestant);
        $r = new Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = ContestController::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            ContestController::apiAddProblem(new Request([
                'contest_alias' => $virtualContestAlias,
                'problem_alias' => $problemData['problem']->alias,
                'points' => 100,
                'auth_token' => $login->auth_token
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }

    public function testVirtualContestRestrictedApiRemoveProblem() {
        // Create a real contest
        $contestData = ContestsFactory::createContest();

        // Create a problem
        $problemData = ProblemsFactory::createProblem();

        // Add problem to original contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create a new contestant
        $contestant = UserFactory::createUser();

        // Lets assume the original contest has been finished
        Time::setTimeForTesting(Time::get() + 3600);

        $login = self::login($contestant);
        $r = new Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = ContestController::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            ContestController::apiRemoveProblem(new Request([
                'contest_alias' => $virtualContestAlias,
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }

    public function testVirtualContestRestrictedApiUpdate() {
        // Create a real contest
        $contestData = ContestsFactory::createContest();

        // Create a new contestant
        $contestant = UserFactory::createUser();

        // Lets assume the original contest has been finished
        Time::setTimeForTesting(Time::get() + 3600);

        $login = self::login($contestant);
        $r = new Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = ContestController::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            ContestController::apiUpdate(new Request([
                'contest_alias' => $virtualContestAlias,
                'title' => 'testtest',
                'auth_token' => $login->auth_token
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }
}
