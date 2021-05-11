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
     * @return array{contestData: array{current: list<array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>, future: list<array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>, past: list<array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>}, invitedUserIdentity: \OmegaUp\DAO\VO\Identities}
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
     * Extracts only the information about the "contest" model and also can filter by admission mode.
     *
     * @param array{current: list<array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>, future: list<array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>, past: list<array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}>} $contestData
     * @param string|null $admissionMode
     *
     * @return array{current: list<\OmegaUp\DAO\VO\Contests|null>, future: list<\OmegaUp\DAO\VO\Contests|null>, past: list<\OmegaUp\DAO\VO\Contests|null>}
     */
    private function extractContests(
        array $contestData,
        string $admissionMode = null
    ): array {
        $contests = [];

        foreach (self::TIMES as $time) {
            $contests[$time] = [];

            foreach ($contestData[$time] as $data) {
                if (
                    ! is_null(
                        $admissionMode
                    ) and $data['contest']->admission_mode !== $admissionMode
                ) {
                    continue;
                }

                $contests[$time][] = $data['contest'];
            }
        }

        return $contests;
    }

    /**
     * Extracts only the aliases from the contests.
     *
     * @param array{current: list<\OmegaUp\DAO\VO\Contests|null>, future: list<\OmegaUp\DAO\VO\Contests|null>, past: list<\OmegaUp\DAO\VO\Contests|null>}>} $contests
     *
     * @return array{current: list<string>, future: list<string>, past: list<string>}
     */
    private function extractAliases(array $contests): array {
        $aliases = [];

        foreach (self::TIMES as $time) {
            $aliases[$time] = [];

            foreach ($contests[$time] as $contest) {
                $aliases[$time][] = isset(
                    $contest->alias
                ) ? $contest->alias : $contest['alias'];
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

        $contests = $this->extractContests($contestData, 'public');

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );
        $contestAliases = $this->extractAliases($contests);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
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

        $contests = $this->extractContests($contestData);

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );
        $contestAliases = $this->extractAliases($contests);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
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

        $contests = $this->extractContests($contestData, 'public');

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );
        $contestAliases = $this->extractAliases($contests);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
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

        $contests = $this->extractContests($contestData);

        $contestListPayloadAliases = $this->extractAliases(
            $contestListPayload['contests']
        );
        $contestAliases = $this->extractAliases($contests);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
        );
    }
}
