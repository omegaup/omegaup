<?php

/**
 * Tests API's where unassociated identities does not have access.
 */
class IdentityRestrictionsTest extends OmegaupTestCase {
    /**
     * Restricted Conests APIs for unassociated identities.
     */
    public function testRestrictionsForContests() {
        // Create a contest with admin privileges (main identity can do that)
        $contestData = ContestsFactory::createContest();

        // Create a group, a set of identities, get one of them
        $password = Utils::CreateRandomString();
        $identity = self::createGroupIdentityCreatorAndGroup($password);

        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            ContestController::apiMyList(new Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail('unassociated identity does not have access to see apiMyList');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            ContestController::apiCreateVirtual(new Request([
                'auth_token' => $login->auth_token,
                'alias' => $contestData['contest']->alias,
            ]));
            $this->fail('unassociated identity can not create virtual contests');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            ContestController::apiClone(new Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'title' => Utils::CreateRandomString(),
                'description' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'start_time' => Time::get(),
            ]));
            $this->fail('unassociated identity can not clone contests');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            ContestsFactory::createContest(new ContestParams([
                'contestDirector' => $identity
            ]));
            $this->fail('unassociated identity can not create contests');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Restricted Course APIs for unassociated identities.
     */
    public function testRestrictionsForCourses() {
        // Create a course with admin privileges (main identity can do that)
        $courseData = CoursesFactory::createCourse();

        // Create a group, a set of identities, get one of them
        $password = Utils::CreateRandomString();
        $identity = self::createGroupIdentityCreatorAndGroup($password);

        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            CourseController::apiClone(new Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'start_time' => Time::get()
            ]));
            $this->fail('unassociated identity can not clone courses');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            CourseController::apiCreate(new Request([
                'auth_token' => $login->auth_token,
                'name' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'description' => Utils::CreateRandomString(),
                'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
                'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
            ]));
            $this->fail('unassociated identity can not create courses');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Restricted Problem APIs for unassociated identities.
     */
    public function testRestrictionsForProblems() {
        // Create a group, a set of identities, get one of them
        $password = Utils::CreateRandomString();
        $identity = self::createGroupIdentityCreatorAndGroup($password);

        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            ProblemController::apiMyList(new Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail('unassociated identity does not have access to see apiMyList');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            // try to create a problem
            $problemData = ProblemsFactory::createProblem(null, $login);
            $this->fail('unassociated identity can not create problems');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Restricted Group APIs for unassociated identities.
     */
    public function testRestrictionsForGroups() {
        // Create a group, a set of identities, get one of them
        $password = Utils::CreateRandomString();
        $identity = self::createGroupIdentityCreatorAndGroup($password);

        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            GroupController::apiMyList(new Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail('unassociated identity does not have access to see apiMyList');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    private static function createGroupIdentityCreatorAndGroup(string $password) {
        // Add a new user with identity groups creator privileges, and login
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);

        // Create a group, where identities will be added
        $group = GroupsFactory::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        )['group'];

        // Create identities and get one for testing
        return IdentityFactory::createIdentitiesFromAGroup(
            $group,
            $creatorLogin,
            $password
        );
    }
}
