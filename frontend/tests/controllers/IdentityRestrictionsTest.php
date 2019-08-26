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
        [
            $unassociatedIdentity,
            $associatedIdentity
        ] = self::createGroupIdentityCreatorAndGroup($password);

        // Create a new user to associate with identity
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Associate identity with user
        UserController::apiAssociateIdentity(new Request([
            'auth_token' => $login->auth_token,
            'username' => $associatedIdentity->username,
            'password' => $associatedIdentity->password,
        ]));

        $this->assertContestRestrictionsForIdentity(
            $unassociatedIdentity,
            $contestData,
            'Unassociated'
        );
        $this->assertContestRestrictionsForIdentity(
            $associatedIdentity,
            $contestData,
            'Associated'
        );
    }

    /**
     * Restricted Course APIs for unassociated identities.
     */
    public function testRestrictionsForCourses() {
        // Create a course with admin privileges (main identity can do that)
        $courseData = CoursesFactory::createCourse();

        // Create a group, a set of identities, get one of them
        $password = Utils::CreateRandomString();
        [
            $unassociatedIdentity,
            $associatedIdentity
        ] = self::createGroupIdentityCreatorAndGroup($password);

        // Create a new user to associate with identity
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Associate identity with user
        UserController::apiAssociateIdentity(new Request([
            'auth_token' => $login->auth_token,
            'username' => $associatedIdentity->username,
            'password' => $associatedIdentity->password,
        ]));

        $this->assertCourseRestrictionsForIdentity(
            $unassociatedIdentity,
            $courseData,
            'Unassociated'
        );
        $this->assertCourseRestrictionsForIdentity(
            $associatedIdentity,
            $courseData,
            'Associated'
        );
    }

    /**
     * Restricted Problem APIs for unassociated identities.
     */
    public function testRestrictionsForProblems() {
        // Create a group, a set of identities, get one of them
        $password = Utils::CreateRandomString();
        [
            $unassociatedIdentity,
            $associatedIdentity
        ] = self::createGroupIdentityCreatorAndGroup($password);

        // Create a new user to associate with identity
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Associate identity with user
        UserController::apiAssociateIdentity(new Request([
            'auth_token' => $login->auth_token,
            'username' => $associatedIdentity->username,
            'password' => $associatedIdentity->password,
        ]));

        $this->assertProblemRestrictionsForIdentity(
            $unassociatedIdentity,
            'Unassociated'
        );
        $this->assertProblemRestrictionsForIdentity(
            $associatedIdentity,
            'Associated'
        );
    }

    /**
     * Restricted Group APIs for unassociated identities.
     */
    public function testRestrictionsForGroups() {
        // Create a group, a set of identities, get one of them
        $password = Utils::CreateRandomString();
        [
            $unassociatedIdentity,
            $associatedIdentity
        ] = self::createGroupIdentityCreatorAndGroup($password);

        // Create a new user to associate with identity
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Associate identity with user
        UserController::apiAssociateIdentity(new Request([
            'auth_token' => $login->auth_token,
            'username' => $associatedIdentity->username,
            'password' => $associatedIdentity->password,
        ]));

        $this->assertGroupRestrictionsForIdentity(
            $unassociatedIdentity,
            'Unassociated'
        );
        $this->assertGroupRestrictionsForIdentity(
            $associatedIdentity,
            'Associated'
        );
    }

    private static function createGroupIdentityCreatorAndGroup(
        string $password
    ) : array {
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

        // Create identities and get one unassociated and other one to be
        // associated with a user
        return IdentityFactory::createIdentitiesFromAGroup(
            $group,
            $creatorLogin,
            $password
        );
    }

    private function assertContestRestrictionsForIdentity(
        Identities $identity,
        array $contestData,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            ContestController::apiMyList(new Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail("{$identityStatus} identity does not have access to see apiMyList");
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            ContestController::apiCreateVirtual(new Request([
                'auth_token' => $login->auth_token,
                'alias' => $contestData['contest']->alias,
            ]));
            $this->fail("{$identityStatus} identity can not create virtual contests");
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
                'start_time' => \OmegaUp\Time::get(),
            ]));
            $this->fail("{$identityStatus} identity can not clone contests");
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

    private function assertCourseRestrictionsForIdentity(
        Identities $identity,
        array $courseData,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            CourseController::apiClone(new Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail("{$identityStatus} identity can not clone courses");
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            CourseController::apiCreate(new Request([
                'auth_token' => $login->auth_token,
                'name' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'description' => Utils::CreateRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120)
            ]));
            $this->fail("{$identityStatus} identity can not create courses");
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    private function assertProblemRestrictionsForIdentity(
        Identities $identity,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            ProblemController::apiMyList(new Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail("{$identityStatus} identity does not have access to see apiMyList");
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            // try to create a problem
            $problemData = ProblemsFactory::createProblem(null, $login);
            $this->fail("{$identityStatus} identity can not create problems");
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    private function assertGroupRestrictionsForIdentity(
        Identities $identity,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            GroupController::apiMyList(new Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail("{$identityStatus} identity does not have access to see apiMyList");
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
