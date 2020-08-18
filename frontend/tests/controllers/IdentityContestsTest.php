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
            'languages' => 'c11-gcc',
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
}
