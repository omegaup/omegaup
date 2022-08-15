<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class CourseListTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        foreach (range(0, 1) as $course) {
            $publicCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
                admissionMode: \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
            );
            $this->courseAliases[] = $publicCourseData['course_alias'];
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

    protected $adminUser;
    protected $identity;
    protected $courseAliases;

    public function testListCoursesMine() {
        $adminLogin = self::login($this->adminUser);

        $archivedCourses = \OmegaUp\Controllers\Course::getCourseMineDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
            ])
        )['templateProperties']['payload']['courses']['admin']['filteredCourses']['archived']['courses'];
        $this->assertCount(1, $archivedCourses);
        $this->assertEquals(
            $this->courseAliases[3],
            $archivedCourses[0]['alias']
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

        // All non-archived courses should be dispayed in the courses list for
        // teaching assistants
        $this->assertCount(3, $teachingAssistantCourses);
        $expectedAliases = array_slice($this->courseAliases, 0, 3);

        $this->assertSame(
            sort($expectedAliases),
            sort($teachingAssistantCoursesAliases)
        );
    }
}
