<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class CourseListTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        foreach (range(0, 1) as $course) {
            $publicCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
                /*$admin=*/                null,
                /*$adminLogin=*/null,
                \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
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
        )['smartyProperties']['payload']['courses']['admin']['filteredCourses']['archived']['courses'];
        $this->assertCount(1, $archivedCourses);
        $this->assertEquals(
            $this->courseAliases[3],
            $archivedCourses[0]['alias']
        );
    }
}
