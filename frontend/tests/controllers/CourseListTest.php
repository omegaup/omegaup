<?php
class CourseListTest extends \OmegaUp\Test\ControllerTestCase {
    protected $adminUser;
    protected $identity;
    protected $courseAliases;

    public function setUp(): void {
        parent::setUp();
        foreach (range(0, 1) as $index) {
            $publicCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
                admissionMode: \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
            );
            $this->courseAliases[$index] = $publicCourseData['course_alias'];
        }
        $privateCourseData = \OmegaUp\Test\Factories\Course::createCourseWithNAssignmentsPerType(
            ['homework' => 3, 'test' => 2]
        );
        $this->courseAliases[] = $privateCourseData['course_alias'];

        $this->adminUser = $privateCourseData['admin'];
        [
            'identity' => $this->identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $privateCourseData,
            $this->identity
        );

        // This course shouldn't affect all the tests as it won't be listed
        $publicArchivedCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $this->adminUser,
            self::login($this->adminUser),
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        $archivedCourse = \OmegaUp\DAO\Courses::getByPK(
            $publicArchivedCourseData['course']->course_id
        );
        $archivedCourse->archived = 1;
        \OmegaUp\DAO\Courses::update($archivedCourse);
        $this->courseAliases[] = $publicArchivedCourseData['course_alias'];
    }

    public function testListCoursesMine() {
        $adminLogin = self::login($this->adminUser);

        $archivedCourses = \OmegaUp\Controllers\Course::getCourseMineDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
            ])
        )['templateProperties']['payload']['courses']['admin']['filteredCourses']['archived']['courses'];
        $this->assertCount(1, $archivedCourses);
        $this->assertSame(
            $this->courseAliases[3],
            $archivedCourses[0]['alias']
        );
    }

    /**
     * The teaching assistant in a course should not see duplicated courses,
     * even if this user was added to the course as participant.
     */
    public function testListNonDuplicatedCoursesMineForTeachingAssistant() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $adminLogin = self::login($admin);

        // create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $teachingAssistantLogin = self::login($identity);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $teachingAssistantLogin
        );

        // admin is able to add a teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $courses = \OmegaUp\Controllers\Course::getCourseMineDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
            ])
        )['templateProperties']['payload']['courses']['admin'];

        // Only one course should be listed
        $this->assertCount(
            1,
            $courses['filteredCourses']['teachingAssistant']['courses']
        );

        $this->assertEmpty($courses['filteredCourses']['current']['courses']);
        $this->assertEmpty($courses['filteredCourses']['past']['courses']);
        $this->assertEmpty($courses['filteredCourses']['archived']['courses']);
    }

    /**
     * The course admin should not see duplicated courses even if this user was
     * added to an admin group.
     */
    public function testListNonDuplicatedCoursesMineForAdmin() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $groupData = \OmegaUp\Test\Factories\Groups::createGroup(
            owner: $courseData['admin']
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $courseData['admin']
        );
        $login = self::login($courseData['admin']);
        \OmegaUp\Controllers\Course::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'course_alias' => $courseData['course_alias'],
        ]));
        $courses = \OmegaUp\Controllers\Course::getCourseMineDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload']['courses']['admin'];

        // Only one course should be listed
        $this->assertCount(
            1,
            $courses['filteredCourses']['current']['courses']
        );
    }

    public function testListCoursesMineForTeachingAssistant() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $adminLogin = self::login($admin);

        // create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // admin is able to add a teaching assistant
        foreach ($this->courseAliases as $alias) {
            \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'usernameOrEmail' => $identity->username,
                    'course_alias' => $alias,
                ])
            );
        }

        // Teaching assistant login
        $userLogin = self::login($identity);

        $teachingAssistantCourses = \OmegaUp\Controllers\Course::getCourseMineDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
            ])
        )['templateProperties']['payload']['courses']['admin']['filteredCourses']['teachingAssistant']['courses'];

        $teachingAssistantCoursesAliases = array_map(
            fn ($course) => $course['alias'],
            $teachingAssistantCourses
        );

        // All non-archived courses should be displayed in the courses list for
        // teaching assistants
        $this->assertCount(3, $teachingAssistantCourses);
        $expectedAliases = array_slice($this->courseAliases, 0, 3);

        $this->assertSame(
            sort($expectedAliases),
            sort($teachingAssistantCoursesAliases)
        );
    }
}
