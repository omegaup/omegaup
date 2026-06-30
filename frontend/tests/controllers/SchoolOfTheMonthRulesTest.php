<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests rules of the School of the Month candidates
 */
class SchoolOfTheMonthRulesTest extends \OmegaUp\Test\ControllerTestCase {
    // Returns the last day of the previous month
    private static function previousMonthRunDate(): string {
        $previousMonth = date_create(date('Y-m-d', \OmegaUp\Time::get()));
        $previousMonth->modify('last day of previous month');
        return date_format($previousMonth, 'Y-m-d');
    }

    // Runs the candidates and returns the list of candidates
    private static function calculateAndGetCandidates(string $runDate): array {
        \OmegaUp\Test\Utils::runUpdateRanks($runDate);
        \OmegaUp\Time::setTimeForTesting(strtotime($runDate));
        return \OmegaUp\DAO\SchoolOfTheMonth::getCandidatesToSchoolOfTheMonth();
    }

    // Finds the score of a school within the candidate list
    private static function findSchoolScore(
        array $candidates,
        string $name
    ): ?float {
        foreach ($candidates as $candidate) {
            if ($candidate['name'] === $name) {
                return $candidate['score'];
            }
        }
        return null;
    }

    public function testUniqueProblemsPerSchoolCountedOnce() {
        $schoolWithDuplicate = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $studentA1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $studentA2] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $studentB1] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolWithDuplicate,
            $studentA1
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $schoolWithDuplicate,
            $studentA2
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $studentB1
        );

        $problem0 = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );
        $problem1 = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // schoolWithDuplicate: studentA1=>problem0, studentA2=>problem0, studentA1=>problem1
        // controlSchool:       studentB1=>problem0, studentB1=>problem1
        //
        // problem0 is solved by two students of schoolWithDuplicate but must count once.
        // Expected distinct problems per school: schoolWithDuplicate=2, controlSchool=2
        // The rank should be: tie (same score)
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $studentA1,
            $problem0,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $studentA2,
            $problem0,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $studentA1,
            $problem1,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $studentB1,
            $problem0,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $studentB1,
            $problem1,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $scoreWithDuplicate = self::findSchoolScore(
            $candidates,
            $schoolWithDuplicate['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($scoreWithDuplicate);
        $this->assertNotNull($controlScore);
        // Both schools solved exactly {problem0, problem1}; if the duplicate
        // submission of problem0 were counted twice the scores would differ.
        $this->assertEqualsWithDelta(
            $controlScore,
            $scoreWithDuplicate,
            0.01,
            'A problem solved twice in a school must be counted only once'
        );
    }

    public function testSiteAdminExcludedFromSchoolOfTheMonth() {
        $adminSchool = \OmegaUp\Test\Factories\Schools::createSchool();
        $regularSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();
        ['identity' => $regular] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool($adminSchool, $admin);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $regularSchool,
            $regular
        );

        $problems = [];
        for ($i = 0; $i < 4; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams(
                    ['quality_seal' => true]
                )
            );
        }

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // adminSchool:   admin=>problem0, admin=>problem1, admin=>problem2, admin=>problem3
        // regularSchool: regular=>problem0, regular=>problem1
        //
        // admin is a site-admin so all their submissions must be excluded.
        // Expected candidates: regularSchool=2 distinct problems, adminSchool=not present
        foreach ($problems as $problem) {
            \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
                $admin,
                $problem,
                $runDate
            );
        }
        // The regular user solves only two problems.
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $regular,
            $problems[0],
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $regular,
            $problems[1],
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $this->assertNull(
            self::findSchoolScore($candidates, $adminSchool['request']['name']),
            'The site-admin school must not appear among the candidates'
        );
        $this->assertNotNull(
            self::findSchoolScore(
                $candidates,
                $regularSchool['request']['name']
            ),
            'The regular user school must appear among the candidates'
        );
    }

    public function testForfeitedProblemsExcludedForSchool() {
        $forfeitSchool = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $forfeiter] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $control] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $forfeitSchool,
            $forfeiter
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $control
        );

        $problem0 = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );
        $problem1 = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // forfeitSchool: forfeiter=>problem0, forfeiter=>problem1 (forfeited)
        // controlSchool: control=>problem0, control=>problem1
        //
        // problem1 is forfeited by forfeiter so it must not count for forfeitSchool.
        // Expected distinct problems: forfeitSchool=1, controlSchool=2
        // The rank should be: controlSchool, forfeitSchool

        // The forfeiter views (forfeits) problem1's solution before solving it.
        $login = self::login($forfeiter);
        \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem1['request']['problem_alias'],
            'forfeit_problem' => true,
        ]));

        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $forfeiter,
            $problem0,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $forfeiter,
            $problem1,
            $runDate
        );

        // Control school solves both problems without forfeiting any solution.
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $control,
            $problem0,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $control,
            $problem1,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $forfeitScore = self::findSchoolScore(
            $candidates,
            $forfeitSchool['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($forfeitScore);
        $this->assertNotNull($controlScore);
        // The forfeit school only counts problem0; the control school counts
        // both problems, so its score must be strictly greater.
        $this->assertGreaterThan(
            $forfeitScore,
            $controlScore,
            'A forfeited problem must not be counted for the school'
        );
    }

    public function testProblemAdminOwnerSolvesDoNotCount() {
        $adminSchool = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $owner] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $problemAdmin] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $controlStudent] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Schools::addUserToSchool($adminSchool, $owner);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $adminSchool,
            $problemAdmin
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $adminSchool,
            $student
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $controlStudent
        );

        // Problem owned by $owner with $problemAdmin as an extra admin.
        $ownedProblem = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $owner
        );
        \OmegaUp\Test\Factories\Problem::addAdminUser(
            $ownedProblem,
            $problemAdmin
        );
        // A separate problem with no special relationship to the school.
        $neutralProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // adminSchool:   owner=>ownedProblem (excluded: is owner),
        //                problemAdmin=>ownedProblem (excluded: is admin),
        //                student=>neutralProblem
        // controlSchool: controlStudent=>neutralProblem
        //
        // ownedProblem solved by owner/admin must not count for adminSchool.
        // Expected distinct problems: adminSchool=1, controlSchool=1
        // The rank should be: tie (same score)

        // Owner and admin solve the owned problem: must NOT be counted.
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $owner,
            $ownedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $problemAdmin,
            $ownedProblem,
            $runDate
        );
        // A regular student of the same school solves the neutral problem.
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $neutralProblem,
            $runDate
        );
        // Control school solves only the neutral problem.
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $neutralProblem,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $adminSchoolScore = self::findSchoolScore(
            $candidates,
            $adminSchool['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($adminSchoolScore);
        $this->assertNotNull($controlScore);
        // The owned problem solved by owner/admin must be ignored, so the admin
        // school is left only with the neutral problem, same as the control.
        $this->assertEqualsWithDelta(
            $controlScore,
            $adminSchoolScore,
            0.01,
            'Problems solved by their owner/admin must not be counted'
        );
    }
}
