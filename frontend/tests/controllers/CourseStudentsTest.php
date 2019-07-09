<?php

/**
 * Tests that students' progress can be tracked.
 */
class CourseStudentsTest extends OmegaupTestCase {
    /**
     * Basic apiStudentProgress test.
     */
    public function testAddStudentToCourse() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemData = ProblemsFactory::createProblem();

        CourseController::apiAddProblem(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $problem = $problemData['problem'];

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Add one run to one of the problems.
        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
        {
            $studentLogin = OmegaupTestCase::login($students[0]);
            $runResponsePA = RunController::apiCreate(new Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment']->problemset_id,
                'problem_alias' => $problem->alias,
                'language' => 'c',
                'source' => $submissionSource,
            ]));
            RunsFactory::gradeRun(null /*runData*/, 0.5, 'PA', null, $runResponsePA['guid']);
        }

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiStudentProgress(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'usernameOrEmail' => $students[0]->username,
        ]));
        $this->assertCount(1, $response['problems']);
        $this->assertCount(1, $response['problems'][0]['runs']);
        $this->assertEquals($response['problems'][0]['runs'][0]['source'], $submissionSource);
        $this->assertEquals($response['problems'][0]['runs'][0]['score'], 0.5);
    }

    /**
     * apiIntroDetails test with associated identities to the course's group.
     */
    public function testAddIdentityStudentToCourse() {
        // Add a new user with identity groups creator privileges, and login
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);

        // Create a course where course admin is a identity creator group member
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $creator,
            $creatorLogin
        );

        // Prepare assignment. Create problems
        $problemData = ProblemsFactory::createProblem();

        CourseController::apiAddProblem(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        // Get Group object
        $associatedGroup = GroupsDAO::findByAlias($courseData['course_alias']);

        // Create identities for a group
        $password = Utils::CreateRandomString();
        $associatedIdentity = IdentityFactory::createIdentitiesFromAGroup(
            $associatedGroup,
            $creatorLogin,
            $password
        );

        // Create an unassociated group, it does not have access to the course
        $unassociatedGroup = GroupsFactory::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        )['group'];

        $unassociatedIdentity = IdentityFactory::createIdentitiesFromAGroup(
            $unassociatedGroup,
            $creatorLogin,
            $password
        );

        // Create a valid run for assignment
        $runData = RunsFactory::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $associatedIdentity
        );

        try {
            // Create an invalid run for assignment, because identity is not a
            // member of the course group
            $runData = RunsFactory::createCourseAssignmentRun(
                $problemData,
                $courseData,
                $unassociatedIdentity
            );
            $this->fail('Unassociated identity group should not join the course' .
                        'without an explicit invitation');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
