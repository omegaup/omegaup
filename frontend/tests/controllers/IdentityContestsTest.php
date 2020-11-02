<?php

/**
 * Testing identity can access to contest and resolve any problem
 *
 * @author juan.pablo
 */
class IdentityContestsTest extends \OmegaUp\Test\ControllerTestCase {
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
        \OmegaUp\Test\Factories\Contest::openContest($contestData, $contestant);

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

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

        return \OmegaUp\Controllers\Run::apiCreate($runRequest);
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
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
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
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }

    /**
     * Basic test for creating a single identity with contests, associating it
     * with a registred user
     */
    public function testChangeAccountForAssociatedIdentities() {
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
        $identityUsername = "{$group['group']->alias}:{$identityName}";
        $password = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $identityUsername,
            'name' => $identityName,
            'password' => $password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        // Create the user to associate
        ['identity' => $user] = \OmegaUp\Test\Factories\User::createUser();

        $userContests = [$identityUsername, $user->username, $user->username];

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

        $login = self::login($user);
        \OmegaUp\Controllers\User::apiAssociateIdentity(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identityUsername,
                'password' => $password,
            ])
        );

        $contestsList = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );

        // User has been invited to 2 contests
        $this->assertEquals(2, $contestsList['number_of_results']);
        $this->assertEquals('Contest_1', $contestsList['results'][0]['title']);
        $this->assertEquals('Contest_2', $contestsList['results'][1]['title']);

        // User switch the account
        $result = \OmegaUp\Controllers\Identity::apiChangeAccount(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identityUsername,
            ])
        );

        $contestsList = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request(['auth_token' => $result['auth_token']])
        );

        // Identity has been invited to 1 contest
        $this->assertEquals(1, $contestsList['number_of_results']);
        $this->assertEquals('Contest_0', $contestsList['results'][0]['title']);
    }
}
