<?php

class CourseCreateTest extends OmegaupTestCase {
    /**
     * Create course happy path
     */
    public function testCreateSchoolCourse() {
        $user = UserFactory::createUser();

        $r = new Request(array(
            'auth_token' => self::login($user),
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ));

        // Call api
        $response = CourseController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(CoursesDAO::findByName($r['name'])));
    }

    public function testCreateCourseDuplicatedName() {
        $user = UserFactory::createUser();

        $r = new Request(array(
            'auth_token' => self::login($user),
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ));

        // Call api
        $response = CourseController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(CoursesDAO::findByName($r['name'])));

        // unset course_id otherwise this would be an update
        $r['course_id'] = null;

        // Call api again
        $response = CourseController::apiCreate($r);
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(CoursesDAO::findByName($r['name'])));
    }

    public function testCreateSchoolAssignment() {
        // Create a test course
        $user = UserFactory::createUser();

        $courseAlias = Utils::CreateRandomString();

        $r = new Request(array(
            'auth_token' => self::login($user),
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ));

        // Call api
        $course = CourseController::apiCreate($r);
        $this->assertEquals('ok', $course['status']);

        // Create a test course
        $r = new Request(array(
            'auth_token' => self::login($user),
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ));
        $course = CourseController::apiCreateAssignment($r);

        // There should exist 1 assignment with this alias
        $this->assertEquals(1, count(AssignmentsDAO::search(
                    array("alias" => $r['alias'])
                )));
    }
}
