<?php

/**
 *
 * @author joemmanuel
 */

class SchoolRankTest extends OmegaupTestCase {
    /**
     * Basic test for school rank
     *
     */
    public function testSchoolRankPositive() {
        // Prepare setup, 5 users, 2 in school #1, 1 in school #2,
        // 1 in school #2 but PA, 1 with no school.
        $schoolsData = [SchoolsFactory::createSchool(), SchoolsFactory::createSchool()];

        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $users[] = UserFactory::createUser();
        }

        SchoolsFactory::addUserToSchool($schoolsData[0], $users[0]);
        SchoolsFactory::addUserToSchool($schoolsData[0], $users[1]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $users[2]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $users[3]);

        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem($problemData, $users[0]);
        RunsFactory::gradeRun($runData);

        $runData = RunsFactory::createRunToProblem($problemData, $users[1]);
        RunsFactory::gradeRun($runData);

        $runData = RunsFactory::createRunToProblem($problemData, $users[2]);
        RunsFactory::gradeRun($runData);

        $runData = RunsFactory::createRunToProblem($problemData, $users[3]);
        RunsFactory::gradeRun($runData, 'PA', 0.5);

        $runData = RunsFactory::createRunToProblem($problemData, $users[4]);
        RunsFactory::gradeRun($runData);

        // Call API
        $rankViewer = UserFactory::createUser();
        $rankViewerLogin = self::login($rankViewer);
        $response = SchoolController::apiRank(new Request([
            'auth_token' => $rankViewerLogin->auth_token
        ]));

        $this->assertEquals(2, count($response['rank']));
        $this->assertEquals($schoolsData[0]['request']['name'], $response['rank'][0]['name']);
        $this->assertEquals(2, $response['rank'][0]['distinct_users']);

        $this->assertEquals($schoolsData[1]['request']['name'], $response['rank'][1]['name']);
        $this->assertEquals(1, $response['rank'][1]['distinct_users']);
    }
}
