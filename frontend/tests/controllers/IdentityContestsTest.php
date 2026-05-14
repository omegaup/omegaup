<?php
/**
 * Testing identity can access to contest and resolve any problem
 */
class IdentityContestsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * @var string $identityName
     */
    protected $identityUsername;

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var \OmegaUp\DAO\VO\Identities $user
     */
    protected $user;

    /**
     * @var \OmegaUp\Test\ScopedLoginToken $login
     */
    protected $login;

    public function setUp(): void {
        parent::setUp();
        [
            'identity' => $creator,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        // Creator adds 3 contests, in the first one identity will be invited.
        // In the other contests user will be invited.
        // At the end, user associates the account with the identity and it can
        // be able to see all the 3 contests switching between both accounts
        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $this->identityUsername = "{$group['group']->alias}:{$identityName}";
        $this->password = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $this->identityUsername,
            'name' => $identityName,
            'password' => $this->password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        [
            'identity' => $this->user,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $userContests = [
            $this->identityUsername,
            $this->user->username,
            $this->user->username,
        ];

        $contests = [];
        foreach ($userContests as $userId => $username) {
            $contests[$userId] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'private',
                    'title' => "Contest_{$userId}",
                ])
            );

            $directorLogin = self::login($contests[$userId]['director']);
            \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contests[$userId]['request']['alias'],
                'usernameOrEmail' => $username,
            ]));
        }

        $this->login = self::login($this->user);
        \OmegaUp\Controllers\User::apiAssociateIdentity(
            new \OmegaUp\Request([
                'auth_token' => $this->login->auth_token,
                'username' => $this->identityUsername,
                'password' => $this->password,
            ])
        );
    }

    private function createRunWithIdentity(
        array $contestData,
        array $problemData,
        string $username,
        string $password
    ): array {
        // Get an invited identity to login and join the private contest
        $contestant = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $contestant->password = $password;

        // Our contestant has to open the contest before sending a run
        \OmegaUp\Test\Factories\Contest::openContest(
            $contestData['contest'],
            $contestant
        );

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        try {
            $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
            // Create valid run
            $contestantLogin = self::login($contestant);
            $runRequest = new \OmegaUp\Request([
                'auth_token' => $contestantLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['problem_alias'],
                'language' => 'c11-gcc',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);
        } finally {
            unset($detourGrader);
        }

        return \OmegaUp\Controllers\Run::apiCreate($runRequest);
    }

    private function assertUserHasBeenInvitedToContests(
        \OmegaUp\Test\ScopedLoginToken $userLogin,
        array $contests,
        bool $isMainIdentity = true
    ): void {
        $contestsList = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request(['auth_token' => $userLogin->auth_token])
        );

        // User has been invited to contests
        $this->assertSame(
            count($contests),
            $contestsList['number_of_results']
        );
        foreach ($contests as $index => $contest) {
            $this->assertSame(
                $contest,
                $contestsList['results'][$index]['title']
            );
        }

        if (!$isMainIdentity) {
            return;
        }

        $result = \OmegaUp\Controllers\Contest::apiMyList(
            new \OmegaUp\Request(['auth_token' => $userLogin->auth_token])
        );

        $this->assertArrayHasKey(
            'contests',
            $result,
            'Users with main identity should be able to see their contests list'
        );
    }

    /**
     * Test identity joins public contest
     */
    public function testIdentityJoinsContest() {
        // Get a public contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get some problems into the contest
        [$problemData] = \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );

        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        // Set default password for all created identities
        $password = \OmegaUp\Test\Utils::createRandomString();

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                $group['group']->alias,
                $password
            ),
            'group_alias' => $group['group']->alias,
        ]));

        $members = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        [
            $identityPublicContest,
            $uninvitedIdentityPrivateContest,
            $invitedIdentityPrivateContest
        ] = $members['identities'];

        $runResponse = $this->createRunWithIdentity(
            $contestData,
            $problemData,
            $identityPublicContest['username'],
            $password
        );
        $this->assertArrayHasKey('guid', $runResponse);

        // Updating admission_mode for the contest
        $directorLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'private',
        ]));

        // Expictly added identity to contest
        \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'usernameOrEmail' => $invitedIdentityPrivateContest['username'],
        ]));

        $runResponse = $this->createRunWithIdentity(
            $contestData,
            $problemData,
            $invitedIdentityPrivateContest['username'],
            $password
        );
        $this->assertArrayHasKey('guid', $runResponse);

        try {
            // Our contestant tries to open a private contest
            $runResponse = $this->createRunWithIdentity(
                $contestData,
                $problemData,
                $uninvitedIdentityPrivateContest['username'],
                $password
            );
            $this->fail(
                'Only invited identities can access to private contest'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }

    /**
     * Basic test for creating a single identity with contests, associating it
     * with a registered user
     */
    public function testSwitchBetweenAssociatedIdentities() {
        $this->assertUserHasBeenInvitedToContests(
            $this->login,
            contests: ['Contest_1', 'Contest_2']
        );

        // User switch the account
        \OmegaUp\Controllers\Identity::apiSelectIdentity(
            new \OmegaUp\Request([
                'auth_token' => $this->login->auth_token,
                'usernameOrEmail' => $this->identityUsername,
            ])
        );

        $this->assertUserHasBeenInvitedToContests(
            $this->login,
            contests: ['Contest_0'],
            isMainIdentity: false
        );
    }

    /**
     * No main identities should be restricted to do some stuff, like see their
     * contests list, even when they have been associated to a user.
     */
    public function testIdentityPrivilegesAndRestrictions() {
        // User switch the account
        \OmegaUp\Controllers\Identity::apiSelectIdentity(
            new \OmegaUp\Request([
                'auth_token' => $this->login->auth_token,
                'usernameOrEmail' => $this->identityUsername,
            ])
        );

        try {
            \OmegaUp\Controllers\Contest::apiMyList(new \OmegaUp\Request([
                'auth_token' => $this->login->auth_token
            ]));
            $this->fail('identity does not have access to see apiMyList');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        // This account can select the main identity
        \OmegaUp\Controllers\Identity::apiSelectIdentity(
            new \OmegaUp\Request([
                'auth_token' => $this->login->auth_token,
                'usernameOrEmail' => $this->user->username,
            ])
        );

        $this->assertUserHasBeenInvitedToContests(
            $this->login,
            contests: ['Contest_1', 'Contest_2']
        );
    }

    /**
     * User login with a no-main identity is no able to switch between accounts
     */
    public function testUserLoggedAsIdentityCanNotSelectOtherIdentity() {
        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $this->identityUsername
        );
        $identity->password = $this->password;

        $this->login = self::login($identity);

        $this->assertUserHasBeenInvitedToContests(
            $this->login,
            contests: ['Contest_0'],
            isMainIdentity: false
        );

        // User switch the account
        try {
            \OmegaUp\Controllers\Identity::apiSelectIdentity(
                new \OmegaUp\Request([
                    'auth_token' => $this->login->auth_token,
                    'usernameOrEmail' => $this->user->username,
                ])
            );
            $this->fail(
                'identity should not have been able to switch identities'
            );
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testAddUsersToContestForTeams() {
        // Create the user to associate
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestForTeams' => true,
                'teamsGroupAlias' => $teamGroup->alias,
            ])
        );

        $login = self::login($contestData['director']);

        // Add users to contest for teams is not allowed
        try {
            \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'usernameOrEmail' => $identity->username,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'usersCanNotBeAddedInContestForTeams',
                $e->getMessage()
            );
        }
    }

    public function testAddGroupsToContestForTeams() {
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestForTeams' => true,
                'teamsGroupAlias' => $teamGroup->alias,
            ])
        );

        $login = self::login($contestData['director']);

        $groupData = \OmegaUp\Test\Factories\Groups::createGroup(
            login: $login,
        );

        // Add groups to contest for teams is not allowed
        try {
            \OmegaUp\Controllers\Contest::apiAddGroup(
                new \OmegaUp\Request([
                    'contest_alias' => strval($contestData['request']['alias']),
                    'group' => $groupData['group']->alias,
                    'auth_token' => $login->auth_token,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'groupsCanNotBeAddedInContestForTeams',
                $e->getMessage()
            );
        }
    }
}
