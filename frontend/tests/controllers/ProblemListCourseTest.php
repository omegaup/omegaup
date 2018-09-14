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
        for ($i = 0; $i < $num_users; $i++) {
            $user[$i] = UserFactory::createUser();
            CoursesFactory::addStudentToCourse($courseData, $user[$i]);
        }
        // Create runs to problems directly
        $runs = [];
        $runs[0] = RunsFactory::createRunToProblem($problem[0], $user[0]);
        $runs[1] = RunsFactory::createRunToProblem($problem[1], $user[1]);
        $runs[2] = RunsFactory::createRunToProblem($problem[2], $user[2]);
        $runs[3] = RunsFactory::createRunToProblem($problem[0], $user[1]);
        $runs[4] = RunsFactory::createRunToProblem($problem[0], $user[2]);
        RunsFactory::gradeRun($runs[0], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[1], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[2]); // run with a AC verdict
        RunsFactory::gradeRun($runs[3]); // run with a AC verdict
        RunsFactory::gradeRun($runs[4]); // run with a AC verdict
        // Users must join course
        for ($i=0; $i<$num_users; $i++) {
            $userLogin[$i] = self::login($user[$i]);
            $intro_details = CourseController::apiIntroDetails(new Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'current_user_id' => $user[$i]->user_id,
                'course_alias' => $courseData['course_alias']
            ]));
            CourseController::apiAddStudent(new Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'course_alias' => $courseData['course_alias'],
                'usernameOrEmail' => $user[$i]->username,
                'accept_teacher' => 'yes',
                'accept_teacher_git_object_id' => $intro_details['accept_teacher_statement']['git_object_id'],
            ]));
        }
        $adminLogin = self::login($courseData['admin']);
        $solvedProblems = CourseController::apiListSolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = CourseController::apiListUnsolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $this->assertArrayHasKey($user[0]->username, $unsolvedProblems['user_problems']);
        $this->assertEquals(1, count($unsolvedProblems['user_problems'][$user[0]->username]));
        $this->assertArrayHasKey($user[1]->username, $unsolvedProblems['user_problems']);
        $this->assertEquals(1, count($unsolvedProblems['user_problems'][$user[1]->username]));
        $this->assertArrayHasKey($user[1]->username, $solvedProblems['user_problems']);
        $this->assertEquals(1, count($solvedProblems['user_problems'][$user[1]->username]));
        $this->assertArrayHasKey($user[2]->username, $solvedProblems['user_problems']);
        $this->assertEquals(2, count($solvedProblems['user_problems'][$user[2]->username]));
        // Now, user[0] submit one run with AC verdict
        $runs[5] = RunsFactory::createRunToProblem($problem[0], $user[0]);
        RunsFactory::gradeRun($runs[5]);
        $solvedProblems = CourseController::apiListSolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = CourseController::apiListUnsolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $this->assertArrayNotHasKey($user[0]->username, $unsolvedProblems['user_problems']);
        $this->assertArrayHasKey($user[0]->username, $solvedProblems['user_problems']);
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
        // Create users and add to course
        for ($i = 0; $i < $num_users; $i++) {
            $user[$i] = UserFactory::createUser();
            CoursesFactory::addStudentToCourse($courseData, $user[$i]);
        }
        // Create runs to problems directly
        $runs = [];
        $runs[0] = RunsFactory::createRunToProblem($problem[0], $user[0]);
        $runs[1] = RunsFactory::createRunToProblem($problem[1], $user[1]);
        $runs[2] = RunsFactory::createRunToProblem($problem[2], $user[2]);
        $runs[3] = RunsFactory::createRunToProblem($problem[0], $user[1]);
        $runs[4] = RunsFactory::createRunToProblem($problem[0], $user[2]);
        RunsFactory::gradeRun($runs[0], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[1], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[2]); // run with a AC verdict
        RunsFactory::gradeRun($runs[3]); // run with a AC verdict
        RunsFactory::gradeRun($runs[4]); // run with a AC verdict
        $adminLogin = self::login($courseData['admin']);
        $solvedProblems = CourseController::apiListSolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = CourseController::apiListUnsolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        // No one has join course
        $this->assertEquals(0, count($solvedProblems['user_problems']));
        $this->assertEquals(0, count($unsolvedProblems['user_problems']));
        // Users must join course
        for ($i=0; $i<($num_users - 1); $i++) {
            $userLogin[$i] = self::login($user[$i]);
            $intro_details = CourseController::apiIntroDetails(new Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'current_user_id' => $user[$i]->user_id,
                'course_alias' => $courseData['course_alias']
            ]));
            CourseController::apiAddStudent(new Request([
                'auth_token' => $userLogin[$i]->auth_token,
                'course_alias' => $courseData['course_alias'],
                'usernameOrEmail' => $user[$i]->username,
                'accept_teacher' => 'no',
                'accept_teacher_git_object_id' => $intro_details['accept_teacher_statement']['git_object_id'],
            ]));
        }
        $solvedProblems = CourseController::apiListSolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = CourseController::apiListUnsolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        // No one has accept teacher's request
        $this->assertEquals(0, count($solvedProblems['user_problems']));
        $this->assertEquals(0, count($unsolvedProblems['user_problems']));
        // User[2] accept teacher's request
        $userLogin[2] = self::login($user[2]);
        $intro_details = CourseController::apiIntroDetails(new Request([
            'auth_token' => $userLogin[$i]->auth_token,
            'current_user_id' => $user[$i]->user_id,
            'course_alias' => $courseData['course_alias']
        ]));
        CourseController::apiAddStudent(new Request([
            'auth_token' => $userLogin[2]->auth_token,
            'course_alias' => $courseData['course_alias'],
            'usernameOrEmail' => $user[2]->username,
            'accept_teacher' => 'yes',
            'accept_teacher_git_object_id' => $intro_details['accept_teacher_statement']['git_object_id'],
        ]));
        $solvedProblems = CourseController::apiListSolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $unsolvedProblems = CourseController::apiListUnsolvedProblems(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $this->assertArrayHasKey($user[2]->username, $solvedProblems['user_problems']);
        $this->assertEquals(2, count($solvedProblems['user_problems'][$user[2]->username]));
        $this->assertEquals(0, count($unsolvedProblems['user_problems']));
    }
}
