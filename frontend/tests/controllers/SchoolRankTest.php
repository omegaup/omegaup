<?php

/**
 *
 * @author joemmanuel
 */

class SchoolRankTest extends \OmegaUp\Test\ControllerTestCase {
    public function testApiMonthlySolvedProblemsCount() {
        $schoolData = SchoolsFactory::createSchool();

        $users = [];
        $identities = [];
        for ($i = 0; $i < 3; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        SchoolsFactory::addUserToSchool($schoolData, $identities[0]);
        SchoolsFactory::addUserToSchool($schoolData, $identities[1]);
        SchoolsFactory::addUserToSchool($schoolData, $identities[2]);

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
            strtotime($runCreationDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'WA');
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        // TODO(https://github.com/omegaup/omegaup/issues/3438): Remove this.
        /*
        $response = \OmegaUp\Controllers\School::apiMonthlySolvedProblemsCount(new \OmegaUp\Request([
            'school_id' => $schoolData['school']->school_id,
            'months_count' => 3,
        ]))['distinct_problems_solved'];
        $this->assertCount(1, $response); // one month, the first one
        $this->assertEquals($response[0]['month'], $firstMonthNumber);
        $this->assertEquals(
            $response[0]['count'],
            $firstMonthExpectedCount
        );
        */

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
            strtotime($runCreationDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        // TODO(https://github.com/omegaup/omegaup/issues/3438): Remove this.
        /*
        $response = \OmegaUp\Controllers\School::apiMonthlySolvedProblemsCount(new \OmegaUp\Request([
            'school_id' => $schoolData['school']->school_id,
            'months_count' => 3,
        ]))['distinct_problems_solved'];
        $this->assertCount(2, $response); // two months (first and second)
        $this->assertEquals($response[0]['month'], $firstMonthNumber);
        $this->assertEquals(
            $response[0]['count'],
            $firstMonthExpectedCount
        );
        $this->assertEquals($response[1]['month'], $secondMonthNumber);
        $this->assertEquals(
            $response[1]['count'],
            $secondMonthExpectedCount
        );
        */

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

        // TODO(https://github.com/omegaup/omegaup/issues/3438): Remove this.
        /*
        $response = \OmegaUp\Controllers\School::apiMonthlySolvedProblemsCount(new \OmegaUp\Request([
            'school_id' => $schoolData['school']->school_id,
            'months_count' => 3,
        ]))['distinct_problems_solved'];
        $this->assertCount(2, $response); // just two months (first and second)
        */
    }

    /**
     * Tests School::apiUsers() in order to retrieve all
     * users from school with their number of solved problems,
     * created problems and organized contests
     */
    public function testSchoolApiUsers() {
        /** Creates 3 users:
         * user1 solves 0 problems, organizes 0 contests and creates 2 problems
         * user2 solves 2 problems, organizes 0 contest and creates 1 problems
         * user3 solves 1 problem, organizes 1 contests and creates 0 problem
         */
        $schoolData = SchoolsFactory::createSchool();
        $users = [];
        $identities = [];
        for ($i = 0; $i < 2; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        // User3 automatically organizes a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $identities[] = $contestData['director'];

        SchoolsFactory::addUserToSchool($schoolData, $identities[0]);
        SchoolsFactory::addUserToSchool($schoolData, $identities[1]);
        SchoolsFactory::addUserToSchool($schoolData, $identities[2]);

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

        $result = \OmegaUp\Controllers\School::apiUsers(new \OmegaUp\Request([
            'school_id' => $schoolData['school']->school_id
        ]));

        $this->assertCount(3, $result['users']);

        $this->assertEquals(
            $identities[0]->username,
            $result['users'][0]['username']
        );
        $this->assertEquals(0, $result['users'][0]['solved_problems']);
        $this->assertEquals(0, $result['users'][0]['organized_contests']);
        $this->assertEquals(2, $result['users'][0]['created_problems']);

        $this->assertEquals(
            $identities[1]->username,
            $result['users'][1]['username']
        );
        $this->assertEquals(2, $result['users'][1]['solved_problems']);
        $this->assertEquals(0, $result['users'][1]['organized_contests']);
        $this->assertEquals(1, $result['users'][1]['created_problems']);

        $this->assertEquals(
            $identities[2]->username,
            $result['users'][2]['username']
        );
        $this->assertEquals(1, $result['users'][2]['solved_problems']);
        $this->assertEquals(1, $result['users'][2]['organized_contests']);
        $this->assertEquals(0, $result['users'][2]['created_problems']);
    }

    /**
     * Tests the historical rank of schools, based on the current
     * criteria: distinct active users and distinct problems solved
     */
    public function testSchoolRank() {
        // Three schools:
        // School0: two distinct problems solved
        // School1: three distinct problems solved
        // School2: two distinct problems solved
        // => School0 and School2 must have same rank and score
        // => School1 must have a better (lower) rank than School0 and School2

        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool()
        ];

        $users = [];
        $identities = [];
        for ($i = 0; $i < 4; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        $problemsData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[0]);
        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[1]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $identities[2]);
        SchoolsFactory::addUserToSchool($schoolsData[2], $identities[3]);

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

        $this->assertEquals($school0->score, $school0->score);
        $this->assertEquals($school0->ranking, $school2->ranking);
        $this->assertGreaterThan($school1->ranking, $school0->ranking);
        $this->assertGreaterThan($school0->score, $school1->score);

        // Test apiRank
        $response = \OmegaUp\Controllers\School::apiRank(new \OmegaUp\Request([
            'offset' => 1,
            'rowcount' => 100,
        ]));
        $this->assertGreaterThanOrEqual(3, count($response['rank']));
        $this->assertGreaterThanOrEqual(
            $response['rank'][0]['ranking'],
            $response['rank'][1]['ranking']
        ); /** is sorted */
    }
}
