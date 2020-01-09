<?php

/**
 * Tests that students' progress can be tracked.
 */
class CourseStudentsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic apiStudentProgress test.
     */
    public function testAddStudentToCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $problem = $problemData['problem'];

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        // Add one run to one of the problems.
        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
        {
            $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[0]
            );
            $runResponsePA = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment']->problemset_id,
                'problem_alias' => $problem->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                null /*runData*/,
                0.5,
                'PA',
                null,
                $runResponsePA['guid']
            );
        }

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiStudentProgress(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'usernameOrEmail' => $students[0]->username,
        ]));
        $this->assertCount(1, $response['problems']);
        $this->assertCount(1, $response['problems'][0]['runs']);
        $this->assertEquals(
            $response['problems'][0]['runs'][0]['source'],
            $submissionSource
        );
        $this->assertEquals($response['problems'][0]['runs'][0]['score'], 0.5);
    }

    /**
     * apiIntroDetails test with associated identities to the course's group.
     */
    public function testAddIdentityStudentToCourse() {
        // Add a new user with identity groups creator privileges, and login
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);

        // Create a course where course admin is a identity creator group member
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $creatorIdentity,
            $creatorLogin
        );

        // Prepare assignment. Create problems
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        // Get Group object
        $associatedGroup = \OmegaUp\DAO\Groups::findByAlias(
            $courseData['course_alias']
        );

        // Create identities for a group
        $password = \OmegaUp\Test\Utils::createRandomString();
        [$_, $associatedIdentity] = IdentityFactory::createIdentitiesFromAGroup(
            $associatedGroup,
            $creatorLogin,
            $password
        );

        // Create an unassociated group, it does not have access to the course
        $unassociatedGroup = GroupsFactory::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        )['group'];

        [$unassociatedIdentity, $_] = IdentityFactory::createIdentitiesFromAGroup(
            $unassociatedGroup,
            $creatorLogin,
            $password
        );

        // Create a valid run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $associatedIdentity
        );

        try {
            // Create an invalid run for assignment, because identity is not a
            // member of the course group
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problemData,
                $courseData,
                $unassociatedIdentity
            );
            $this->fail('Unassociated identity group should not join the course' .
                        'without an explicit invitation');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
