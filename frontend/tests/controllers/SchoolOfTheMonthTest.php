<?php

/**
 * Tests API/DAO functions for getting and selecting the
 * Schools of the Month.
 *
 * @author carlosabcs
 */
class SchoolOfTheMonthTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * @param $schoolsData list<array{creator: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{status: string, school_id: int}, school: \OmegaUp\DAO\VO\Schools}>
     */
    private static function setUpSchoolsRuns(
        array $schoolsData
    ): void {
        $users = [];
        $identities = [];
        for ($i = 0; $i < 4; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        $problems = [];
        for ($i = 0; $i < 6; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Prepare setup:
        // school0: user0=>problem0, user1=>problem1
        // school1: user2=>problem0, user2=>problem1, user2=>problem2, user2=>problem3, user2=>problem4
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

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[3],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[4],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Now solve more problems:
        // user0=>problem1, user1=>problem0 the school0 might be the first one now, but, as the problems
        // solved are counted just once, the ranking is not affected
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
    }

    public function testCalculateSchoolsOfMonth() {
        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
        ];
        $today = date('Y-m-d', \OmegaUp\Time::get());
        $previousMonth = date_create($today);
        date_add(
            $previousMonth,
            date_interval_create_from_date_string(
                '-1 month'
            )
        );

        self::setUpSchoolsRuns($schoolsData);

        \OmegaUp\Test\Utils::runUpdateRanks();
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
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
            'time' => $previousMonth->format('Y-m-01'),
            'rank' => 1
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);
        \OmegaUp\Test\Utils::runUpdateRanks();
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
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
        \OmegaUp\Test\Utils::runUpdateRanks();
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
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
            'time' => $previousMonth->format('Y-m-01'),
            'rank' => 4
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);
        \OmegaUp\Test\Utils::runUpdateRanks();
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
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
    }

    public function testGetSchoolOfTheMonth() {
        \OmegaUp\Test\Utils::cleanUpDB();

        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
        ];

        self::setUpSchoolsRuns($schoolsData);
        return;

        $nextMonth = date_create(date('Y-m-d'));
        date_add(
            $nextMonth,
            date_interval_create_from_date_string(
                '+1 month'
            )
        );

        \OmegaUp\Test\Utils::runUpdateRanks();

        // API should return school1
        \OmegaUp\Time::setTimeForTesting($nextMonth->getTimestamp());
        $response = \OmegaUp\Controllers\School::getSchoolOfTheMonth();
        $this->assertEquals(
            $schoolsData[1]['school']->name,
            $response['schoolinfo']['name']
        );

        $results = \OmegaUp\DAO\SchoolOfTheMonth::getMonthlyList(
            date('Y-m-d', \OmegaUp\Time::get())
        );
        $this->assertCount(count($schoolsData), $results);
        $this->assertEquals(
            $schoolsData[1]['school']->name,
            $results[0]['name']
        );
    }

    public function testApiSelectSchoolOfTheMonth() {
        [
            'user' => $mentor,
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $runDate = date_create(date('Y-m-15'));
        date_add(
            $runDate,
            date_interval_create_from_date_string(
                '-6 month'
            )
        );

        $schoolsData = [
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
            SchoolsFactory::createSchool(),
        ];

        \OmegaUp\Time::setTimeForTesting($runDate->getTimestamp());
        self::setUpSchoolsRuns($schoolsData);

        // Mentor's login
        $login = self::login($mentorIdentity);

        try {
            \OmegaUp\Controllers\School::apiSelectSchoolOfTheMonth(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'school_id' => $schoolsData[0]['school']->school_id,
            ]));
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals(
                'schoolOfTheMonthIsNotInPeriodToBeChosen',
                $e->getMessage()
            );
        }

        // Today must be the end of the month
        $lastDayOfMonth = $runDate;
        $lastDayOfMonth->modify('last day of this month');
        \OmegaUp\Time::setTimeForTesting($lastDayOfMonth->getTimestamp());

        // TODO(https://github.com/omegaup/omegaup/issues/3438): Remove this.
        return;

        $result = \OmegaUp\Controllers\School::apiSelectSchoolOfTheMonth(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $schoolsData[0]['school']->school_id
        ]));
        $this->assertEquals('ok', $result['status']);

        $results = \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth();
        // Should contain exactly two schools of the month, the one from previous test and
        // the one selected on the current one.
        $this->assertCount(2, $results);
        $this->assertEquals(
            $schoolsData[0]['school']->name,
            $results[1]['name']
        );
        $this->assertGreaterThan($results[1]['time'], $results[0]['time']);
    }
}
