<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class SchoolRankTest extends \OmegaUp\Test\ControllerTestCase {
    public function testGetMonthlySolvedProblemsCount() {
        $schoolData = \OmegaUp\Test\Factories\Schools::createSchool();

        $users = [];
        $identities = [];
        for ($i = 0; $i < 3; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolData,
            $identities[2]
        );

        $problems = [];
        for ($i = 0; $i < 3; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        $today = date('Y-m-d');
        $runCreationDate = date_create($today);

        /**
         * Two months ago:
         * user0 tries problem0 but fails
         * user0 => problem0 = 1 distinct problem
         * user1 => problem1 = 1 distinct problem
         * user1 tries (but fails) problem2
         *
         * Total expected count: 2
         */
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-2 month'
            )
        );
        $firstMonthNumber = intval($runCreationDate->format('m'));
        $firstMonthExpectedCount = 2;

        $runCreationDate = date_format($runCreationDate, 'Y-m-d');

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'WA');
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'WA');
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        \OmegaUp\Test\Utils::runUpdateRanks();

        $response = \OmegaUp\Controllers\School::getMonthlySolvedProblemsCount(
            $schoolData['school']->school_id
        );
        $this->assertCount(1, $response); // one month, the first one
        $this->assertSame($response[0]['month'], $firstMonthNumber);
        $this->assertSame(
            $response[0]['problems_solved'],
            $firstMonthExpectedCount
        );

        // One month ago:
        // user2 => problem0, problem1 = 2 distinct problems
        // user0 => problem0 (the user has solved it the last month and also so it has
        //                      been solved by user3 this month) = 0 distinct problems
        // user1 => problem2 = 1 distinct problem
        //
        // Total expected count: 3
        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $secondMonthNumber = intval($runCreationDate->format('m'));
        $secondMonthExpectedCount = 3;

        $runCreationDate = date_format($runCreationDate, 'Y-m-d');

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runCreationDate))
        );

        \OmegaUp\Test\Utils::runUpdateRanks();

        $response = \OmegaUp\Controllers\School::getMonthlySolvedProblemsCount(
            $schoolData['school']->school_id
        );
        $this->assertCount(2, $response); // two months (first and second)
        $this->assertSame($response[0]['month'], $firstMonthNumber);
        $this->assertSame(
            $response[0]['problems_solved'],
            $firstMonthExpectedCount
        );
        $this->assertSame($response[1]['month'], $secondMonthNumber);
        $this->assertSame(
            $response[1]['problems_solved'],
            $secondMonthExpectedCount
        );

        // This month:
        // user1 => problem1 (he has already solved it, doesn't count)
        //
        // Total expected count: 0, the month/year won't be retrieved as no distinct
        // problems are going to be found
        $currentMonth = intval(date_create($today)->format('m'));

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        \OmegaUp\Test\Utils::runUpdateRanks();

        $response = \OmegaUp\Controllers\School::getMonthlySolvedProblemsCount(
            $schoolData['school']->school_id
        );
        $this->assertCount(2, $response); // just two months (first and second)
    }

    /**
     * Tests School::getUsers() in order to retrieve all
     * users from school with their number of solved problems,
     * created problems and organized contests
     */
    public function testSchoolGetUsers() {
        /** Creates 3 users:
         * user1 solves 0 problems, organizes 0 contests and creates 2 problems
         * user2 solves 2 problems, organizes 0 contest and creates 1 problems
         * user3 solves 1 problem, organizes 1 contests and creates 0 problem
         */
        $schoolData = \OmegaUp\Test\Factories\Schools::createSchool();
        $users = [];
        $identities = [];
        for ($i = 0; $i < 2; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        // User3 automatically organizes a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['languages' => ['c11-gcc']]
            )
        );
        $identities[] = $contestData['director'];

        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolData,
            $identities[2]
        );

        // User 1
        $login = self::login($identities[0]);
        $problems = [];
        for ($i = 0; $i < 2; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                $identities[0],
                $login
            );
        }

        // User 2
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($identities[1]);
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $identities[1],
            $login
        );

        // User 3
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $results = \OmegaUp\Controllers\School::getUsers(
            $schoolData['school']->school_id
        );

        $this->assertCount(3, $results);

        $this->assertSame(
            $identities[0]->username,
            $results[0]['username']
        );
        $this->assertSame(0, $results[0]['solved_problems']);
        $this->assertSame(0, $results[0]['organized_contests']);
        $this->assertSame(2, $results[0]['created_problems']);

        $this->assertSame(
            $identities[1]->username,
            $results[1]['username']
        );
        $this->assertSame(2, $results[1]['solved_problems']);
        $this->assertSame(0, $results[1]['organized_contests']);
        $this->assertSame(1, $results[1]['created_problems']);

        $this->assertSame(
            $identities[2]->username,
            $results[2]['username']
        );
        $this->assertSame(1, $results[2]['solved_problems']);
        $this->assertSame(1, $results[2]['organized_contests']);
        $this->assertSame(0, $results[2]['created_problems']);
    }

    /**
     * Tests the historical rank of schools, based on the current with
     * schools scoring 0
     * criteria: distinct active users and distinct problems solved
     * with schools with a score of 0 without appearing in the ranking
     */
    public function testSchoolRank() {
        // Four schools:
        // School0: two distinct problems solved
        // School1: three distinct problems solved
        // School2: two distinct problems solved
        // School3: without solving problem
        // => School0 and School2 must have same rank and score
        // => School1 must have a better (lower) rank than School0 and School2
        // => School3 must have a better (lower) score than School0
        // => NUmber of schools in the ranking must be 3

        $schoolsData = [
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool()
        ];

        $users = [];
        $identities = [];
        for ($i = 0; $i < 5; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        $problemsData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolsData[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolsData[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolsData[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolsData[2],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolsData[3],
            $identities[4]
        );

        // School 0
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[1],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // School 1
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[0],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[2],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // School 2
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[0],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[1],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        $school0 = \Omegaup\DAO\Schools::getByPK(
            $schoolsData[0]['school']->school_id
        );
        $school1 = \Omegaup\DAO\Schools::getByPK(
            $schoolsData[1]['school']->school_id
        );
        $school2 = \Omegaup\DAO\Schools::getByPK(
            $schoolsData[2]['school']->school_id
        );
        $school3 = \Omegaup\DAO\Schools::getByPK(
            $schoolsData[3]['school']->school_id
        );

        $this->assertSame($school0->score, $school0->score);
        $this->assertSame($school0->ranking, $school2->ranking);
        $this->assertGreaterThan($school1->ranking, $school0->ranking);
        $this->assertGreaterThan($school0->score, $school1->score);
        $this->assertGreaterThan($school3->score, $school0->score);

        // Test apiRank
        $results = \OmegaUp\DAO\Schools::getRank(1, 100);
        $this->assertEquals(3, count($results['rank']));
        $this->assertGreaterThanOrEqual(
            $results['rank'][0]['ranking'],
            $results['rank'][1]['ranking']
        ); /** is sorted */
    }
}
