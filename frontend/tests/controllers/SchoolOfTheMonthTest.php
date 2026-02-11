<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests API/DAO functions for getting and selecting the
 * Schools of the Month.
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
            $previousMonth->modify('last day of previous month');
            $runDate = date_format($previousMonth, 'Y-m-d');
        }

        $users = [];
        $identities = [];
        for ($i = 0; $i < 4; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
        }

        $problems = [];
        for ($i = 0; $i < 6; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'quality_seal' => true,
                ])
            );
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
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[1],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[2],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[3],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[4],
            $identities[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[3]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
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
            new \OmegaUp\Timestamp(strtotime($runDate))
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problems[0],
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($runDate))
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
        $previousMonth->modify('last day of previous month');
        $runDate = date_format($previousMonth, 'Y-m-d');

        self::setUpSchoolsRuns($schoolsData);
        \OmegaUp\Test\Utils::runUpdateRanks($runDate);

        \OmegaUp\Time::setTimeForTesting(strtotime($runDate));
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
        $this->assertCount(3, $schools);
        $this->assertSame(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertSame(
            $schoolsData[0]['request']['name'],
            $schools[1]['name']
        );
        $this->assertSame(
            $schoolsData[2]['request']['name'],
            $schools[2]['name']
        );
        $this->assertGreaterThan(
            $schools[1]['score'],
            $schools[0]['score']
        );
        $this->assertGreaterThan(
            $schools[2]['score'],
            $schools[1]['score']
        );

        // Now insert one of the Schools as SchoolOfTheMonth, it should not be retrieved
        // again by the DAO as it has already been selected current year.

        $previousMonth->modify('last day of previous month');
        $today = date_format($previousMonth, 'Y-m-01');
        $newSchool = new \OmegaUp\DAO\VO\SchoolOfTheMonth([
            'school_id' => $schoolsData[2]['school']->school_id,
            'time' => $today,
            'ranking' => 1,
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);

        \OmegaUp\Test\Utils::runUpdateRanks($runDate);
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
        $this->assertCount(2, $schools);
        $this->assertSame(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertSame(
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
        $this->assertSame(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertSame(
            $schoolsData[0]['request']['name'],
            $schools[1]['name']
        );
        $this->assertSame(
            $schoolsData[2]['request']['name'],
            $schools[2]['name']
        );
        \OmegaUp\DAO\SchoolOfTheMonth::delete($newSchool);

        // Now insert an school already selected but neither rank 1 neither selected by a mentor.
        // It should be retrieved.
        $newSchool = new \OmegaUp\DAO\VO\SchoolOfTheMonth([
            'school_id' => $schoolsData[2]['school']->school_id,
            'time' => $today,
            'ranking' => 4,
        ]);
        \OmegaUp\DAO\SchoolOfTheMonth::create($newSchool);
        $schools = \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
        $this->assertCount(3, $schools);
        $this->assertSame(
            $schoolsData[1]['request']['name'],
            $schools[0]['name']
        );
        $this->assertSame(
            $schoolsData[0]['request']['name'],
            $schools[1]['name']
        );
        $this->assertSame(
            $schoolsData[2]['request']['name'],
            $schools[2]['name']
        );
        \OmegaUp\DAO\SchoolOfTheMonth::delete($newSchool);
    }

    public function testGetSchoolOfTheMonth() {
        $schoolsData = [
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
            \OmegaUp\Test\Factories\Schools::createSchool(),
        ];
        $today = date('Y-m-d', \OmegaUp\Time::get());

        $previousMonth = date_create($today);
        $previousMonth->modify('last day of previous month');
        $runDate = date_format($previousMonth, 'Y-m-d');

        self::setUpSchoolsRuns($schoolsData);
        \OmegaUp\Test\Utils::runUpdateRanks($runDate);

        // API should return school1
        $response = \OmegaUp\Controllers\School::getSchoolOfTheMonth();
        $this->assertSame(
            $schoolsData[1]['school']->name,
            $response['schoolinfo']['name']
        );

        $results = \OmegaUp\DAO\SchoolOfTheMonth::getMonthlyList(
            date('Y-m-d', \OmegaUp\Time::get())
        );
        $this->assertCount(count($schoolsData), $results);
        $this->assertSame(
            $schoolsData[1]['school']->name,
            $results[0]['name']
        );

        // Now do the same but for the current month
        $nextMonth = date_create($today);
        $nextMonth->modify('last day of next month');
        $nextMonthDate = date_format($nextMonth, 'Y-m-d');

        self::setUpSchoolsRuns($schoolsData, $today);
        \OmegaUp\Test\Utils::runUpdateRanks($today);

        $results = \OmegaUp\DAO\SchoolOfTheMonth::getMonthlyList(
            $nextMonthDate
        );
        $this->assertCount(count($schoolsData) - 1, $results);
        $this->assertSame(
            $schoolsData[0]['school']->name,
            $results[0]['name']
        );

        // School of the month (of the next month) should not be retrieved because
        // it is a calculation for the future.
        $results = \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth();
        $this->assertCount(1, $results);
        $this->assertSame(
            $results[0]['school_id'],
            $schoolsData[1]['school']->school_id
        );

        $nextMonth = date_create($nextMonthDate);
        $nextMonth->modify('last day of next month');
        $nextMonthDate = date_format($nextMonth, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($nextMonthDate));

        // Finally verify that both best schools of each month are retrieved
        $results = \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth();
        $this->assertCount(2, $results);
        $this->assertSame(
            $results[0]['school_id'],
            $schoolsData[0]['school']->school_id
        );
        $this->assertSame(
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
        $date->modify('last day of next month');
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
            $this->assertSame(
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
        $this->assertSame('ok', $result['status']);

        $nextMonth = $lastDayOfMonth;
        $nextMonth->modify('last day of next month');
        $nextMonthDate = date_format($nextMonth, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($nextMonthDate));

        $results = \OmegaUp\DAO\SchoolOfTheMonth::getSchoolsOfTheMonth();
        $this->assertCount(1, $results);
        $this->assertSame(
            $schoolsData[2]['school']->name,
            $results[0]['name']
        );
    }
}
