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
        $this->assertEquals(2, count(CoursesDAO::findByName($r['name'])));
    }
}
