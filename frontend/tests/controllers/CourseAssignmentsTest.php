<?php

class CourseAssignmentsTest extends OmegaupTestCase {
    public function testOrderAssignments() {
        // Create a course with 5 assignments
        $courseData = CoursesFactory::createCourseWithAssignments(5);

        // Login admin and getting assignments list
        $adminLogin = self::login($courseData['admin']);
        $assignments = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // Setting original order
        $i = 1;
        foreach ($assignments['assignments'] as $index => $assignment) {
            $assignments['assignments'][$index]['order'] = $i++;
        }
        \OmegaUp\Controllers\Course::apiUpdateAssignmentsOrder(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignments' => $assignments['assignments'],
        ]));

        // Getting one more time assignments list with original order
        $assignments = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // ordering assignments
        $assignments['assignments'][0]['order'] = 1;
        $assignments['assignments'][1]['order'] = 2;
        $assignments['assignments'][2]['order'] = 3;
        $assignments['assignments'][3]['order'] = 4;
        $assignments['assignments'][4]['order'] = 5;

        // Asserting assignments order is the same that the original
        $i = 1;
        foreach ($assignments['assignments'] as $index => $assignment) {
            $originalOrder[$index] = [
                'alias' => $assignments['assignments'][$index]['alias'],
                'order' => $assignments['assignments'][$index]['order']
            ];
            $this->assertEquals($assignments['assignments'][$index]['order'], $i++);
        }

        // Reordering assignments
        $assignments['assignments'][0]['order'] = 5;
        $assignments['assignments'][1]['order'] = 3;
        $assignments['assignments'][2]['order'] = 1;
        $assignments['assignments'][3]['order'] = 2;
        $assignments['assignments'][4]['order'] = 4;
        \OmegaUp\Controllers\Course::apiUpdateAssignmentsOrder(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignments' => $assignments['assignments'],
        ]));
        $assignments = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // Asserting that the new ordering is not equal that original
        foreach ($assignments['assignments'] as $index => $assignment) {
            $this->assertNotEquals(
                $assignment['alias'],
                $originalOrder[$index]['alias']
            );
        }
    }
}
