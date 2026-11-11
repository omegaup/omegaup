<?php

class RunsVersionChangeDAOTest extends \OmegaUp\Test\ControllerTestCase {
    private const SUBMIT_DELAY = 60;

    private function createContestSubmission(): array {
        $now = \OmegaUp\Time::get();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => new \OmegaUp\Timestamp(
                    $now - self::SUBMIT_DELAY * 60
                ),
                'finishTime' => new \OmegaUp\Timestamp($now + 60 * 60),
                'penaltyType' => 'contest_start',
            ])
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problem = new \OmegaUp\DAO\VO\Problems([
            'acl_id' => \OmegaUp\DAO\ACLs::create(new \OmegaUp\DAO\VO\ACLs([
                'owner_id' => $contestData['userDirector']->user_id,
            ])),
            'alias' => 'penalty-version',
            'title' => 'Penalty version',
            'commit' => sha1('commit-1'),
            'current_version' => sha1('version-1'),
            'creation_date' => new \OmegaUp\Timestamp($now),
        ]);
        \OmegaUp\DAO\Problems::create($problem);

        $problemsetProblem = new \OmegaUp\DAO\VO\ProblemsetProblems([
            'problemset_id' => $contestData['contest']->problemset_id,
            'problem_id' => $problem->problem_id,
            'commit' => $problem->commit,
            'version' => $problem->current_version,
            'points' => 100,
        ]);
        \OmegaUp\DAO\ProblemsetProblems::create($problemsetProblem);

        $submission = new \OmegaUp\DAO\VO\Submissions([
            'identity_id' => $identity->identity_id,
            'problem_id' => $problem->problem_id,
            'problemset_id' => $contestData['contest']->problemset_id,
            'guid' => md5(uniqid()),
            'language' => 'py3',
            'time' => new \OmegaUp\Timestamp($now),
            'status' => 'ready',
            'verdict' => 'PA',
            'submit_delay' => self::SUBMIT_DELAY,
            'type' => 'normal',
        ]);
        \OmegaUp\DAO\Submissions::create($submission);

        $run = new \OmegaUp\DAO\VO\Runs([
            'submission_id' => $submission->submission_id,
            'version' => $problem->current_version,
            'commit' => $problem->commit,
            'status' => 'ready',
            'verdict' => 'PA',
            'penalty' => self::SUBMIT_DELAY,
            'time' => $submission->time,
            'score' => 0.5,
            'contest_score' => 50,
        ]);
        \OmegaUp\DAO\Runs::create($run);
        $submission->current_run_id = $run->run_id;
        \OmegaUp\DAO\Submissions::update($submission);

        $later = $now + 3 * 60 * 60;
        \OmegaUp\Time::setTimeForTesting($later);
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            "SET TIMESTAMP = {$later};"
        );

        return [
            'problem' => $problem,
            'problemsetProblem' => $problemsetProblem,
            'submission' => $submission,
            'run' => $run,
        ];
    }

    public function testCreateRunsForVersionKeepsPenaltyAndTime() {
        [
            'problem' => $problem,
            'submission' => $submission,
        ] = $this->createContestSubmission();

        $problem->commit = sha1('commit-2');
        $problem->current_version = sha1('version-2');
        \OmegaUp\DAO\Problems::update($problem);
        \OmegaUp\DAO\Runs::createRunsForVersion($problem);

        $newRuns = \OmegaUp\DAO\Runs::getNewRunsForVersion($problem);
        $this->assertCount(1, $newRuns);
        $newRun = \OmegaUp\DAO\Runs::getByPK(intval($newRuns[0]->run_id));
        $this->assertNotNull($newRun);
        $this->assertSame($submission->submission_id, $newRun->submission_id);
        $this->assertSame(self::SUBMIT_DELAY, $newRun->penalty);
        $this->assertSame($submission->time->time, $newRun->time->time);
    }

    public function testProblemsetVersionChangeKeepsPenaltyAndTime() {
        [
            'problemsetProblem' => $problemsetProblem,
            'submission' => $submission,
            'run' => $run,
        ] = $this->createContestSubmission();

        $problemsetProblem->commit = sha1('commit-2');
        $problemsetProblem->version = sha1('version-2');
        \OmegaUp\DAO\ProblemsetProblems::update($problemsetProblem);
        \OmegaUp\DAO\ProblemsetProblems::updateProblemsetProblemSubmissions(
            $problemsetProblem
        );

        $updatedSubmission = \OmegaUp\DAO\Submissions::getByPK(
            intval($submission->submission_id)
        );
        $this->assertNotNull($updatedSubmission);
        $this->assertNotSame($run->run_id, $updatedSubmission->current_run_id);
        $newRun = \OmegaUp\DAO\Runs::getByPK(
            intval($updatedSubmission->current_run_id)
        );
        $this->assertNotNull($newRun);
        $this->assertSame($problemsetProblem->version, $newRun->version);
        $this->assertSame(self::SUBMIT_DELAY, $newRun->penalty);
        $this->assertSame($submission->time->time, $newRun->time->time);

        $scoreboardRuns = \OmegaUp\DAO\Runs::getProblemsetRuns(
            intval($problemsetProblem->problemset_id)
        );
        $this->assertCount(1, $scoreboardRuns);
        $this->assertSame(self::SUBMIT_DELAY, $scoreboardRuns[0]['penalty']);
    }
}
