<?php
/**
 * Description of Contest List v2
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
        $this->createContests();

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['templateProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        // Now, only contests in the current tab should be shown
        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-public',
                ],
                'future' => [],
                'past' => [],
            ],
            $contestListPayloadAliases
        );
    }

    public function testPublicContestsForv2() {
        $this->createContests();

        foreach (self::TIMES as $time) {
            $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsForTypeScript(
                new \OmegaUp\Request([
                    'tab_name' => $time,
                ])
            )['templateProperties']['payload']['contests'];

            $this->assertCount(1, $contestListPayload);
            $this->assertSame(
                "{$time}-public",
                $contestListPayload[0]['alias']
            );
        }
    }

    public function testPrivateContestsForInvitedUser() {
        [
            'invitedUserIdentity' => $invitedUserIdentity,
        ] = $this->createContests();

        // Logging user
        $login = self::login($invitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-private',
                    'current-public',
                ],
                'future' => [],
                'past' => [],
            ],
            $contestListPayloadAliases
        );
    }

    public function testPrivateContestsForNonInvitedUser() {
        $this->createContests();

        // Create user that wont be invited
        ['identity' => $nonInvitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Logging user
        $login = self::login($nonInvitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-public',
                ],
                'future' => [],
                'past' => [],
            ],
            $contestListPayloadAliases,
        );
    }

    public function testPrivateContestsForSystemAdmin() {
        $this->createContests();

        // Create admin user (system admin)
        ['identity' => $adminUserIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Logging user
        $login = self::login($adminUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );

        $this->assertEqualsCanonicalizing(
            [
                'current' => [
                    'current-private',
                    'current-public',
                ],
                'future' => [],
                'past' => [],
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
        $this->createContests();

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['templateProperties']['payload'];

        $currentContests = $contestListPayload['contests']['current'];
        $pastContests = $contestListPayload['contests']['past'];
        $futureContests = $contestListPayload['contests']['future'];
        $contests = array_merge(
            $currentContests,
            $pastContests,
            $futureContests
        );

        $contestContestants = [];

        [
            'response' => $contestants,
        ] = \OmegaUp\Controllers\Contest::apiGetNumberOfContestants(
            new \OmegaUp\Request([
                'contest_ids' => join(',', array_map(
                    fn ($contest) => $contest['contest_id'],
                    $contests
                )),
            ])
        );
        foreach ($contests as $contest) {
            $contestContestants[$contest['title']] = $contestants[$contest['contest_id']] ?? 0;
        }

        $this->assertEqualsCanonicalizing(
            [
                'current-public' => 0,
            ],
            $contestContestants,
        );
    }

    public function testContestantsforFutureWithRegistrationContests() {
        [
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
        ] = $this->createContestsAndAddContestants();

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['templateProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestContestants = [];

        // Logging user
        $login = self::login($firstInvitedUserIdentity);

        [
            'response' => $contestants,
        ] = \OmegaUp\Controllers\Contest::apiGetNumberOfContestants(
            new \OmegaUp\Request([
                'contest_ids' => join(',', array_map(
                    fn ($contest) => $contest['contest_id'],
                    $contests
                )),
                'auth_token' => $login->auth_token,
            ])
        );
        foreach ($contests as $contest) {
            $contestContestants[$contest['title']] = $contestants[$contest['contest_id']] ?? 0;
        }

        $this->assertEqualsCanonicalizing(
            [
                'contest-with-contestants' => 2,
                'contest-without-contestants' => 0,
            ],
            $contestContestants,
        );
    }

    public function testContestantsColumnAsCreatorUser() {
        [
            'secondContestData' => $secondContestData,
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
        ] = $this->createContestsAndAddContestants('private');

        // Logging user
        $login = self::login($firstInvitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestContestants = [];

        [
            'response' => $contestants,
        ] = \OmegaUp\Controllers\Contest::apiGetNumberOfContestants(
            new \OmegaUp\Request([
                'contest_ids' => join(',', array_map(
                    fn ($contest) => $contest['contest_id'],
                    $contests
                )),
                'auth_token' => $login->auth_token,
            ])
        );
        foreach ($contests as $contest) {
            $contestContestants[$contest['title']] = $contestants[$contest['contest_id']] ?? 0;
        }

        $this->assertEqualsCanonicalizing(
            [
                'contest-with-contestants' => 2,
            ],
            $contestContestants,
        );

        $secondContestCreator = $secondContestData['director'];

        // Logging user
        $login = self::login($secondContestCreator);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestContestants = [];

        foreach ($contests as $contest) {
            $contestContestants[$contest['title']] = $contest['contestants'];
        }

        $this->assertEqualsCanonicalizing(
            [
                'contest-without-contestants' => 0,
            ],
            $contestContestants,
        );
    }

    public function testContestantsColumnAsInvitedUser() {
        [
            'firstInvitedUserIdentity' => $firstInvitedUserIdentity,
        ] = $this->createContestsAndAddContestants('private');

        // Logging user
        $login = self::login($firstInvitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestContestants = [];

        [
            'response' => $contestants,
        ] = \OmegaUp\Controllers\Contest::apiGetNumberOfContestants(
            new \OmegaUp\Request([
                'contest_ids' => join(',', array_map(
                    fn ($contest) => $contest['contest_id'],
                    $contests
                )),
                'auth_token' => $login->auth_token,
            ])
        );
        foreach ($contests as $contest) {
            $contestContestants[$contest['title']] = $contestants[$contest['contest_id']] ?? 0;
        }

        $this->assertEqualsCanonicalizing(
            [
                'contest-with-contestants' => 2,
            ],
            $contestContestants,
        );
    }

    public function testContestantsColumnAsSystemAdmin() {
        $this->createContestsAndAddContestants('private');

        // Create admin user (system admin)
        ['identity' => $adminUserIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Logging user
        $login = self::login($adminUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contests = $contestListPayload['contests']['current'];
        $contestContestants = [];

        [
            'response' => $contestants,
        ] = \OmegaUp\Controllers\Contest::apiGetNumberOfContestants(
            new \OmegaUp\Request([
                'contest_ids' => join(',', array_map(
                    fn ($contest) => $contest['contest_id'],
                    $contests
                )),
                'auth_token' => $login->auth_token,
            ])
        );
        foreach ($contests as $contest) {
            $contestContestants[$contest['title']] = $contestants[$contest['contest_id']] ?? 0;
        }

        $this->assertEqualsCanonicalizing(
            [
                'contest-with-contestants' => 2,
                'contest-without-contestants' => 0,
            ],
            $contestContestants,
        );
    }

    public function testOrganizerColumnAsUserNotLoggedIn() {
        $organizerUsername = 'organizer-user';

        // Create organizer user (creator/director)
        ['user' => $organizerUser, 'identity' => $organizerIdentity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => $organizerUsername
            ])
        );

        // Create contest
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestDirector' => $organizerIdentity,
                'contestDirectorUser' => $organizerUser,
                'requestsUserInformation' => 'optional',
            ])
        );

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertSame($organizerUsername, $contest['organizer']);
    }

    public function testOrganizerColumnAsCreatorUser() {
        $organizerUsername = 'organizer-user';

        // Create organizer user (creator/director)
        ['user' => $organizerUser, 'identity' => $organizerIdentity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => $organizerUsername
            ])
        );

        // Create contest
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestDirector' => $organizerIdentity,
                'contestDirectorUser' => $organizerUser,
                'admissionMode' => 'private',
                'requestsUserInformation' => 'optional',
            ])
        );

        // Logging user
        $login = self::login($organizerIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertSame($organizerUsername, $contest['organizer']);
    }

    public function testOrganizerColumnAsInvitedUser() {
        $organizerUsername = 'organizer-user';

        // Create organizer user (creator/director)
        ['user' => $organizerUser, 'identity' => $organizerIdentity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => $organizerUsername
            ])
        );

        // Create contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestDirector' => $organizerIdentity,
                'contestDirectorUser' => $organizerUser,
                'admissionMode' => 'private',
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create invited user
        ['identity' => $invitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $invitedUserIdentity
        );

        // Logging user
        $login = self::login($invitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertSame($organizerUsername, $contest['organizer']);
    }

    public function testOrganizerColumnAsSystemAdmin() {
        $organizerUsername = 'organizer-user';

        // Create organizer user (creator/director)
        ['user' => $organizerUser, 'identity' => $organizerIdentity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => $organizerUsername
            ])
        );

        // Create contest
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestDirector' => $organizerIdentity,
                'contestDirectorUser' => $organizerUser,
                'admissionMode' => 'private',
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create admin user (system admin)
        ['identity' => $adminUserIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Logging user
        $login = self::login($adminUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertSame($organizerUsername, $contest['organizer']);
    }

    public function testParticipatingColumnAsUserNotLoggedIn() {
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
            ])
        );

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertFalse($contest['participating']);
    }

    public function testParticipatingColumnAsInvitedUser() {
        // Create contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create invited user
        ['identity' => $invitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $invitedUserIdentity
        );

        // Logging user
        $login = self::login($invitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertTrue($contest['participating']);
    }

    public function testParticipatingColumnAsNonInvitedUser() {
        // Create contest
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create non-invited user
        ['identity' => $nonInvitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Logging user
        $login = self::login($nonInvitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertFalse($contest['participating']);
    }

    public function testParticipatingColumnAsInvitedGroup() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create invited user for invited group
        ['identity' => $invitedUserIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Create group that will be invited
        $invitedGroupData = \OmegaUp\Test\Factories\Groups::createGroup();

        // Add user to the group
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $invitedGroupData,
            $invitedUserIdentity
        );

        // Add group to the private contest
        $login = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiAddGroup(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => strval(
                    $contestData['request']['alias']
                ),
                'group' => $invitedGroupData['group']->alias,
            ])
        );

        $login = self::login($invitedUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertTrue($contest['participating']);
    }

    public function testParticipatingColumnAsSystemAdmin() {
        // Create contest
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create admin user (system admin)
        ['identity' => $adminUserIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Logging user
        $login = self::login($adminUserIdentity);

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $contest = $contestListPayload['contests']['current'][0];

        $this->assertTrue($contest['participating']);
    }
}
