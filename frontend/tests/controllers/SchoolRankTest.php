<?php

/**
 *
 * @author joemmanuel
 */

class SchoolRankTest extends OmegaupTestCase {
    /**
     *  Helper to create runs with users inside a school
     *
     */
    private function createRunsWithSchool(&$schoolsData) {
        $users = [];
        $identities = [];
        for ($i = 0; $i < 5; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = UserFactory::createUser();
        }

        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[0]);
        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[1]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $identities[2]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $identities[3]);

        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $identities[0]
        );
        RunsFactory::gradeRun($runData);

        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $identities[1]
        );
        RunsFactory::gradeRun($runData);

        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $identities[2]
        );
        RunsFactory::gradeRun($runData);

        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $identities[3]
        );
        RunsFactory::gradeRun($runData, 0.5, 'PA');

        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $identities[4]
        );
        RunsFactory::gradeRun($runData);
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
        ['user' => $rankViewer, 'identity' => $identity] = UserFactory::createUser();
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

        ['user' => $rankViewer, 'identity' => $identity] = UserFactory::createUser();
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
}
