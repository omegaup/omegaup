<?php

/**
 * VirtualContestTest
 */

class VirtualContestTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * @return array{virtualContests: list<\OmegaUp\DAO\VO\Contests>, originalContest: \OmegaUp\DAO\VO\Contests, identity: \OmegaUp\DAO\VO\Identities}
     */
    private static function createVirtualContest(
        int $numberOfVirtualContests = 1,
        string $admissionMode = 'public'
    ) {
        // create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => $admissionMode]
            )
        );

        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add problem to contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Let assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $virtualContests = [];
        foreach (range(0, $numberOfVirtualContests - 1) as $virtualIndex) {
            $response = \OmegaUp\Controllers\Contest::apiCreateVirtual(
                new \OmegaUp\Request([
                    'alias' => $contestData['request']['alias'],
                    'auth_token' => $login->auth_token,
                ])
            );

            // Get generated virtual contest alias
            $virtualContestAlias = $response['alias'];

            $virtualContests[$virtualIndex] = \OmegaUp\DAO\Contests::getByAlias(
                $virtualContestAlias
            );
        }

        $originalContest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        return [
            'virtualContests' => $virtualContests,
            'originalContest' => $originalContest,
            'identity' => $identity,
        ];
    }

    public function testCreateVirtualContest() {
        [
            'virtualContests' => $virtualContests,
            'originalContest' => $originalContest,
            'identity' => $identity,
        ] = self::createVirtualContest();

        $virtualContest = $virtualContests[0];

        $this->assertNull($originalContest->rerun_id);

        // Assert virtual contest
        $this->assertSame(
            $originalContest->contest_id,
            $virtualContest->rerun_id
        );
        $this->assertSame($originalContest->title, $virtualContest->title);
        $this->assertSame(
            $originalContest->description,
            $virtualContest->description
        );
        $this->assertSame('private', $virtualContest->admission_mode); // Virtual contest must be private
        $this->assertSame(
            $originalContest->scoreboard,
            $virtualContest->scoreboard
        );
        $this->assertSame(
            $originalContest->points_decay_factor,
            $virtualContest->points_decay_factor
        );
        $this->assertSame(
            $originalContest->score_mode,
            $virtualContest->score_mode
        );
        $this->assertSame(
            $originalContest->submissions_gap,
            $virtualContest->submissions_gap
        );
        $this->assertSame(
            $originalContest->feedback,
            $virtualContest->feedback
        );
        $this->assertSame(
            $originalContest->penalty,
            $virtualContest->penalty
        );
        $this->assertSame(
            $originalContest->penalty_type,
            $virtualContest->penalty_type
        );
        $this->assertSame(
            $originalContest->penalty_calc_policy,
            $virtualContest->penalty_calc_policy
        );
        $this->assertSame(
            $originalContest->languages,
            $virtualContest->languages
        );

        // Assert virtual contest problemset problems
        $originalProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $originalContest->problemset_id,
            needSubmissions: false
        );
        $virtualProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $virtualContest->problemset_id,
            needSubmissions: false
        );
        // Number of problems must be equal
        $this->assertSame(count($originalProblems), count($virtualProblems));

        // Because we only put one problem in contest we can assert only the first element
        $this->assertSame($originalProblems[0], $virtualProblems[0]);

        \OmegaUp\Test\Factories\Contest::openContest(
            $virtualContest,
            $identity
        );

        $login = self::login($identity);

        $result = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'contest_alias' => $virtualContest->alias,
                'auth_token' => $login->auth_token,
            ])
        );

        $response = $result['templateProperties']['payload'];

        // Virtual contests have information of the original contest
        $this->assertArrayHasKey('original', $response);

        $this->assertArrayHasKey('contest', $response['original']);
        $this->assertArrayHasKey('scoreboard', $response['original']);
        $this->assertArrayHasKey('scoreboardEvents', $response['original']);

        $this->assertSame(
            $originalContest->alias,
            $response['original']['contest']->alias
        );
        $this->assertSame('arena_contest_virtual', $result['entrypoint']);
    }

    public function testAddParticipantsInVirtualContest() {
        [
            'virtualContests' => $virtualContests,
            'identity' => $identity,
        ] = self::createVirtualContest();

        $virtualContest = $virtualContests[0];

        // Create 5 participants for the virtual contest
        $numberOfParticipants = 5;
        $participants = [];
        foreach (range(0, $numberOfParticipants - 1) as $participantIndex) {
            ['identity' => $participants[$participantIndex]] = \OmegaUp\Test\Factories\User::createUser();
        }

        $login = self::login($identity);

        foreach ($participants as $participant) {
            \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
                'contest_alias' => $virtualContest->alias,
                'usernameOrEmail' => $participant->username,
                'auth_token' => $login->auth_token,
            ]));
        }

        $response = \OmegaUp\Controllers\Contest::apiUsers(
            new \OmegaUp\Request([
                'contest_alias' => $virtualContest->alias,
                'auth_token' => $login->auth_token,
            ])
        );

        // New added users should appear in the list
        $this->assertCount($numberOfParticipants, $response['users']);

        foreach ($response['users'] as $p) {
            $this->assertArrayContainsWithPredicate(
                $participants,
                fn ($value) => $value->username == $p['username']
            );
        }
    }

    public function testAddedParticipantJoinsToVirtualContest() {
        [
            'virtualContests' => $virtualContests,
            'identity' => $identity,
        ] = self::createVirtualContest();

        $virtualContest = $virtualContests[0];

        $login = self::login($identity);

        // Create a new participant and add them to virtual contest
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
            'contest_alias' => $virtualContest->alias,
            'usernameOrEmail' => $participant->username,
            'auth_token' => $login->auth_token,
        ]));

        // User joins the virtual contest where they were assigned
        $login = self::login($participant);

        $result = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'contest_alias' => $virtualContest->alias,
                'auth_token' => $login->auth_token,
            ])
        );

        // Assert contest is virtual
        $this->assertNotNull(
            $result['templateProperties']['payload']['contest']['rerun_id']
        );

        // User doesn't join to the virtual contest yet
        $this->assertSame($result['entrypoint'], 'contest_intro');

        \OmegaUp\Test\Factories\Contest::openContest(
            $virtualContest,
            $participant
        );

        $login = self::login($participant);

        $result = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'contest_alias' => $virtualContests[0]->alias,
                'auth_token' => $login->auth_token,
            ])
        );

        // User joins to the virtual contest
        $this->assertSame($result['entrypoint'], 'arena_contest_virtual');
    }

    public function testParticipantsCanNotBeAddedToVirtualContestWhenOriginalContestIsPrivate() {
        [
            'virtualContests' => $virtualContests,
            'identity' => $identity,
        ] = self::createVirtualContest(admissionMode: 'private');

        $virtualContest = $virtualContests[0];

        $login = self::login($identity);

        // Create a new participant and add them to virtual contest
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        try {
            \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
                'contest_alias' => $virtualContest->alias,
                'usernameOrEmail' => $participant->username,
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should have thrown a InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                $e->getMessage(),
                'usersCanNotBeAddedInVirtualContestWhenOriginalContestIsPrivate'
            );
        }
    }

    public function testParticipantCanNotJoinToVirtualContestWithoutRegister() {
        [
            'virtualContests' => $virtualContests,
            'identity' => $identity,
        ] = self::createVirtualContest(numberOfVirtualContests: 3);

        $login = self::login($identity);

        // Create a new participant and add them to virtual contest
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
            'contest_alias' => $virtualContests[0]->alias,
            'usernameOrEmail' => $participant->username,
            'auth_token' => $login->auth_token,
        ]));

        // User tries to join the virtual contest where they were not assigned
        $login = self::login($participant);

        try {
            \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
                new \OmegaUp\Request([
                    'contest_alias' => $virtualContests[1]->alias,
                    'auth_token' => $login->auth_token,
                ])
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }

    public function testUserCanNotGetScoreboardsFromUnregisteredVirtualContests() {
        // Creating several virtual contests from different users
        [
            'virtualContests' => $virtualContests1,
            'identity' => $identity1,
        ] = self::createVirtualContest(numberOfVirtualContests: 3);
        [
            'virtualContests' => $virtualContests2,
        ] = self::createVirtualContest(numberOfVirtualContests: 3);
        $virtualContests = array_merge($virtualContests1, $virtualContests2);

        $virtualContest = $virtualContests[0];

        // Create a new participant and add them to virtual contest
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity1);

        \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
            'contest_alias' => $virtualContest->alias,
            'usernameOrEmail' => $participant->username,
            'auth_token' => $login->auth_token,
        ]));

        // User tries to join the virtual contest where they were not assigned
        \OmegaUp\Test\Factories\Contest::openContest(
            $virtualContest,
            $participant
        );

        $login = self::login($participant);

        $response = \OmegaUp\Controllers\Problemset::apiScoreboardEvents(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' => $virtualContests[0]->problemset_id,
            ])
        );

        $this->assertArrayHasKey('events', $response);

        // The rest of virtual contests are unaccessible for the contestant
        foreach ($virtualContests as $index => $vc) {
            if ($index == 0) {
                continue;
            }
            try {
                \OmegaUp\Controllers\Problemset::apiScoreboardEvents(
                    new \OmegaUp\Request([
                        'auth_token' => $login->auth_token,
                        'problemset_id' => $vc->problemset_id,
                    ])
                );

                $this->fail('Should have thrown a ForbiddenAccessException');
            } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
                $this->assertEquals($e->getMessage(), 'userNotAllowed');
            }
        }
    }

    public function testCreateVirtualContestBeforeTheOriginalEnded() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() - 100);

        try {
            \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'originalContestHasNotEnded');
        }
    }

    public function testCreateVirtualContestWithInvalidAlias() {
        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => 'wrong alias',
            'auth_token' => $login->auth_token
        ]);

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() - 100);

        try {
            \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
            $this->fail('Should have thrown a InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($e->getMessage(), 'parameterInvalid');
        }
    }

    public function testVirtualContestRestrictedApiAddProblem() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Lets assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
                'contest_alias' => $virtualContestAlias,
                'problem_alias' => $problemData['problem']->alias,
                'points' => 100,
                'auth_token' => $login->auth_token
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }

    public function testVirtualContestRestrictedApiRemoveProblem() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add problem to original contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Lets assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            \OmegaUp\Controllers\Contest::apiRemoveProblem(new \OmegaUp\Request([
                'contest_alias' => $virtualContestAlias,
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }

    public function testVirtualContestRestrictedApiUpdate() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Lets assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'contest_alias' => $virtualContestAlias,
                'title' => 'testtest',
                'auth_token' => $login->auth_token,
                'languages' => 'c11-gcc',
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }
}
