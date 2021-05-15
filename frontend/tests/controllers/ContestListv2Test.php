<?php

/**
 * Description of Contest List v2
 *
 * @author Michael Serrato
 */

class ContestListv2Test extends \OmegaUp\Test\ControllerTestCase {
    private const ADMISSION_MODES = [
        'public',
        'private'
    ];

    private const TIMES = [
        'current',
        'future',
        'past'
    ];

    private const SECONDS_PER_DAY = 24 * 60  * 60;

    /**
     * Creates six kinds of contests {public, private} x {current, future, past}.
     *
     * @return array{contestData: array{current: list<array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>, future: list<array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>, past: list<array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>}, invitedUserIdentity: \OmegaUp\DAO\VO\Identities}
     */
    private function createContests(): array {
        $contestData = [];
        $now = \OmegaUp\Time::get();
        $yesterday = $now - self::SECONDS_PER_DAY;
        $beforeYesterday = $yesterday - self::SECONDS_PER_DAY;
        $tomorrow = $now + self::SECONDS_PER_DAY;
        $afterTomorrow = $tomorrow + self::SECONDS_PER_DAY;

        $intervals = [
            'current' => [
                'startTime'     => $yesterday,
                'finishTime'    => $tomorrow
            ],
            'future' => [
                'startTime'     => $tomorrow,
                'finishTime'    => $afterTomorrow
            ],
            'past' => [
                'startTime'     => $beforeYesterday,
                'finishTime'    => $yesterday
            ],
        ];

        // Create user that will be invited
        ['identity' => $invitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        foreach (self::TIMES as $time) {
            $contestData[$time] = [];

            foreach (self::ADMISSION_MODES as $admissionMode) {
                // Create contests
                $individualContestData = \OmegaUp\Test\Factories\Contest::createContest(
                    new \OmegaUp\Test\Factories\ContestParams([
                        'title' => "{$time}-{$admissionMode}",
                        'admissionMode' => $admissionMode,
                        'startTime' => new \OmegaUp\Timestamp(
                            $intervals[$time]['startTime']
                        ),
                        'finishTime' => new \OmegaUp\Timestamp(
                            $intervals[$time]['finishTime']
                        ),
                        'requestsUserInformation' => 'optional',
                    ])
                );

                if ($admissionMode === 'private') {
                    // Add user to the private contest
                    \OmegaUp\Test\Factories\Contest::addUser(
                        $individualContestData,
                        $invitedUserIdentity
                    );
                }

                $contestData[$time][] = $individualContestData;
            }
        }

        return [
            'contestData' => $contestData,
            'invitedUserIdentity' => $invitedUserIdentity,
        ];
    }

    /**
     * Extracts only the aliases from the contests.
     *
     * @param ContestList $contests
     *
     * @return array{current: list<string>, future: list<string>, past: list<string>}
     */
    private function extractAliases(array $contests): array {
        $aliases = [];

        foreach (self::TIMES as $time) {
            $aliases[$time] = [];

            foreach ($contests[$time] as $contest) {
                $aliases[$time][] = $contest['alias'];
            }

            // This is important to have the same order
            sort($aliases[$time]);
        }

        return $aliases;
    }

    public function testPublicContestsNotLoggedIn() {
        [
            'contestData' => $contestData,
            'invitedUserIdentity' => $invitedUserIdentity,
        ] = $this->createContests();

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-public',
                ],
                'future' => [
                    'future-public',
                ],
                'past' => [
                    'past-public'
                ]
            ],
            $contestListPayloadAliases
        );
    }

    public function testPrivateContestsForInvitedUser() {
        [
            'contestData' => $contestData,
            'invitedUserIdentity' => $invitedUserIdentity,
        ] = $this->createContests();

        // Logging user
        $login = self::login($invitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-private',
                    'current-public',
                ],
                'future' => [
                    'future-private',
                    'future-public',
                ],
                'past' => [
                    'past-private',
                    'past-public',
                ]
            ],
            $contestListPayloadAliases
        );
    }

    public function testPrivateContestsForNonInvitedUser() {
        [
            'contestData' => $contestData
        ] = $this->createContests();

        // Create user that wont be invited
        ['identity' => $nonInvitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Logging user
        $login = self::login($nonInvitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-public',
                ],
                'future' => [
                    'future-public',
                ],
                'past' => [
                    'past-public'
                ]
            ],
            $contestListPayloadAliases,
        );
    }

    public function testPrivateContestsForSystemAdmin() {
        [
            'contestData' => $contestData
        ] = $this->createContests();

        // Create admin user (system admin)
        ['user' => $user, 'identity' => $adminUserIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Logging user
        $login = self::login($adminUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-private',
                    'current-public',
                ],
                'future' => [
                    'future-private',
                    'future-public',
                ],
                'past' => [
                    'past-private',
                    'past-public',
                ]
            ],
            $contestListPayloadAliases
        );
    }

    /**
     * Create 2 contests, add 2 contestants (invited users) to the first contest and add 0 contestants to the second one.
     *
     * @param string $admissionMode (optional) Contest Admission Mode
     *
     * @return array{firstContestData: array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}, secondContestData: array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}, firstInvitedUserIdentity: \OmegaUp\DAO\VO\Identities, secondInvitedUserIdentity: \OmegaUp\DAO\VO\Identities}
     */
    private function createContestsAndAddContestants(string $admissionMode = 'public'): array {
        // Create contest that will have 2 contestants
        $firstContestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => 'contest-with-contestants',
                'admissionMode' => $admissionMode,
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create first user that will be invited
        ['identity' => $firstInvitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser(
            $firstContestData,
            $firstInvitedUserIdentity
        );

        // Create second user that will be invited
        ['identity' => $secondInvitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser(
            $firstContestData,
            $secondInvitedUserIdentity
        );

        // Create contest that will have 0 contestants
        $secondContestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => 'contest-without-contestants',
                'admissionMode' => $admissionMode,
                'requestsUserInformation' => 'optional',
            ])
        );

        return [
            'firstContestData' => $firstContestData,
            'secondContestData' => $secondContestData,
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
            'secondInvitedUserIdentity' => $secondInvitedUserIdentity,
        ];
    }

    public function testContestantsColumnAsUserNotLoggedIn() {
        [
            'firstContestData' => $firstContestData,
            'secondContestData' => $secondContestData,
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
            'secondInvitedUserIdentity' => $secondInvitedUserIdentity,
        ] = $this->createContestsAndAddContestants();

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['smartyProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];

        if ($contests[0]['title'] === 'contest-with-contestants') {
            $contestWithContestants = $contests[0];
            $contestWithoutContestants = $contests[1];
        } else {
            $contestWithContestants = $contests[1];
            $contestWithoutContestants = $contests[0];
        }

        $this->assertEquals(2, $contestWithContestants['contestants']);
        $this->assertEquals(0, $contestWithoutContestants['contestants']);
    }

    public function testContestantsColumnAsCreatorUser() {
        [
            'firstContestData' => $firstContestData,
            'secondContestData' => $secondContestData,
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
            'secondInvitedUserIdentity' => $secondInvitedUserIdentity,
        ] = $this->createContestsAndAddContestants('private');

        $firstContestCreator = $firstContestData['director'];

        // Logging user
        $login = self::login($firstInvitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestWithContestants = $contests[0];

        $this->assertEquals(2, $contestWithContestants['contestants']);

        $secondContestCreator = $secondContestData['director'];

        // Logging user
        $login = self::login($secondContestCreator);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestWithoutContestants = $contests[0];

        $this->assertEquals(0, $contestWithoutContestants['contestants']);
    }

    public function testContestantsColumnAsInvitedUser() {
        [
            'firstContestData' => $firstContestData,
            'secondContestData' => $secondContestData,
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
            'secondInvitedUserIdentity' => $secondInvitedUserIdentity,
        ] = $this->createContestsAndAddContestants('private');

        // Logging user
        $login = self::login($firstInvitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestWithContestants = $contests[0];

        $this->assertEquals(2, $contestWithContestants['contestants']);
    }

    public function testContestantsColumnAsSystemAdmin() {
        [
            'firstContestData' => $firstContestData,
            'secondContestData' => $secondContestData,
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
            'secondInvitedUserIdentity' => $secondInvitedUserIdentity,
        ] = $this->createContestsAndAddContestants('private');

        // Create admin user (system admin)
        ['user' => $user, 'identity' => $adminUserIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Logging user
        $login = self::login($adminUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];

        if ($contests[0]['title'] === 'contest-with-contestants') {
            $contestWithContestants = $contests[0];
            $contestWithoutContestants = $contests[1];
        } else {
            $contestWithContestants = $contests[1];
            $contestWithoutContestants = $contests[0];
        }

        $this->assertEquals(2, $contestWithContestants['contestants']);
        $this->assertEquals(0, $contestWithoutContestants['contestants']);
    }
}
