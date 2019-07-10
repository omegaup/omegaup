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

        $this->getRestrictContestForIdentity($unassociatedIdentity, $contestData);
        $this->getRestrictContestForIdentity($associatedIdentity, $contestData, true);
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

        $this->getRestrictCourseForIdentity($unassociatedIdentity, $courseData);
        $this->getRestrictCourseForIdentity($associatedIdentity, $courseData, true);
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

        $this->getRestrictProblemForIdentity($unassociatedIdentity);
        $this->getRestrictProblemForIdentity($associatedIdentity, true);
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

        $this->getRestrictGroupForIdentity($unassociatedIdentity);
        $this->getRestrictGroupForIdentity($associatedIdentity, true);
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

    private function getRestrictContestForIdentity(
        Identities $identity,
        array $contestData,
        bool $isAssociated = false
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        $identityStatus = $isAssociated ? 'Associated' : 'Unassociated';
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
                'start_time' => Time::get(),
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

    private function getRestrictCourseForIdentity(
        Identities $identity,
        array $courseData,
        bool $isAssociated = false
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        $identityStatus = $isAssociated ? 'Associated' : 'Unassociated';
        try {
            CourseController::apiClone(new Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => Utils::CreateRandomString(),
                'alias' => Utils::CreateRandomString(),
                'start_time' => Time::get()
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
                'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
                'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
            ]));
            $this->fail("{$identityStatus} identity can not create courses");
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    private function getRestrictProblemForIdentity(
        Identities $identity,
        bool $isAssociated = false
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        $identityStatus = $isAssociated ? 'Associated' : 'Unassociated';
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

    private function getRestrictGroupForIdentity(
        Identities $identity,
        bool $isAssociated = false
    ) : void {
        // Login with the identity recently created
        $login = OmegaupTestCase::login($identity);

        $identityStatus = $isAssociated ? 'Associated' : 'Unassociated';
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
