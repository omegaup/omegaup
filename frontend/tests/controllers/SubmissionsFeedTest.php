<?php

/**
 *
 * @author carlosabcs
 */

class SubmissionsFeedTest extends \OmegaUp\Test\ControllerTestCase {
    public function testSubmissionsFeed() {
        /**
         * Create 3 users, 3 problems and 1 contest.
         *
         * - User2 is going to have private information, so that user's
         * submissions should not be returned by the query.
         *
         * - Problem2 is going to be private, so their submissions won't
         * be returned by the query.
         *
         * - Contest is going to be unfinished so submissions made over its
         * problem, won't be returned by the query.
         */

        $users = [];
        $identities = [];
        ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'isPrivate' => true,
            ])
        );

        $problems = [];
        for ($i = 0; $i < 2; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
            ])
        );

        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problems[1],
            $contestData
        );

        /**
         * Submissions and if they are expected to be returned or not:
         * - User0 => Problem0: RETURNED
         * - User0 => Problem1: NOT RETURNED (contest is still running)
         * - User0 => Problem2: NOT RETURNED (problem private)
         * - User1 => Problem0: NOT RETURNED (user is private)
         */
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problems[1],
            $contestData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $results = \OmegaUp\DAO\Submissions::getLatestSubmissions(
            1,
            100,
            null
        );
        $this->assertCount(1, $results['submissions']);
        $this->assertEquals(1, $results['totalRows']);
        $this->assertEquals(
            $identities[0]->username,
            $results['submissions'][0]['username']
        );
        $this->assertEquals(
            $problems[0]['problem']->alias,
            $results['submissions'][0]['alias']
        );

        // Now add a new submission from User0 to Problem0, but out of the 30-day interval
        $runCreationDate = date_create(date('Y-m-d'));
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-2 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        $results = \OmegaUp\DAO\Submissions::getLatestSubmissions(
            1,
            100,
            null
        );
        $this->assertCount(1, $results['submissions']);
        $this->assertEquals(1, $results['totalRows']);
        $this->assertEquals(
            $identities[0]->username,
            $results['submissions'][0]['username']
        );
        $this->assertEquals(
            $problems[0]['problem']->alias,
            $results['submissions'][0]['alias']
        );
    }

    public function testSubmissionsFeedForUser() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problem,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problem,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $results = \OmegaUp\DAO\Submissions::getLatestSubmissions(
            1,
            100,
            $identity->identity_id
        );
        $this->assertEquals(2, $results['totalRows']);
        $this->assertCount(2, $results['submissions']);
        $this->assertEquals(
            $identity->username,
            $results['submissions'][0]['username']
        );
        $this->assertEquals(
            $identity->username,
            $results['submissions'][1]['username']
        );
    }

    public function testSubmissionsFeedForPrivateUser() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'isPrivate' => true,
            ])
        );
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problem,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problem,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        try {
            $results = \OmegaUp\DAO\Submissions::getLatestSubmissions(
                1,
                100,
                $identity->identity_id
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userInformationIsPrivate', $e->getMessage());
        }
    }
}
