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

    public function testProblemSolvedBySchoolInPreviousMonth() {
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $oldstudent] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $newstudent] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $controlStudent] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool($school, $oldstudent);
        \OmegaUp\Test\Factories\Schools::addUserToSchool($school, $newstudent);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $controlStudent
        );

        $sharedProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );
        $exclusiveProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();
        $priorDate = date(
            'Y-m-15',
            strtotime('first day of -2 months', \OmegaUp\Time::get())
        );

        // Prepare setup:
        // school:        oldstudent=>sharedProblem (prior month, outside window)
        //                newstudentr=>sharedProblem (current window, but school already has it)
        //                newstudent=>exclusiveProblem (current window)
        // controlSchool: controlStudent=>sharedProblem (current window)
        //                controlStudent=>exclusiveProblem (current window)
        //
        // sharedProblem was already solved by $school before the evaluation window so
        // newstudent's AC must not count for $school's score this month.
        // Expected distinct problems: school=1, controlSchool=2
        // The rank should be: controlSchool, school
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $sharedProblem,
            $oldstudent
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::updateRunTime(
            $runData['response']['guid'],
            new \OmegaUp\Timestamp(strtotime($priorDate))
        );

        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $newstudent,
            $sharedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $newstudent,
            $exclusiveProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $sharedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $exclusiveProblem,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $schoolScore = self::findSchoolScore(
            $candidates,
            $school['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($schoolScore);
        $this->assertNotNull($controlScore);
        $this->assertGreaterThan(
            $schoolScore,
            $controlScore,
            'A problem solved by the school in a prior month must not count again'
        );
    }

    public function testProblemsWithoutQualitySeal() {
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $controlStudent] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool($school, $student);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $controlStudent
        );

        $sealedProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );
        $unsealedProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => false])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // school:        student=>sealedProblem, student=>unsealedProblem
        // controlSchool: controlStudent=>sealedProblem
        //
        // unsealedProblem has no quality seal so it must not add points.
        // Expected counted problems: school=1, controlSchool=1
        // The rank should be: tie (same score)
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $sealedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $unsealedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $sealedProblem,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $schoolScore = self::findSchoolScore(
            $candidates,
            $school['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($schoolScore);
        $this->assertNotNull($controlScore);
        // Both schools only get points for sealedProblem; if the problem
        // without quality seal were counted the scores would differ.
        $this->assertEqualsWithDelta(
            $controlScore,
            $schoolScore,
            0.01,
            'A problem without quality seal must not be counted'
        );
    }

    public function testIdentitiesWithoutMainEmail() {
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        [
            'identity' => $student,
            'user' => $studentUser,
        ] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $controlStudent] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool($school, $student);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $controlStudent
        );

        $problem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // school:        student=>problem (student has no main email)
        // controlSchool: controlStudent=>problem
        //
        // student's user has main_email_id = NULL so their submissions must
        // be excluded and school must not appear among the candidates.
        // Expected candidates: controlSchool only
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $problem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $problem,
            $runDate
        );

        // Remove the student's main email after the runs are created.
        $user = \OmegaUp\DAO\Users::getByPK(intval($studentUser->user_id));
        if (is_null($user)) {
            $this->fail('The student user must exist');
        }
        $user->main_email_id = null;
        \OmegaUp\DAO\Users::update($user);

        $candidates = self::calculateAndGetCandidates($runDate);

        $this->assertNull(
            self::findSchoolScore($candidates, $school['request']['name']),
            'A school whose only solver has no main email must not be a candidate'
        );
        $this->assertNotNull(
            self::findSchoolScore(
                $candidates,
                $controlSchool['request']['name']
            ),
            'The school of the user with a main email must be a candidate'
        );
    }

    public function testSchoolEligibility() {
        // 12 months rule
        $ineligibleSchool = \OmegaUp\Test\Factories\Schools::createSchool();
        $eligibleSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $ineligibleStudent] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $eligibleStudent] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $ineligibleSchool,
            $ineligibleStudent
        );
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $eligibleSchool,
            $eligibleStudent
        );

        $problem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();
        // The cron computes candidates for the month that follows $runDate.
        $firstDayOfNextMonth = date('Y-m-01', strtotime($runDate . ' +1 day'));
        // Latest previous win that is still inside the 12-month exclusion
        // window (time >= DATE_SUB(first_day_of_next_month, INTERVAL 1 YEAR)).
        $boundaryDate = date(
            'Y-m-d',
            strtotime($firstDayOfNextMonth . ' -1 year')
        );
        // One day earlier the win is more than 12 months old.
        $beforeBoundaryDate = date(
            'Y-m-d',
            strtotime($boundaryDate . ' -1 day')
        );

        // Prepare setup:
        // ineligibleSchool: won exactly 12 months before the target month,
        //                   still inside the window => excluded
        // eligibleSchool:   won 12 months and one day before the target
        //                   month => eligible again
        // Both schools solve the same problem within the evaluation window.
        \OmegaUp\DAO\SchoolOfTheMonth::create(
            new \OmegaUp\DAO\VO\SchoolOfTheMonth([
                'school_id' => $ineligibleSchool['school']->school_id,
                'time' => $boundaryDate,
                'ranking' => 1,
            ])
        );
        \OmegaUp\DAO\SchoolOfTheMonth::create(
            new \OmegaUp\DAO\VO\SchoolOfTheMonth([
                'school_id' => $eligibleSchool['school']->school_id,
                'time' => $beforeBoundaryDate,
                'ranking' => 1,
            ])
        );

        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $ineligibleStudent,
            $problem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $eligibleStudent,
            $problem,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $this->assertNull(
            self::findSchoolScore(
                $candidates,
                $ineligibleSchool['request']['name']
            ),
            'A school selected less than 12 months ago must not be eligible'
        );
        $this->assertNotNull(
            self::findSchoolScore(
                $candidates,
                $eligibleSchool['request']['name']
            ),
            'A school selected more than 12 months ago must be eligible again'
        );
    }

    public function testGroupProblemAdminSolvesDoNotCount() {
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $owner] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $groupMember] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $controlStudent] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Schools::addUserToSchool($school, $groupMember);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $controlStudent
        );

        // Problem owned by $owner and administered by a group that contains
        // $groupMember.
        $ownedProblem = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $owner
        );
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup($owner);
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $groupMember
        );
        \OmegaUp\Test\Factories\Problem::addGroupAdmin(
            $ownedProblem,
            $groupData['group']
        );
        // A separate problem with no special relationship to the school.
        $neutralProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // school:        groupMember=>ownedProblem (excluded: group admin),
        //                groupMember=>neutralProblem
        // controlSchool: controlStudent=>neutralProblem
        //
        // ownedProblem solved by a group admin must not count for school.
        // Expected distinct problems: school=1, controlSchool=1
        // The rank should be: tie (same score)
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $groupMember,
            $ownedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $groupMember,
            $neutralProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $neutralProblem,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $schoolScore = self::findSchoolScore(
            $candidates,
            $school['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($schoolScore);
        $this->assertNotNull($controlScore);
        // The problem administered through a group must be ignored, so the
        // school is left only with the neutral problem, same as the control.
        $this->assertEqualsWithDelta(
            $controlScore,
            $schoolScore,
            0.01,
            'Problems administered by the solver through a group must not be counted'
        );
    }

    public function testNonAcceptedRuns() {
        // Only AC counts
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $controlStudent] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool($school, $student);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $controlStudent
        );

        $solvedProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );
        $attemptedProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // school:        student=>solvedProblem (AC),
        //                student=>attemptedProblem (WA)
        // controlSchool: controlStudent=>solvedProblem (AC)
        //
        // The WA run on attemptedProblem must not add ranking points.
        // Expected counted problems: school=1, controlSchool=1
        // The rank should be: tie (same score)
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $solvedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $attemptedProblem,
            $runDate,
            0,
            'WA'
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $solvedProblem,
            $runDate
        );

        $candidates = self::calculateAndGetCandidates($runDate);

        $schoolScore = self::findSchoolScore(
            $candidates,
            $school['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($schoolScore);
        $this->assertNotNull($controlScore);
        // Only accepted runs gives ranking points, so both schools must end
        // up with the same score.
        $this->assertEqualsWithDelta(
            $controlScore,
            $schoolScore,
            0.01,
            'A non-accepted run must not add ranking points'
        );
    }

    public function testPrivateProblemsExcluded() {
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        $controlSchool = \OmegaUp\Test\Factories\Schools::createSchool();

        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $controlStudent] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Schools::addUserToSchool($school, $student);
        \OmegaUp\Test\Factories\Schools::addUserToSchool(
            $controlSchool,
            $controlStudent
        );

        $sharedProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );
        // Created public so that the run can be submitted; it will be made
        // private before the ranks are computed.
        $hiddenProblem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams(['quality_seal' => true])
        );

        $runDate = self::previousMonthRunDate();

        // Prepare setup:
        // school:        student=>hiddenProblem (private at rank time),
        //                student=>sharedProblem
        // controlSchool: controlStudent=>sharedProblem
        //
        // hiddenProblem has visibility < 1 when the ranks are computed so it
        // must not add points.
        // Expected counted problems: school=1, controlSchool=1
        // The rank should be: tie (same score)
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $hiddenProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $student,
            $sharedProblem,
            $runDate
        );
        \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
            $controlStudent,
            $sharedProblem,
            $runDate
        );

        // Make the problem private before the ranks are computed.
        $problem = $hiddenProblem['problem'];
        $problem->visibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE;
        \OmegaUp\DAO\Problems::update($problem);

        $candidates = self::calculateAndGetCandidates($runDate);

        $schoolScore = self::findSchoolScore(
            $candidates,
            $school['request']['name']
        );
        $controlScore = self::findSchoolScore(
            $candidates,
            $controlSchool['request']['name']
        );

        $this->assertNotNull($schoolScore);
        $this->assertNotNull($controlScore);
        // The private problem must be ignored, so both schools only count
        // the shared problem.
        $this->assertEqualsWithDelta(
            $controlScore,
            $schoolScore,
            0.01,
            'A problem that is not public must not be counted'
        );
    }
}
