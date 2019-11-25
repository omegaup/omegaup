<?php

/**
 *
 * @author joemmanuel
 */

class SchoolRankTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     *  Helper to create runs with users inside a school
     *
     */
    private function createRunsWithSchool(&$schoolsData) {
        $users = [];
        $identities = [];
        for ($i = 0; $i < 5; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[0]);
        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[1]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $identities[2]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $identities[3]);

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0.5, 'PA');

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identities[4]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
    }

    /**
     * Basic test for school rank
     *
     */
    public function testSchoolRankPositive() {
        $currentTime = \OmegaUp\Time::get();
        $pastMonthTime = strtotime(
            'first day of last month',
            \OmegaUp\Time::get()
        );

        \OmegaUp\Time::setTimeForTesting($pastMonthTime);

        // Prepare setup, 5 users, 2 in school #1, 1 in school #2,
        // 1 in school #2 but PA, 1 with no school for the past month
        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool()
        ];

        $this->createRunsWithSchool($schoolsData);

        \OmegaUp\Time::setTimeForTesting($currentTime);

        // Prepare setup, 5 users, 2 in school #1, 1 in school #2,
        // 1 in school #2 but PA, 1 with no school for the current time
        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool()
        ];

        $this->createRunsWithSchool($schoolsData);

        // Call API
        ['user' => $rankViewer, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $rankViewerLogin = self::login($identity);
        $response = \OmegaUp\Controllers\School::apiRank(new \OmegaUp\Request([
            'auth_token' => $rankViewerLogin->auth_token
        ]));

        // Only runs of this month should be considered for the rank
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(2, count($response['rank']));
        $this->assertEquals(
            $schoolsData[0]['request']['name'],
            $response['rank'][0]['name']
        );
        $this->assertEquals(2, $response['rank'][0]['distinct_users']);

        $this->assertEquals(
            $schoolsData[1]['request']['name'],
            $response['rank'][1]['name']
        );
        $this->assertEquals(1, $response['rank'][1]['distinct_users']);

        $cachedResponse = \OmegaUp\Controllers\School::apiRank(new \OmegaUp\Request([
            'auth_token' => $rankViewerLogin->auth_token
        ]));

        $this->assertEquals($response, $cachedResponse);
    }

    /**
     * Test School Rank API with start_time, end_time params
     */
    public function testSchoolRankApiWithTimes() {
        // Prepare setup, 5 users, 2 in school #1, 1 in school #2,
        // 1 in school #2 but PA, 1 with no school.
        $schoolsData = [SchoolsFactory::createSchool(), SchoolsFactory::createSchool()];

        ['user' => $rankViewer, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $rankViewerLogin = self::login($identity);
        $originalResponse = \OmegaUp\Controllers\School::apiRank(new \OmegaUp\Request([
            'auth_token' => $rankViewerLogin->auth_token,
        ]));

        $this->createRunsWithSchool($schoolsData);

        $start_time = strtotime('-1 day');
        $end_time = strtotime('+1 day');

        $response = \OmegaUp\Controllers\School::apiRank(new \OmegaUp\Request([
            'auth_token' => $rankViewerLogin->auth_token,
            'start_time' => $start_time,
            'finish_time' => $end_time
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(4, count($response['rank']));
        $this->assertEquals(2, $response['rank'][0]['distinct_users']);
        $this->assertEquals(1, $response['rank'][2]['distinct_users']);

        $cachedResponse = \OmegaUp\Controllers\School::apiRank(new \OmegaUp\Request([
            'auth_token' => $rankViewerLogin->auth_token,
        ]));

        // start_time/finish_time path should not be the one cached..
        $this->assertEquals($originalResponse, $cachedResponse);
        $this->assertNotEquals($response, $cachedResponse);
    }

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

        /**
         * One month ago:
         * user2 => problem0, problem1 = 2 distinct problems
         * user0 => problem0 (the user has solved it the last month and also so it has
         *                      been solved by user3 this month) = 0 distinct problems
         * user1 => problem2 = 1 distinct problem
         *
         * Total expected count: 3
         */
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

        /**
         * This month:
         * user1 => problem1 (he has already solved it, doesn't count)
         *
         * Total expected count: 0, the month/year won't be retrieved as no distinct
         * problems are going to be found
         */
        $currentMonth = intval(date_create($today)->format('m'));

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $response = \OmegaUp\Controllers\School::apiMonthlySolvedProblemsCount(new \OmegaUp\Request([
            'school_id' => $schoolData['school']->school_id,
            'months_count' => 3,
        ]))['distinct_problems_solved'];
        $this->assertCount(2, $response); // just two months (first and second)
    }
}
