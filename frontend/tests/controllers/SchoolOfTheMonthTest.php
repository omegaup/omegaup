<?php

/**
 * Tests API/DAO functions for getting and selecting the
 * Schools of the Month.
 *
 * @author carlosabcs
 */
class SchoolOfTheMonthTest extends \OmegaUp\Test\ControllerTestCase {
    private static function setUpSchoolsRuns($schoolsData) {
        $previousMonth = date_create(date('Y-m-d'));
        date_add(
            $previousMonth,
            date_interval_create_from_date_string(
                '-1 month'
            )
        );
        $previousMonth = date_format($previousMonth, 'Y-m-d');

        $users = [];
        $identities = [];
        for ($i = 0; $i < 4; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        $problems = [];
        for ($i = 0; $i < 3; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Prepare setup:
        // school0: user0=>problem0, user1=>problem1
        // school1: user2=>problem0, user2=>problem1, user2=>problem2
        // school2: user3=>problem0
        // The rank should be: school1, school0, school2
        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[0]);
        SchoolsFactory::addUserToSchool($schoolsData[0], $identities[1]);
        SchoolsFactory::addUserToSchool($schoolsData[1], $identities[2]);
        SchoolsFactory::addUserToSchool($schoolsData[2], $identities[3]);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($previousMonth)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($previousMonth)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($previousMonth)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($previousMonth)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($previousMonth)
        );

        // Setting p.accepted value
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Now solve more problems:
        // user0=>problem1, user1=>problem0 the school0 might be the first one now, but, as the problems
        // solved are counted just once, the ranking is not affected
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($previousMonth)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($previousMonth)
        );

        // Setting p.accepted value
        \OmegaUp\Test\Utils::runUpdateRanks();
    }

    public function testCalculateSchoolsOfMonth() {
        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
        ];
        $today = date('Y-m-d', \OmegaUp\Time::get());

        self::setUpSchoolsRuns($schoolsData);

        $schools = \OmegaUp\DAO\SchoolOfTheMonth::calculateSchoolsOfMonthByGivenDate(
            $today
        );
        $this->assertCount(3, $schools);
        $this->assertEquals(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertEquals(
            $schoolsData[0]['request']['name'],
            $schools[1]['name']
        );
        $this->assertEquals(
            $schoolsData[2]['request']['name'],
            $schools[2]['name']
        );

        // Now insert one of the Schools as SchoolOfTheMonth, it should not be retrieved
        // again by the DAO as it has already been selected current year.
        $newSchool = new \OmegaUp\DAO\VO\SchoolOfTheMonth([
            'school_id' => $schoolsData[2]['school']->school_id,
            'time' => $today,
            'rank' => 1
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::calculateSchoolsOfMonthByGivenDate(
            $today
        );
        $this->assertCount(2, $schools);
        $this->assertEquals(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertEquals(
            $schoolsData[0]['request']['name'],
            $schools[1]['name']
        );
        \OmegaUp\DAO\SchoolOfTheMonth::delete($newSchool);

        // Now insert one but with date from year 2017, it should be retrieved as the school
        // has not been selected on the current year
        $newSchool = new \OmegaUp\DAO\VO\SchoolOfTheMonth([
            'school_id' => $schoolsData[2]['school']->school_id,
            'time' => '2017-01-01',
            'rank' => 1
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::calculateSchoolsOfMonthByGivenDate(
            $today
        );
        $this->assertCount(3, $schools);
        $this->assertEquals(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertEquals(
            $schoolsData[0]['request']['name'],
            $schools[1]['name']
        );
        $this->assertEquals(
            $schoolsData[2]['request']['name'],
            $schools[2]['name']
        );
        \OmegaUp\DAO\SchoolOfTheMonth::delete($newSchool);

        // Now insert an school already selected but neither rank 1 neither selected by a mentor.
        // It should be retrieved.
        $newSchool = new \OmegaUp\DAO\VO\SchoolOfTheMonth([
            'school_id' => $schoolsData[2]['school']->school_id,
            'time' => $today,
            'rank' => 4
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::calculateSchoolsOfMonthByGivenDate(
            $today
        );
        $this->assertCount(3, $schools);
        $this->assertEquals(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertEquals(
            $schoolsData[0]['request']['name'],
            $schools[1]['name']
        );
        $this->assertEquals(
            $schoolsData[2]['request']['name'],
            $schools[2]['name']
        );
    }

    public function testApiSchoolOfTheMonth() {
        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
        ];

        self::setUpSchoolsRuns($schoolsData);

        // API should return school1
        $response = \OmegaUp\Controllers\School::apiSchoolOfTheMonth(
            new \OmegaUp\Request()
        );
        $this->assertEquals(
            $schoolsData[1]['school']->name,
            $response['schoolinfo']['name']
        );
    }
}
