<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * A contest might require registration to participate on it.
 */
class RegisterToContestTest extends \OmegaUp\Test\ControllerTestCase {
    // Intro is the page that you see just before joining a contest
    // listing all the contest details.
    public function testIntro() {
        // Create contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $contestIdentityAdmin
        );

        // Contest will start in the future:
        $adminLogin = self::login($contestIdentityAdmin);
        $request = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
            'start_time' => \OmegaUp\Time::get() + 60 * 60,
            'languages' => 'c11-gcc',
        ]);
        $request['finish_time'] = $request['start_time'] + 60;
        \OmegaUp\Controllers\Contest::apiUpdate($request);

        // Contestant will try to open the contes, this should fail
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $contestantLogin = self::login($identity);
        try {
            $response = \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $contestantLogin->auth_token,
            ]));
            $this->AssertFalse(
                true,
                'User gained access to contest even though its registration needed.'
            );
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $fae) {
            // Expected contestNotStarted exception. Continue.
        }

        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $identity,
            $contestData['contest']
        );
        $this->assertSame(
            $showIntro,
            \OmegaUp\Controllers\Contest::SHOW_INTRO
        );

        // Contest is going on right now
        $adminLogin = self::login($contestIdentityAdmin);
        $request = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
            'start_time' => \OmegaUp\Time::get() - 1,
            'languages' => 'c11-gcc',
        ]);
        $request['finish_time'] = $request['start_time'] + 60;
        \OmegaUp\Controllers\Contest::apiUpdate($request);

        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $identity,
            $contestData['contest']
        );
        $this->assertSame(
            $showIntro,
            \OmegaUp\Controllers\Contest::SHOW_INTRO
        );

        $contestantLogin = self::login($identity);

        // Join this contest
        $response = \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]));

        // Now that i have joined the contest, i should not see the intro
        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $identity,
            $contestData['contest']
        );
        $this->assertSame(
            $showIntro,
            !\OmegaUp\Controllers\Contest::SHOW_INTRO
        );
    }

    /**
     * Testing if intro must be shown
     */
    public function testShowIntro() {
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        // user can now submit to contest
        $showIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $identity,
            $contestData['contest']
        );

        $this->assertTrue($showIntro);
    }

    //pruebas (público, privado) x (usuario mortal, admin, invitado)
    //pruebas extra para distinguir entre invitado y ya entrado al concurso
    public function testSimpleRegistrationActions() {
        // create a contest and its admin
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = \OmegaUp\Test\Factories\User::createUser();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['contestDirector' => $contestIdentityAdmin]
            )
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // make it "registrable"
        self::log('Update contest to make it registrable');
        $adminLogin = self::login($contestIdentityAdmin);
        $r1 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
            'languages' => 'c11-gcc',
        ]);
        \OmegaUp\Controllers\Contest::apiUpdate($r1);

        // some user asks for contest
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        try {
            $response = \OmegaUp\Controllers\Contest::apiDetails($r2);
            $this->fail(
                'User gained access to contest even though its registration needed.'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        self::log('user registers into contest');
        \OmegaUp\Controllers\Contest::apiRegisterForContest($r2);

        // admin lists registrations
        $adminLogin = self::login($contestIdentityAdmin);
        $r3 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $adminLogin->auth_token,
        ]);
        $result = \OmegaUp\Controllers\Contest::apiRequests($r3);
        $this->assertSame(sizeof($result['users']), 1);

        self::log('admin rejects registration');
        $r3['username'] = $identity->username;
        $r3['resolution'] = false;
        \OmegaUp\Controllers\Contest::apiArbitrateRequest($r3);

        // ask for details again, this should fail again
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        try {
            $response = \OmegaUp\Controllers\Contest::apiDetails($r2);
            $this->fail(
                'User gained access to contest even though its registration needed.'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $fae) {
            // Expected. Continue.
        }

        // admin admits user
        $r3['username'] = $identity->username;
        $r3['resolution'] = true;
        \OmegaUp\Controllers\Contest::apiArbitrateRequest($r3);

        // user can now submit to contest
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r2);

        \OmegaUp\Controllers\Contest::apiDetails($r2);
    }

    public function testUserCannotSelfApprove() {
        // create a contest and its admin
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $contestIdentityAdmin
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // make it "registrable"
        $adminLogin = self::login($contestIdentityAdmin);
        $r1 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]);
        \OmegaUp\Controllers\Contest::apiUpdate($r1);

        // some user asks for contest
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $contestantLogin = self::login($identity);
        $r2 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]);
        \OmegaUp\Controllers\Contest::apiRegisterForContest($r2);

        $r3 = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
            'username' => $identity->username,
            'resolution' => true,
        ]);

        try {
            \OmegaUp\Controllers\Contest::apiArbitrateRequest($r3);
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $fae) {
            // Expected. Continue.
        }
    }

    /**
     * Test user cannot join the contest because he doesn't have registered
     * his basic profile information (country, state and school)
     */
    public function testUserMissingBasicInformation() {
        // create a contest and its admin
        [
            'user' => $contestAdmin,
            'identity' => $contestIdentityAdmin,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams([
            'contestDirector' => $contestIdentityAdmin,
        ]));

        $adminLogin = self::login($contestIdentityAdmin);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'needs_basic_information' => 1,
            'auth_token' => $adminLogin->auth_token,
            'languages' => 'c11-gcc',
        ]));

        // Contestant will try to open the contest, it should fail
        [
            'user' => $contestant,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $contestantLogin = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $contestantLogin->auth_token,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'contestBasicInformationNeeded',
                $e->getMessage()
            );
        }
    }

    /**
     * Test user joins the contest
     */
    public function testUserAllowedJoinTheContest() {
        // Create a school
        $school = \OmegaUp\Test\Factories\Schools::createSchool();

        // create a contest and its admin
        ['user' => $contestAdmin, 'identity' => $contestIdentityAdmin] = \OmegaUp\Test\Factories\User::createUser();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams([
            'contestDirector' => $contestIdentityAdmin,
        ]));

        // Updates contest, with basic information needed
        $adminLogin = self::login($contestIdentityAdmin);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'basic_information' => 1,
            'auth_token' => $adminLogin->auth_token,
            'languages' => 'c11-gcc',
        ]));

        // Contestant will try to open the contes, this should fail
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Updates contestant, with basic information
        $contestantLogin = self::login($identity);
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $contestantLogin->auth_token,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_id' => $school['school']->school_id
        ]));

        $contest = \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $contestantLogin->auth_token,
        ]));

        $this->assertSame('ok', $contest['status']);
    }

    /**
     * Testing identities can access to contest, but there are invited
     * and no invited
     */
    public function testIdentitiesInvitedAndNoInvitedToContest() {
        // Creating 5 identities, and inviting them to the contest
        $numberOfInvitedContestants = 5;
        $invitedContestants = [];
        $invitedContestantIdentities = [];
        for ($i = 0; $i < $numberOfInvitedContestants; $i++) {
            [
                'user' => $invitedContestants[],
                'identity' => $invitedContestantIdentities[],
            ] = \OmegaUp\Test\Factories\User::createUser();
        }
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'public']
            )
        );
        foreach ($invitedContestantIdentities as $contestant) {
            \OmegaUp\Test\Factories\Contest::addUser($contestData, $contestant);
        }
        // Creating 3 identities without an invitation to join the contest
        $numberOfNotInvitedContestants = 3;
        $uninvitedContestants = [];
        $uninvitedContestantIdentities = [];
        for ($i = 0; $i < $numberOfNotInvitedContestants; $i++) {
            [
                'user' => $uninvitedContestants[],
                'identity' => $uninvitedContestantIdentities[],
            ] = \OmegaUp\Test\Factories\User::createUser();
        }

        // All identities join the contest
        foreach ($uninvitedContestantIdentities as $contestant) {
            $contestantLogin = self::login($contestant);

            \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $contestantLogin->auth_token,
            ]));
        }

        $problemsetIdentities = \OmegaUp\DAO\ProblemsetIdentities::getIdentitiesByProblemset(
            $contestData['contest']->problemset_id
        );

        $this->assertSame(
            count(
                $problemsetIdentities
            ),
            ($numberOfInvitedContestants + $numberOfNotInvitedContestants)
        );

        $this->assertIdentitiesAreInCorrectList(
            $invitedContestants,
            true /*isInvited*/,
            $problemsetIdentities
        );

        $this->assertIdentitiesAreInCorrectList(
            $uninvitedContestants,
            false /*isInvited*/,
            $problemsetIdentities
        );
    }

    public function testAccessRequestNoNeededToInvitedIdentities() {
        // create a contest and its admin
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $admin);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // make it "registrable"
        $adminLogin = self::login($admin);

        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Create two users
        ['identity' => $invited] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $uninvited] = \OmegaUp\Test\Factories\User::createUser();

        // The first one is explicitly invited
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $invited);

        // And can access to the contest
        $invitedLogin = self::login($invited);

        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $invitedLogin->auth_token,
        ]));

        // The second one needs request access to the contest
        $uninvitedLogin = self::login($uninvited);

        try {
            \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $uninvitedLogin->auth_token,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('contestNotRegistered', $e->getMessage());
        }
    }

    /**
     * Once contest admission mode is updated to "open with registration",
     * all the contestants previously added should be accepted
     */
    public function testAddAllContestantsToContestWithRegsitration() {
        // Create contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Creating 2 identities, and inviting them to the contest
        $invitedContestants = [];
        foreach (range(0, 2) as $number) {
            [
                'identity' => $invitedContestants[$number],
            ] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $invitedContestants[$number]
            );
        }
        ['identity' => $uninvited] = \OmegaUp\Test\Factories\User::createUser();

        // Update admission_mode to registration
        $adminLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'registration',
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Invited contestants should access without a new request
        foreach ($invitedContestants as $contestant) {
            $login = self::login($contestant);
            $result = \OmegaUp\Controllers\Contest::apiOpen(
                new \OmegaUp\Request([
                    'contest_alias' => $contestData['request']['alias'],
                    'auth_token' => $login->auth_token,
                ])
            );
            $this->assertSame($result['status'], 'ok');
        }

        // Uninvited user should not have access
        $uninvitedLogin = self::login($uninvited);

        try {
            \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $uninvitedLogin->auth_token,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('contestNotRegistered', $e->getMessage());
        }
    }

    private function assertIdentitiesAreInCorrectList(
        $contestants,
        bool $isInvited,
        $identities
    ) {
        foreach ($contestants as $contestant) {
            $this->assertArrayContainsWithPredicate(
                $identities,
                fn ($identity) => (
                    $identity['user_id'] == $contestant->user_id &&
                    $identity['is_invited'] == $isInvited
                )
            );
        }
    }
}
