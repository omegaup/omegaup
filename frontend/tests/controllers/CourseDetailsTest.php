<?php

/**
 *
 * @author alan
 */

class CourseDetailsTest extends OmegaupTestCase {

    public function testGetcourseDetailsValid() {

        // Create 1 course with 1 assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        // Call the details API
        $response = CourseController::apiDetails(new Request(array(
            'auth_token' => self::login($courseData['user']),
            'alias' => $courseData['course_alias']
        )));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($courseData['course_alias'], $response['alias']);
        Validators::isNumber($response['start_time'], 'start_time', true);
        Validators::isNumber($response['finish_time'], 'finish_time', true);

        // 1 assignment
        $this->assertEquals(1, count($response['assignments']));

        foreach ($response['assignments'] as $assignment) {
            $this->assertNotNull($assignment['name']);
            $this->assertNotNull($assignment['description']);
            $this->assertNotNull($assignment['alias']);
            $this->assertNotNull($assignment['assignment_type']);
            $this->assertNotNull($assignment['start_time']);
            $this->assertNotNull($assignment['finish_time']);

            Validators::isNumber($assignment['start_time'], 'start_time', true);
            Validators::isNumber($assignment['finish_time'], 'finish_time', true);
        }
    }
}

