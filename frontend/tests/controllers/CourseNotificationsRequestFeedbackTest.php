<?php

/**
 * Test administrative tasks for teaching assistant team
 */
class CourseNotificationsRequestFeedbackTest extends \OmegaUp\Test\ControllerTestCase {
    private static $adminLogin = null;
    private static $courseAlias = null;
    private static $assignmentAlias = null;
    private static $course = null;
    private static $courseData = null;
    private static $problemData = null;
    private static $identity = null;
    private static $identity2 = null;
    private static $identity3 = null;
    private static $identityGroupTA = null;
    private static $identityGroupTA2 = null;
    private static $identityGroupAdmin = null;
    private static $groupDataAdmin = null;
    private static $groupDataTA = null;

    //separar TA de admins

    public function setUp(): void {
        parent::setUp();

        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();
        self::$adminLogin = self::login($adminUser);

        // Create a course
        self::$courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // create a problem
        self::$problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // create normal users
        ['identity' => self::$identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => self::$identity2] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => self::$identity3] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => self::$identityGroupTA] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => self::$identityGroupTA2] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => self::$identityGroupAdmin] = \OmegaUp\Test\Factories\User::createUser();

        // Get a group for admins and other for teaching assistants
        self::$groupDataAdmin = \OmegaUp\Test\Factories\Groups::createGroup();
        self::$groupDataTA = \OmegaUp\Test\Factories\Groups::createGroup();

        self::$courseAlias = self::$courseData['course_alias'];

        // add problem to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            self::$adminLogin,
            self::$courseData['course_alias'],
            self::$courseData['assignment_alias'],
            [ self::$problemData ]
        );

        // add identity like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => self::$adminLogin->auth_token,
                'usernameOrEmail' => self::$identity->username,
                'course_alias' => self::$courseData['course_alias'],
            ])
        );

        // add identity2 like an admin
        \OmegaUp\Controllers\Course::apiAddAdmin(
            new \OmegaUp\Request([
                'auth_token' => self::$adminLogin->auth_token,
                'usernameOrEmail' => self::$identity2->username,
                'course_alias' => self::$courseData['course_alias'],
            ])
        );

        // add identity3 like student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            self::$courseData,
            self::$identity3
        );

        // add user to the groups
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            self::$groupDataAdmin,
            self::$identityGroupAdmin
        );

        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            self::$groupDataTA,
            self::$identityGroupTA
        );

        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            self::$groupDataTA,
            self::$identityGroupTA2
        );

        // Call api to add group admin
        \OmegaUp\Controllers\Course::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => self::$adminLogin->auth_token,
            'group' => self::$groupDataAdmin['request']['alias'],
            'course_alias' => self::$courseData['course_alias'],
        ]));

        // Call api to add group teaching assistant
        \OmegaUp\Controllers\Course::apiAddGroupTeachingAssistant(new \OmegaUp\Request([
            'auth_token' => self::$adminLogin->auth_token,
            'group' => self::$groupDataTA['request']['alias'],
            'course_alias' => self::$courseData['course_alias'],
        ]));

        // Create the assignment
        self::$assignmentAlias = self::$courseData['assignment_alias'];
        self::$course = \OmegaUp\DAO\Courses::getByAlias(self::$courseAlias);
        if (is_null(self::$course) || is_null(self::$course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Create a run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            self::$problemData,
            self::$courseData,
            self::$identity3
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        $studentLogin = self::login(self::$identity3);

        // student call api to send notifications to all administrators
        // members in the course
        \OmegaUp\Controllers\Course::apiRequestFeedback(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'assignment_alias' => self::$assignmentAlias,
                'course_alias' => self::$courseData['course_alias'],
                'guid' => $runData['response']['guid']
            ])
        );
    }

    public function testAdminsHaveCorrectRoles() {
        $this->assertTrue(
            \OmegaUp\Authorization::isCourseAdmin(
                self::$identity2,
                self::$course
            )
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isCourseAdmin(
                self::$identityGroupAdmin,
                self::$course
            )
        );
    }

    public function testTeachingAssistantsHaveCorrectRoles() {
        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                self::$identity,
                self::$course
            )
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                self::$identityGroupTA,
                self::$course
            )
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                self::$identityGroupTA2,
                self::$course
            )
        );
    }

    public function testCanAdminsReceiveNotifications() {
        // Verify if notification has been sent to admins
        $author = \Omegaup\DAO\Users::FindByUsername(
            self::$identity2->username
        );
        if (is_null($author)) {
            return;
        }
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $author
        );
        $this->assertCount(2, $notifications);

        $contents = json_decode($notifications[1]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_REQUEST_FEEDBACK,
            $contents['type']
        );
        $this->assertEquals(
            self::$courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );

        // Verify if notification has been sent to admins group members
        $author = \Omegaup\DAO\Users::FindByUsername(
            self::$identityGroupAdmin->username
        );
        if (is_null($author)) {
            return;
        }
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $author
        );
        $this->assertCount(1, $notifications);

        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_REQUEST_FEEDBACK,
            $contents['type']
        );
        $this->assertEquals(
            self::$courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );
    }

    public function testCanTeachingAssistantsReceiveNotifications() {
        // Verify if notification has been sent to teaching assistants
        $author = \Omegaup\DAO\Users::FindByUsername(
            self::$identity->username
        );
        if (is_null($author)) {
            return;
        }
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $author
        );
        $this->assertCount(2, $notifications);

        $contents = json_decode($notifications[1]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_REQUEST_FEEDBACK,
            $contents['type']
        );
        $this->assertEquals(
            self::$courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );

        // Verify if notification has been sent to a teaching assistants
        // group members
        $author = \Omegaup\DAO\Users::FindByUsername(
            self::$identityGroupTA->username
        );
        if (is_null($author)) {
            return;
        }
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $author
        );
        $this->assertCount(1, $notifications);

        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_REQUEST_FEEDBACK,
            $contents['type']
        );
        $this->assertEquals(
            self::$courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );

        $author = \Omegaup\DAO\Users::FindByUsername(
            self::$identityGroupTA2->username
        );
        if (is_null($author)) {
            return;
        }
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $author
        );
        $this->assertCount(1, $notifications);

        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_REQUEST_FEEDBACK,
            $contents['type']
        );
        $this->assertEquals(
            self::$courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );
    }
}
