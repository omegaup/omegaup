<?php

class CourseCreateTest extends OmegaupTestCase {

    /**
     * Create course helper
     */
    private function createCourse($user, $name = null, $alias = null, $description = null, $start_time = null, $finish_time = null) {
        $data = array();
        $data['request'] = new Request(array(
            'auth_token' => self::login($user),
            'name' => is_null($name) ? Utils::CreateRandomString() : $name,
            'alias' => is_null($alias) ? Utils::CreateRandomString() : $alias,
            'description' => is_null($description) ? Utils::CreateRandomString() : $description,
            'start_time' => is_null($start_time) ? (Utils::GetPhpUnixTimestamp() + 60) : $start_time,
            'finish_time' => is_null($finish_time) ? (Utils::GetPhpUnixTimestamp() + 120) : $finish_time
        ));

        // Call api
        $data['response'] = CourseController::apiCreate($data['request']);
        return $data;
    }

    /**
     * Create an assignment inside a course.
     */
    private function createAssignment($user, $courseAlias, $name = null, $alias = null, $description = null, $start_time = null, $finish_time = null, $assignment_type = null) {
        $data = array();
        $data['request'] = new Request(array(
            'auth_token' => self::login($user),
            'course_alias' => $courseAlias,
            'name' => is_null($name) ? Utils::CreateRandomString() : $name,
            'alias' => is_null($alias) ? Utils::CreateRandomString() : $alias,
            'description' => is_null($description) ? Utils::CreateRandomString() : $description,
            'start_time' => is_null($start_time) ? (Utils::GetPhpUnixTimestamp() + 60) : $start_time,
            'finish_time' => is_null($finish_time) ? (Utils::GetPhpUnixTimestamp() + 120) : $finish_time,
            'assignment_type' => is_null($assignment_type) ? 'homework' : $assignment_type
        ));

        $data['response'] = CourseController::apiCreateAssignment($data['request']);
        return $data;
    }

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
            array('alias' => $r['alias'])
        )));
    }

    /**
     * Tests course/apiListAssignments returns valid results.
     */
    public function testListCourseAssignments() {
        // Create a course with 2 assignments
        $courseAdmin = UserFactory::createUser();
        $courseData = $this->createCourse($courseAdmin);
        $courseAlias = $courseData['request']['alias'];
        $assignmentData = array();
        $assignmentData[0] = $this->createAssignment($courseAdmin, $courseAlias);
        $assignmentData[1] = $this->createAssignment($courseAdmin, $courseAlias);

        // Call API
        $response = CourseController::apiListAssignments(new Request(array(
            'auth_token' => self::login($courseAdmin),
            'course_alias' => $courseAlias
        )));

        $this->assertEquals(2, count($response['assignments']));
    }
}
