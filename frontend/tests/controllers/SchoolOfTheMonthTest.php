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
        array $schoolsData,
        ?string $runDate = null
    ): void {
        if (is_null($runDate)) {
            $previousMonth = date_create(date('Y-m-d'));
            date_add(
                $previousMonth,
                date_interval_create_from_date_string(
                    '-1 month'
                )
            );
            $runDate = date_format($previousMonth, 'Y-m-d');
        }

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

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[3],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[4],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );

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
            strtotime($runDate)
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            strtotime($runDate)
        );
    }

    public function testCalculateSchoolsOfMonth() {
        $schoolsData = [
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
        ];
        $today = date('Y-m-d', \OmegaUp\Time::get());

        $previousMonth = date_create($today);
        date_add(
            $previousMonth,
            date_interval_create_from_date_string(
                '-1 month'
            )
        );
        $runDate = date_format($previousMonth, 'Y-m-d');

        self::setUpSchoolsRuns($schoolsData);
        \OmegaUp\Test\Utils::runUpdateRanks($runDate);

        \OmegaUp\Time::setTimeForTesting(strtotime($runDate));
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
            'time' => $today,
            'ranking' => 1
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);

        \OmegaUp\Test\Utils::runUpdateRanks($runDate);
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
            'ranking' => 1
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);

        \OmegaUp\Test\Utils::runUpdateRanks($runDate);
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
            'time' => $today,
            'ranking' => 4
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);
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
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
        ];
        $today = date('Y-m-d', \OmegaUp\Time::get());

        $previousMonth = date_create($today);
        date_add(
            $previousMonth,
            date_interval_create_from_date_string(
                '-1 month'
            )
        );
        $runDate = date_format($previousMonth, 'Y-m-d');

        self::setUpSchoolsRuns($schoolsData);
        \OmegaUp\Test\Utils::runUpdateRanks($runDate);

        // API should return school1
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

        // Now do the same but for the current month
        $nextMonth = date_create($today);
        date_add(
            $nextMonth,
            date_interval_create_from_date_string(
                '+1 month'
            )
        );
        $nextMonthDate = date_format($nextMonth, 'Y-m-d');

        self::setUpSchoolsRuns($schoolsData, $today);
        \OmegaUp\Test\Utils::runUpdateRanks($today);

        $results = \OmegaUp\DAO\SchoolOfTheMonth::getMonthlyList(
            $nextMonthDate
        );
        $this->assertCount(count($schoolsData) - 1, $results);
        $this->assertEquals(
            $schoolsData[0]['school']->name,
            $results[0]['name']
        );

        // Finally verify that both best schools of each month are retrieved
        $results = \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth();
        $this->assertCount(2, $results);
        $this->assertEquals(
            $results[0]['school_id'],
            $schoolsData[0]['school']->school_id
        );
        $this->assertEquals(
            $results[1]['school_id'],
            $schoolsData[1]['school']->school_id
        );
    }

    public function testApiSelectSchoolOfTheMonth() {
        [
            'user' => $mentor,
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $schoolsData = [
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
        ];

        $today = date('Y-m-d', \OmegaUp\Time::get());

        $date = date_create($today);
        date_add(
            $date,
            date_interval_create_from_date_string(
                '+1 month'
            )
        );
        $runDate = date_format($date, 'Y-m-15');

        self::setUpSchoolsRuns($schoolsData, $runDate);
        \OmegaUp\Test\Utils::runUpdateRanks($runDate);
        \OmegaUp\Time::setTimeForTesting(strtotime($runDate));

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
        $lastDayOfMonth = $date;
        $lastDayOfMonth->modify('last day of this month');
        \OmegaUp\Time::setTimeForTesting($lastDayOfMonth->getTimestamp());

        $result = \OmegaUp\Controllers\School::apiSelectSchoolOfTheMonth(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $schoolsData[2]['school']->school_id
        ]));
        $this->assertEquals('ok', $result['status']);

        $results = \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth();
        $this->assertCount(3, $results);
        $this->assertEquals(
            $schoolsData[2]['school']->name,
            $results[0]['name']
        );
    }
}
