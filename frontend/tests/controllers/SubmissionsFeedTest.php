<?php

/**
 *
 * @author carlosabcs
 */

class SubmissionsFeedTest extends \OmegaUp\Test\ControllerTestCase {
    public function testSubmissionsFeed() {
        \OmegaUp\Test\Utils::cleanupDB();
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
                'visibility' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
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

        $results = \OmegaUp\Controllers\Submission::apiLatestSubmissions(
            new \OmegaUp\Request()
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
            strtotime($runCreationDate)
        );

        $results = \OmegaUp\Controllers\Submission::apiLatestSubmissions(
            new \OmegaUp\Request()
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
}
