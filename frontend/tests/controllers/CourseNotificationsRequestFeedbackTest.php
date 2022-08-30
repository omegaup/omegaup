<?php

/**
 * Test administrative tasks for teaching assistant team
 */
class CourseNotificationsRequestFeedbackTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCanSendNotificationsToAdministrators() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $assignmentAlias = $courseData['assignment_alias'];

        // create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // add problem to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        // create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $identity3] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $identityGroupTA] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $identityGroupTA2] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $identityGroupAdmin] = \OmegaUp\Test\Factories\User::createUser();

        // add identity like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // add identity2 like an admin
        \OmegaUp\Controllers\Course::apiAddAdmin(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity2->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // add identity3 like student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity3
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $identity3
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        // Get a group
        $groupDataAdmin = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupDataAdmin,
            $identityGroupAdmin
        );

        // Get a group
        $groupDataTA = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupDataTA,
            $identityGroupTA
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupDataTA,
            $identityGroupTA2
        );

        // Prepare request
        $rAdmin = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'group' => $groupDataAdmin['request']['alias'],
            'course_alias' => $courseData['course_alias'],
        ]);

        $rTA = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'group' => $groupDataTA['request']['alias'],
            'course_alias' => $courseData['course_alias'],
        ]);

        // Call api to add group admin
        \OmegaUp\Controllers\Course::apiAddGroupAdmin($rAdmin);

        // Call api to add group teaching assistant
        \OmegaUp\Controllers\Course::apiAddGroupTeachingAssistant($rTA);

        // login teaching assistant
        $userLogin = self::login($identity);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $identity,
                $course
            )
        );

        // newly added admin login
        $userLogin = self::login($identity2);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isCourseAdmin(
                $identity2,
                $course
            )
        );

        // newly added admin login like a group member
        $userLogin = self::login($identityGroupAdmin);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isCourseAdmin(
                $identity2,
                $course
            )
        );

        // login teaching assistant like a group member
        $userLogin = self::login($identityGroupTA);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $identity,
                $course
            )
        );

        $userLogin = self::login($identity3);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        \OmegaUp\Controllers\Course::apiRequestFeedback(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'assignment_alias' => $assignmentAlias,
                'course_alias' => $courseData['course_alias'],
                'run_id' => $runData['response']['guid']
            ])
        );

        // Verify if notification has been sent to an admin
        $author = \Omegaup\DAO\Users::FindByUsername($identity->username);
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
            $courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );

        // Verify if notification has been sent to a teaching assistant
        $author = \Omegaup\DAO\Users::FindByUsername($identity2->username);
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
            $courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );

        // Verify if notification has been sent to a teaching assistant group member
        $author = \Omegaup\DAO\Users::FindByUsername(
            $identityGroupTA->username
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
            $courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );

        // Verify if notification has been sent to aa admin group member
        $author = \Omegaup\DAO\Users::FindByUsername(
            $identityGroupAdmin->username
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
            $courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );
    }
}
