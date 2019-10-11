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
        \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
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
        \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
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
        \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
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
        \OmegaUp\Controllers\User::apiAssociateIdentity(new \OmegaUp\Request([
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
        ['user' => $creator, 'identity' => $creatorIdentity] = UserFactory::createGroupIdentityCreator();
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
        \OmegaUp\DAO\VO\Identities $identity,
        array $contestData,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiMyList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail("{$identityStatus} identity does not have access to see apiMyList");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            \OmegaUp\Controllers\Contest::apiCreateVirtual(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'alias' => $contestData['contest']->alias,
            ]));
            $this->fail("{$identityStatus} identity can not create virtual contests");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            \OmegaUp\Controllers\Contest::apiClone(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'title' => Utils::CreateRandomString(),
                'description' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'start_time' => \OmegaUp\Time::get(),
            ]));
            $this->fail("{$identityStatus} identity can not clone contests");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            ContestsFactory::createContest(new ContestParams([
                'contestDirector' => $identity
            ]));
            $this->fail('unassociated identity can not create contests');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    private function assertCourseRestrictionsForIdentity(
        \OmegaUp\DAO\VO\Identities $identity,
        array $courseData,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiClone(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail("{$identityStatus} identity can not clone courses");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'description' => Utils::CreateRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120)
            ]));
            $this->fail("{$identityStatus} identity can not create courses");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    private function assertProblemRestrictionsForIdentity(
        \OmegaUp\DAO\VO\Identities $identity,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Problem::apiMyList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail("{$identityStatus} identity does not have access to see apiMyList");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            // try to create a problem
            $problemData = ProblemsFactory::createProblem(null, $login);
            $this->fail("{$identityStatus} identity can not create problems");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    private function assertGroupRestrictionsForIdentity(
        \OmegaUp\DAO\VO\Identities $identity,
        string $identityStatus
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        try {
            \OmegaUp\Controllers\Group::apiMyList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ]));
            $this->fail("{$identityStatus} identity does not have access to see apiMyList");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
