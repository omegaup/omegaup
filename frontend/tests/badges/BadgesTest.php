<?php

/**
 * Test to ensure that all the badges are in the correct format.
 */
class BadgesTest extends \OmegaUp\Test\BadgesTestCase {
    private static function getSortedExpectedResults(array $expected): array {
        $results = [];
        foreach ($expected as $username) {
            // From each username, obtaining its ID
            $user = \OmegaUp\DAO\Users::FindByUsername($username);
            self::assertNotNull(
                $user,
                "User '{$username}' not found"
            );
            $results[] = $user->user_id;
        }
        asort($results);
        return $results;
    }

    private static function RunRequest(array $apicall): void {
        $login = self::login(new \OmegaUp\DAO\VO\Identities([
            'username' => $apicall['username'],
            'password' => $apicall['password'],
        ]));
        foreach ($apicall['requests'] as $req) {
            $params = [
                'auth_token' => $login->auth_token,
                'languages' => 'c11-gcc',
            ];
            foreach ($req['params'] as $k => $v) {
                $params[$k] = $v;
            }
            if (array_key_exists('files', $req)) {
                $_FILES['problem_contents']['tmp_name'] = (
                    OMEGAUP_ROOT . "/../{$req['files']['problem_contents']}"
                );
            }
            if ($req['api'] === '\\OmegaUp\\Controllers\\QualityNomination::apiCreate') {
                $params['contents'] = json_encode($params['contents']);
            }
            $r = new \OmegaUp\Request($params);
            $r->method = $req['api'];
            $r->methodName = $req['api'];
            $fullResponse = \OmegaUp\ApiCaller::call($r);
            self::assertSame(
                'ok',
                $fullResponse['status'],
                'request=' . json_encode($req) .
                "\nresponse=" . json_encode($fullResponse)
            );
            if ($r->method === '\\OmegaUp\\Controllers\\Run::apiCreate') {
                $points = array_key_exists(
                    'points',
                    $req['gradeResult']
                ) ? $req['gradeResult']['points'] : 1;
                $verdict = $req['gradeResult']['verdict'];
                \OmegaUp\Test\Utils::gradeRun(
                    null,
                    $fullResponse['guid'],
                    $points,
                    $verdict
                );
            }
        }
    }

