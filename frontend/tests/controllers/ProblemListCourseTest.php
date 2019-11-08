<?php
/**
 * Class ProblemListCourse
 *
 * @author juan.pablo
 */
class ProblemListCourseTest extends OmegaupTestCase {
    public function testSolvedAndUnsolvedProblemByUsersOfACourse() {
        $num_users = 3;
        $num_problems = 3;
        // Create course
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $adminLogin = self::login($courseData['admin']);
        // Create problems and add to course
        for ($i = 0; $i < $num_problems; $i++) {
            $problem[$i] = ProblemsFactory::createProblem();
        }
        CoursesFactory::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            $problem
        );
        // Create users and add to course
        $user = [];
        $identity = [];
        for ($i = 0; $i < $num_users; $i++) {
            ['user' => $user[$i], 'identity' => $identity[$i]] = UserFactory::createUser();
            CoursesFactory::addStudentToCourse($courseData, $identity[$i]);
        }
        // Create runs to problems directly
        $runs = [];
        $runs[0] = RunsFactory::createRunToProblem($problem[0], $identity[0]);
        $runs[1] = RunsFactory::createRunToProblem($problem[1], $identity[1]);
        $runs[2] = RunsFactory::createRunToProblem($problem[2], $identity[2]);
        $runs[3] = RunsFactory::createRunToProblem($problem[0], $identity[1]);
        $runs[4] = RunsFactory::createRunToProblem($problem[0], $identity[2]);
        RunsFactory::gradeRun($runs[0], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[1], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[2]); // run with a AC verdict
        RunsFactory::gradeRun($runs[3]); // run with a AC verdict
        RunsFactory::gradeRun($runs[4]); // run with a AC verdict
        // Users must join course
        for ($i = 0; $i < $num_users; $i++) {
            $userLogin[$i] = self::login($identity[$i]);
            $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'course_alias' => $courseData['course_alias']
            ]));

            $gitObjectId = $details['statements']['acceptTeacher']['gitObjectId'];
            \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'course_alias' => $courseData['course_alias'],
                'usernameOrEmail' => $user[$i]->username,
                'accept_teacher' => true,
                'accept_teacher_git_object_id' => $gitObjectId,
            ]));
        }
        $adminLogin = self::login($courseData['admin']);
        $solvedProblems = \OmegaUp\Controllers\Course::apiListSolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = \OmegaUp\Controllers\Course::apiListUnsolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $this->assertArrayHasKey(
            $user[0]->username,
            $unsolvedProblems['user_problems']
        );
        $this->assertEquals(
            1,
            count(
                $unsolvedProblems['user_problems'][$user[0]->username]
            )
        );
        $this->assertArrayHasKey(
            $user[1]->username,
            $unsolvedProblems['user_problems']
        );
        $this->assertEquals(
            1,
            count(
                $unsolvedProblems['user_problems'][$user[1]->username]
            )
        );
        $this->assertArrayHasKey(
            $user[1]->username,
            $solvedProblems['user_problems']
        );
        $this->assertEquals(
            1,
            count(
                $solvedProblems['user_problems'][$user[1]->username]
            )
        );
        $this->assertArrayHasKey(
            $user[2]->username,
            $solvedProblems['user_problems']
        );
        $this->assertEquals(
            2,
            count(
                $solvedProblems['user_problems'][$user[2]->username]
            )
        );
        // Now, user[0] submit one run with AC verdict
        $runs[5] = RunsFactory::createRunToProblem($problem[0], $identity[0]);
        RunsFactory::gradeRun($runs[5]);
        $solvedProblems = \OmegaUp\Controllers\Course::apiListSolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = \OmegaUp\Controllers\Course::apiListUnsolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $this->assertArrayNotHasKey(
            $user[0]->username,
            $unsolvedProblems['user_problems']
        );
        $this->assertArrayHasKey(
            $user[0]->username,
            $solvedProblems['user_problems']
        );
    }
    public function testUsersOfACourseDenyAccessFromATeacher() {
        $num_users = 3;
        $num_problems = 3;
        // Create course
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $adminLogin = self::login($courseData['admin']);
        // Create problems and add to course
        for ($i = 0; $i < $num_problems; $i++) {
            $problem[$i] = ProblemsFactory::createProblem();
        }
        CoursesFactory::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            $problem
        );
        // Create users and add to course$user = [];
        $user = [];
        $identity = [];
        for ($i = 0; $i < $num_users; $i++) {
            ['user' => $user[$i], 'identity' => $identity[$i]] = UserFactory::createUser();
            CoursesFactory::addStudentToCourse($courseData, $identity[$i]);
        }
        // Create runs to problems directly
        $runs = [];
        $runs[0] = RunsFactory::createRunToProblem($problem[0], $identity[0]);
        $runs[1] = RunsFactory::createRunToProblem($problem[1], $identity[1]);
        $runs[2] = RunsFactory::createRunToProblem($problem[2], $identity[2]);
        $runs[3] = RunsFactory::createRunToProblem($problem[0], $identity[1]);
        $runs[4] = RunsFactory::createRunToProblem($problem[0], $identity[2]);
        RunsFactory::gradeRun($runs[0], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[1], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[2]); // run with a AC verdict
        RunsFactory::gradeRun($runs[3]); // run with a AC verdict
        RunsFactory::gradeRun($runs[4]); // run with a AC verdict
        $adminLogin = self::login($courseData['admin']);
        $solvedProblems = \OmegaUp\Controllers\Course::apiListSolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = \OmegaUp\Controllers\Course::apiListUnsolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        // No one has join course
        $this->assertEquals(0, count($solvedProblems['user_problems']));
        $this->assertEquals(0, count($unsolvedProblems['user_problems']));
        // Users must join course
        for ($i = 0; $i < ($num_users - 1); $i++) {
            $userLogin[$i] = self::login($identity[$i]);
            $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'course_alias' => $courseData['course_alias']
            ]));

            $gitObjectId = $details['statements']['acceptTeacher']['gitObjectId'];
            \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'course_alias' => $courseData['course_alias'],
                'usernameOrEmail' => $user[$i]->username,
                'accept_teacher' => false,
                'accept_teacher_git_object_id' => $gitObjectId,
            ]));
        }
        $solvedProblems = \OmegaUp\Controllers\Course::apiListSolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = \OmegaUp\Controllers\Course::apiListUnsolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        // No one has accept teacher's request
        $this->assertEquals(0, count($solvedProblems['user_problems']));
        $this->assertEquals(0, count($unsolvedProblems['user_problems']));
        // User[2] accept teacher's request
        $userLogin[2] = self::login($identity[2]);
        $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin[$i]->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $gitObjectId = $details['statements']['acceptTeacher']['gitObjectId'];
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $userLogin[2]->auth_token,
            'course_alias' => $courseData['course_alias'],
            'usernameOrEmail' => $user[2]->username,
            'accept_teacher' => true,
            'accept_teacher_git_object_id' => $gitObjectId,
        ]));
        $solvedProblems = \OmegaUp\Controllers\Course::apiListSolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = \OmegaUp\Controllers\Course::apiListUnsolvedProblems(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $this->assertArrayHasKey(
            $user[2]->username,
            $solvedProblems['user_problems']
        );
        $this->assertEquals(
            2,
            count(
                $solvedProblems['user_problems'][$user[2]->username]
            )
        );
        $this->assertEquals(0, count($unsolvedProblems['user_problems']));
    }
}
