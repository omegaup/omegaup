<?php

/**
 * Testing identity can access to contest and resolve any problem
 *
 * @author juan.pablo
 */
class IdentityContestsTest extends OmegaupTestCase {
    /**
     * Test identity joins public contest
     */
    public function testIdentityJoinsContest() {
        // Get a public contest
        $contestData = ContestsFactory::createContest(new ContestParams());

        // Get some problems into the contest
        [$problemData] = ContestsFactory::insertProblemsInContest($contestData);

        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        // Set default password for all created identities
        $password = Utils::CreateRandomString();

        // Call api using identity creator group member
        IdentityController::apiBulkCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => IdentityFactory::getCsvData(
                'identities.csv',
                $group['group']->alias,
                $password
            ),
            'group_alias' => $group['group']->alias,
        ]));

        $members = GroupController::apiMembers(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        [
            $identityPublicContest,
            $uninvitedIdentityPrivateContest,
            $invitedIdentityPrivateContest
        ] = $members['identities'];

        // Get an uninvited identity to login and join the public contest
        $contestant = IdentityController::resolveIdentity(
            $identityPublicContest['username']
        );
        $contestant->password = $password;

        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        $runRequest = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        $detourGrader = new ScopedGraderDetour();

        // Call API
        $contestantLogin = self::login($contestant);
        $runRequest['auth_token'] = $contestantLogin->auth_token;
        $response = RunController::apiCreate($runRequest);

        $this->assertEquals('ok', $response['status']);

        // Updating admission_mode for the contest
        $directorLogin = self::login($contestData['director']);
        ContestController::apiUpdate(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'private',
        ]));

        // Expictly added identity to contest
        ContestController::apiAddUser(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'usernameOrEmail' => $invitedIdentityPrivateContest['username'],
        ]));

        // Get an invited identity to login and join the private contest
        $contestant = IdentityController::resolveIdentity(
            $invitedIdentityPrivateContest['username']
        );
        $contestant->password = $password;

        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        $detourGrader = new ScopedGraderDetour();

        // Create valid run
        $contestantLogin = self::login($contestant);
        $runRequest['auth_token'] = $contestantLogin->auth_token;
        $response = RunController::apiCreate($runRequest);

        $this->assertEquals('ok', $response['status']);

        // Get an uninvited identity to try to join the private contest
        $contestant = IdentityController::resolveIdentity(
            $uninvitedIdentityPrivateContest['username']
        );
        $contestant->password = $password;

        try {
            // Our contestant tries to open a private contest
            ContestsFactory::openContest($contestData, $contestant);
            $this->fail('Only invited identities can access to private contest');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }
}
