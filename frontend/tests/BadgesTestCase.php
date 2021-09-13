<?php

namespace OmegaUp\Test;

/**
 * Parent class of all Test cases for omegaUp badges
 */
class BadgesTestCase extends \OmegaUp\Test\ControllerTestCase {
    /** @psalm-suppress MixedOperand OMEGAUP_ROOT is definitely defined. */
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';
    /** @psalm-suppress MixedOperand OMEGAUP_ROOT is definitely defined. */
    const BADGES_TESTS_ROOT = OMEGAUP_ROOT . '/tests/badges';
    const MAX_BADGE_SIZE = 20 * 1024;
    const ICON_FILE = 'icon.svg';
    const LOCALIZATIONS_FILE = 'localizations.json';
    const QUERY_FILE = 'query.sql';
    const TEST_FILE = 'test.json';

    /**
     * @var \OmegaUp\FileUploader|null
     */
    private $originalFileUploader = null;

    public function setUp(): void {
        parent::setUp();
        \OmegaUp\Time::setTimeForTesting(null);
        $this->originalFileUploader = \OmegaUp\FileHandler::getFileUploader();
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );
    }

    public function tearDown(): void {
        parent::tearDown();
        $this->assertNotNull($this->originalFileUploader);
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->originalFileUploader
        );
    }

    /**
     * @return list<int>
     */
    public static function getSortedResults(string $query) {
        /** @var list<array{user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($query);
        $results = [];
        foreach ($rs as $user) {
            $results[] = $user['user_id'];
        }
        sort($results);
        return $results;
    }

    protected function courseGraduateTest(
        string $courseAlias,
        string $language,
        string $folderName
    ): void {
        // Create problems
        $problems = [];
        for ($i = 0; $i < 10; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create extra problems
        $extraProblems = [];
        for ($i = 0; $i < 10; $i++) {
            $extraProblems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            /*$admin=*/            null,
            /*$adminLogin=*/ null,
            /*$admissionMode=*/ \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
            /*$requestsUserInformation=*/ 'no',
            /*$showScoreboard=*/ 'false',
            /*$startTimeDelay=*/ 0,
            /*$courseDuration=*/ 120,
            /*$assignmentDuration=*/ 120,
            $courseAlias
        );
        $assignmentAlias = $courseData['assignment_alias'];

        // Login
        $login = self::login($courseData['admin']);

        // Add the problems to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            $problems
        );

        // Add the extra problems to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            $extraProblems,
            true
        );

        // Create students
        $students = [];
        $students[0] = \OmegaUp\Test\Factories\User::createUser();
        $students[1] = \OmegaUp\Test\Factories\User::createUser();

        // Add students to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $students[0]['identity']
        );
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $students[1]['identity']
        );

        // One student solves 90% of the problems that are not extra problems,
        // including the extra problems, they were solved only 45%, nevertheless,
        // the student must receive the badge.
        for ($i = 0; $i < 9; $i++) {
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problems[$i],
                $courseData,
                $students[0]['identity'],
                $language
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        // The other student solves only one problem with multiple submissions
        for ($i = 0; $i < 10; $i++) {
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problems[0],
                $courseData,
                $students[1]['identity'],
                $language
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        $queryPath = self::OMEGAUP_BADGES_ROOT . '/' . $folderName . '/' . self::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$students[0]['user']->user_id];
        $this->assertEquals($expected, $results);
    }
}