    public function apicallTest(
        array $actions,
        array $expectedResults,
        string $queryPath
    ): void {
        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'changeTime':
                    $time = strtotime($action['time']);
                    \OmegaUp\Time::setTimeForTesting($time);
                    break;

                case 'apicalls':
                    foreach ($action['apicalls'] as $apicall) {
                        self::RunRequest($apicall);
                    }
                    break;

                case 'scripts':
                    foreach ($action['scripts'] as $script) {
                        switch ($script) {
                            case 'update_ranks.py':
                                \OmegaUp\Test\Utils::runUpdateRanks(
                                    date('Y-m-01', \OmegaUp\Time::get())
                                );
                                break;

                            case 'aggregate_feedback.py':
                                \OmegaUp\Test\Utils::runAggregateFeedback();
                                break;

                            default:
                                $this->fail(
                                    "Script {$script} doesn't exist."
                                );
                        }
                    }
                    break;

                default:
                    $this->fail(
                        "Action {$action['type']} doesn't exist"
                    );
            }
        }
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = self::getSortedExpectedResults($expectedResults);
        $this->assertSame($results, $expected);
        \OmegaUp\Time::setTimeForTesting(null);
    }

    /**
     * @dataProvider allBadgesProvider
     */
    public function testBadge(string $badgeAlias) {
        $this->assertTrue(
            \OmegaUp\Validators::alias($badgeAlias),
            'The alias for this badge is invalid.'
        );

        $badgePath = static::OMEGAUP_BADGES_ROOT . "/{$badgeAlias}";

        $iconPath = "{$badgePath}/" . static::ICON_FILE;
        if (file_exists($iconPath)) {
            $this->assertLessThanOrEqual(
                static::MAX_BADGE_SIZE,
                filesize($iconPath),
                "The size of {$iconPath} must be less than or equal to 20KB."
            );
        }

        $localizationsPath = "{$badgePath}/" . static::LOCALIZATIONS_FILE;
        $this->assertTrue(
            file_exists($localizationsPath),
            "The file '{$localizationsPath}' doesn't exist."
        );

        $queryPath = "{$badgePath}/" . static::QUERY_FILE;
        $this->assertTrue(
            file_exists($queryPath),
            "The file '{$queryPath}' doesn't exist."
        );

        $testPath = "{$badgePath}/" . static::TEST_FILE;
        $this->assertTrue(
            file_exists($testPath),
            "The file '{$testPath}' doesn't exist."
        );

        $content = json_decode(file_get_contents($testPath), true);
        switch ($content['testType']) {
            case 'apicall':
                self::apicallTest(
                    $content['actions'],
                    $content['expectedResults'],
                    $queryPath
                );
                break;

            case 'phpunit':
                $testPath = static::BADGES_TESTS_ROOT . "/Badge_{$badgeAlias}Test.php";
                $this->assertTrue(
                    file_exists($testPath),
                    "The file '{$testPath}' doesn't exist."
                );
                break;

            default:
                $this->fail(
                    "Test type {$content['testType']} doesn't exist"
                );
        }
    }

    /**
     * @return iterable<array{0: string}>
     */
    public function allBadgesProvider(): iterable {
        $aliases = array_diff(
            scandir(
                static::OMEGAUP_BADGES_ROOT
            ),
            ['..', '.', 'default_icon.svg']
        );
        foreach ($aliases as $badgeAlias) {
            $badgePath = static::OMEGAUP_BADGES_ROOT . "/{$badgeAlias}";

            if (!is_dir($badgePath)) {
                continue;
            }

            yield [$badgeAlias];
        }
    }

    private static function getBadgesFromArray(array $badgesResults): array {
        $badges = [];
        foreach ($badgesResults as $badge) {
            $badges[] = $badge['badge_alias'];
        }
        return $badges;
    }

    private static function getBadgesFromNotificationContents(array $notifications): array {
        $badges = [];
        foreach ($notifications as $notification) {
            $badges[] = $notification['contents']['badge'];
        }
        return $badges;
    }

    public function testListBadges() {
        // Manually creates a new badge
        $newBadge = 'testBadge';
        $newBadgePath = static::OMEGAUP_BADGES_ROOT . "/{$newBadge}";
        $results = [];
        try {
            mkdir($newBadgePath);
            $results = \OmegaUp\Controllers\Badge::apiList(
                new \OmegaUp\Request(
                    []
                )
            );
        } finally {
            rmdir($newBadgePath);
        }
        // Get all badges through API
        $this->assertTrue(in_array($newBadge, $results));
    }

    public function testAssignBadgesCronjob() {
        // Create two badge receivers:
        // - User 1 will receive: Problem Setter badge
        // - User 2 will receive: Problem Setter and Contest Manager badges
        ['user' => $userOne, 'identity' => $identityOne] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identityTwo] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Problem::createProblemWithAuthor($identityOne);
        \OmegaUp\Test\Factories\Problem::createProblemWithAuthor($identityTwo);
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                [
                    'contestDirector' => $identityTwo,
                    'languages' => ['c11-gcc'],
                ]
            )
        );
        $expectedUserOneResults = ['problemSetter'];
        $expectedUserTwoResults = ['contestManager', 'problemSetter'];
        \OmegaUp\Test\Utils::runAssignBadges();
        {
            $login = self::login($identityOne);
            // Fetch badges through apiMyList
            $userOneBadges = \OmegaUp\Controllers\Badge::apiMyList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'user' => $userOne,
            ]));
            $results = self::getBadgesFromArray($userOneBadges['badges']);
            $this->assertSame(
                count(array_intersect($expectedUserOneResults, $results)),
                count($expectedUserOneResults)
            );
            $this->assertFalse(
                in_array(
                    'contestManager',
                    $expectedUserOneResults
                )
            );

            // Fetch badges through apiUserList
            $userTwoBadges = \OmegaUp\Controllers\Badge::apiUserList(new \OmegaUp\Request([
                'target_username' => $identityTwo->username,
            ]));
            $results = self::getBadgesFromArray($userTwoBadges['badges']);
            $this->assertSame(
                count(array_intersect($expectedUserTwoResults, $results)),
                count($expectedUserTwoResults)
            );

            // Now check if notifications have been created for both users
            $userOneNotifications = \OmegaUp\Controllers\Notification::apiMyList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'user' => $userOne,
            ]));
            $results = self::getBadgesFromNotificationContents(
                $userOneNotifications['notifications']
            );
            $this->assertSame(
                count(array_intersect($expectedUserOneResults, $results)),
                count($expectedUserOneResults)
            );
            $this->assertFalse(
                in_array(
                    'contestManager',
                    $expectedUserOneResults
                )
            );
        }
        {
            $login = self::login($identityTwo);
            $userTwoNotifications = \OmegaUp\Controllers\Notification::apiMyList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'user' => $userOne,
            ]));
            $results = self::getBadgesFromNotificationContents(
                $userTwoNotifications['notifications']
            );
            $this->assertSame(
                count(array_intersect($expectedUserTwoResults, $results)),
                count($expectedUserTwoResults)
            );
        }
    }

    public function testGetAssignationTime() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Problem::createProblemWithAuthor($identity);

        $previousTime = \OmegaUp\Time::get();
        \OmegaUp\Test\Utils::runAssignBadges();

        $login = self::login($identity);
        $problemSetterResult = \OmegaUp\Controllers\Badge::apiMyBadgeAssignationTime(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
            'badge_alias' => 'problemSetter',
        ]));
        $this->assertNotNull($problemSetterResult['assignation_time']);
        $this->assertThat(
            $problemSetterResult['assignation_time']->time,
            $this->logicalAnd(
                $this->greaterThanOrEqual($previousTime),
                $this->lessThanOrEqual(\OmegaUp\Time::get())
            )
        );

        [
            'templateProperties' => $templateProperties,
        ] = \OmegaUp\Controllers\Badge::getDetailsForTypeScript(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'badge_alias' => 'problemSetter'
        ]));
        $this->assertNotNull(
            $templateProperties['payload']['badge']['assignation_time']
        );

        $contestManagerResult = \OmegaUp\Controllers\Badge::apiMyBadgeAssignationTime(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
            'badge_alias' => 'contestManager',
        ]));
        $this->assertNull($contestManagerResult['assignation_time']);
    }

    public function testBadgeDetails() {
        // Creates one owner for ContestManager Badge and no owner for
        // ContestManager, then checks badge details results.
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // For some reason, this method creates a new user also.
        \OmegaUp\Test\Factories\Problem::createProblemWithAuthor($identity);

        $previousTime = \OmegaUp\Time::get();
        \OmegaUp\Test\Utils::runAssignBadges();

        // In total they must exist 4 users: admintest, test,
        // the user created by createProblemWithAuthor and $user

        $details = \OmegaUp\Controllers\Badge::apiBadgeDetails(new \OmegaUp\Request([
            'badge_alias' => 'problemSetter',
        ]));
        $this->assertNotNull($details['first_assignation']);
        $this->assertThat(
            $details['first_assignation']->time,
            $this->logicalAnd(
                $this->greaterThanOrEqual($previousTime),
                $this->lessThanOrEqual(\OmegaUp\Time::get())
            )
        );
        $this->assertSame(1, $details['owners_count']);
        $this->assertSame(4, $details['total_users']);
        $details = \OmegaUp\Controllers\Badge::apiBadgeDetails(new \OmegaUp\Request([
            'badge_alias' => 'contestManager',
        ]));
        $this->assertSame(0, $details['owners_count']);
        $this->assertNull($details['first_assignation']);
    }

    public function testBadgeDetailsException() {
        try {
            \OmegaUp\Controllers\Badge::apiBadgeDetails(new \OmegaUp\Request([
                'badge_alias' => 'esteBadgeNoExiste',
            ]));
            $this->fail('Should have thrown a NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame($e->getMessage(), 'badgeNotExist');
        }
    }
}
