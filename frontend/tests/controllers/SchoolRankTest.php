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
}
