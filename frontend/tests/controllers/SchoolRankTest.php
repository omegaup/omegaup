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
        for ($i = 0; $i < 5; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        SchoolsFactory::addUserToSchool($schoolData, $identities[0]);
        SchoolsFactory::addUserToSchool($schoolData, $identities[1]);
        SchoolsFactory::addUserToSchool($schoolData, $identities[2]);
        SchoolsFactory::addUserToSchool($schoolData, $identities[3]);

        $problems = [];
        for ($i = 0; $i < 10; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        $today = date('Y-m-d');
        $runCreationDate = date_create($today);

        // Two months ago, problems 0 and 1 have been solved by users 0 and 1 respectively.
        // User 1 also tried problem 2 but failed. So 2 problems in total are expected
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-2 month'
            )
        );
        $firstMonth = intval($runCreationDate->format('m'));
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
            'months_number' => 3,
        ]));
        $this->assertCount(1, $response);
        $this->assertEquals($response[0]['month'], $firstMonth);
        $this->assertEquals($response[0]['distinct_problems'], 2);

        // One month ago, user 3 solved problem 0, 1 and 2 (3 problems in total)
        // user 0 solves again problem 0, but doesn't count as it has been already solved
        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $secondMonth = intval($runCreationDate->format('m'));
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[3]
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
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runCreationDate)
        );

        $response = \OmegaUp\Controllers\School::apiMonthlySolvedProblemsCount(new \OmegaUp\Request([
            'school_id' => $schoolData['school']->school_id,
            'months_number' => 3,
        ]));
        $this->assertCount(2, $response);
        $this->assertEquals($response[0]['month'], $firstMonth);
        $this->assertEquals($response[0]['distinct_problems'], 2);
        $this->assertEquals($response[1]['month'], $secondMonth);
        $this->assertEquals($response[1]['distinct_problems'], 3);

        $currentMonth = intval(date_create($today)->format('m'));
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $response = \OmegaUp\Controllers\School::apiMonthlySolvedProblemsCount(new \OmegaUp\Request([
            'school_id' => $schoolData['school']->school_id,
            'months_number' => 3,
        ]));
        print_r($response);
    }
}
