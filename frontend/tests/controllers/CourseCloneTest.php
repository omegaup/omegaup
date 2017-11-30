<?php

class CourseCloneTest extends OmegaupTestCase {
    /**
     * Create clone of a course
     */
    public function testCreateCourseClone() {
        $homeworkCount = 2;
        $testCount = 2;
        $problemsPerAssignment = 2;
        $studentCount = 2;
        $problemAssignmentsMap = [];

        // Create course with assignments
        $courseData = CoursesFactory::createCourseWithNAssignmentsPerType([
            'homework' => $homeworkCount,
            'test' => $testCount
        ]);

        // Add problems to assignments
        $adminLogin = self::login($courseData['admin']);
        for ($i = 0; $i < $homeworkCount + $testCount; $i++) {
            $assignmentAlias = $courseData['assignment_aliases'][$i];
            $problemAssignmentsMap[$assignmentAlias] = [];

            for ($j = 0; $j < $problemsPerAssignment; $j++) {
                $problemData = ProblemsFactory::createProblem();
                CourseController::apiAddProblem(new Request([
                    'auth_token' => $adminLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $assignmentAlias,
                    'problem_alias' => $problemData['request']['alias'],
                ]));
                $problemAssignmentsMap[$assignmentAlias][] = $problemData;
            }
        }

        // Create & add students to course
        $studentsUsername = [];
        $studentsData = null;
        for ($i = 0; $i < $studentCount; $i++) {
            $studentsData = CoursesFactory::addStudentToCourse($courseData);
            $studentsUsername[] = $studentsData->username;
        }

        $courseAlias = Utils::CreateRandomString();

        // Clone the course
        $adminLogin = self::login($courseData['admin']);
        $courseClonedData = CourseController::apiClone(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'start_time' => Time::get()
        ]));

        $this->assertEquals($courseAlias, $courseClonedData['alias']);

        $assignments = CourseController::apiListAssignments(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));
        foreach ($assignments['assignments'] as $key => $assignment) {
            $this->assertEquals($courseData['assignment_aliases'][$key], $assignment['alias']);
            $problems = CourseController::apiAssignmentDetails(new Request([
                'assignment' => $assignment['alias'],
                'course' => $courseAlias,
                'auth_token' => $adminLogin->auth_token
            ]));
            foreach ($problems['problems'] as $index => $problem) {
                $this->assertEquals($problemAssignmentsMap[$courseData[
                    'assignment_aliases'][$key]][$index]['problem']->alias, $problem['alias']);
            }
        }
        $students = CourseController::apiListStudents(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseAlias
        ]));
        $this->assertCount(0, $students['students']);
    }
}
