<?php

require_once OMEGAUP_ROOT . '/www/api/ApiCaller.php';
require_once 'libs/FileHandler.php';
require_once 'libs/FileUploader.php';

/**
 * Test to ensure that all the badges are in the correct format.
 *
 * @author carlosabcs
 */
class BadgesTest extends BadgesTestCase {
    private static function getSortedExpectedResults(array $expected): array {
        $results = [];
        foreach ($expected as $username) {
            // From each username, obtaining its ID
            $user = UsersDAO::FindByUsername($username);
            $results[] = $user->user_id;
        }
        asort($results);
        return $results;
    }

    private static function RunRequest(array $apicall): void {
        $login = self::login(new Identities([
            'username' => $apicall['username'],
            'password' => $apicall['password'],
        ]));
        foreach ($apicall['requests'] as $req) {
            $params = [
                'auth_token' => $login->auth_token,
            ];
            foreach ($req['params'] as $k => $v) {
                $params[$k] = $v;
            }
            if (array_key_exists('files', $req)) {
                $_FILES['problem_contents']['tmp_name'] = $req['files']['problem_contents'];
            }
            if ($req['api'] === 'QualityNominationController::apiCreate') {
                $params['contents'] = json_encode($params['contents']);
            }
            $r = new Request($params);
            $r->method = $req['api'];
            $fullResponse = ApiCaller::call($r);
            if ($fullResponse['status'] !== 'ok') {
                throw new Exception($fullResponse['error']);
            }
            if ($r->method === 'RunController::apiCreate') {
                $points = array_key_exists('points', $req['gradeResult']) ? $req['gradeResult']['points'] : 1;
                $verdict = $req['gradeResult']['verdict'];
                Utils::gradeRun(null, $fullResponse['guid'], $points, $verdict);
            }
        }
    }

    public function apicallTest(array $actions, array $expectedResults, string $queryPath): void {
        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'changeTime':
                    $time = strtotime($action['time']);
                    Time::setTimeForTesting($time);
                    break;

                case 'apicalls':
                    foreach ($action['apicalls'] as $apicall) {
                        self::RunRequest($apicall);
                    }
                    break;

                case 'scripts':
                    foreach ($action['scripts'] as $script) {
                        switch ($script) {
                            case 'update_user_rank.py':
                                Utils::RunUpdateUserRank();
                                break;
                            case 'aggregate_feedback.py':
                                Utils::RunAggregateFeedback();
                                break;
                            default:
                                throw new Exception("Script {$script} doesn't exist.");
                        }
                    }
                    break;
                default:
                    throw new Exception("Action {$action['type']} doesn't exist");
            }
        }
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = self::getSortedExpectedResults($expectedResults);
        $this->assertEquals($results, $expected);
        Time::setTimeForTesting(null);
    }

    public function phpUnitTest($badge): void {
        $testPath = static::BADGES_TESTS_ROOT . "/${badge}Test.php";
        $this->assertTrue(
            file_exists($testPath),
            "$badge:> The file ${badge}.php doesn't exist in frontend/tests/badges."
        );
    }

    public function runBadgeTest($testPath, $queryPath, $badge): void {
        FileHandler::SetFileUploader($this->createFileUploaderMock());
        $content = json_decode(file_get_contents($testPath), true);
        Utils::CleanupFilesAndDb();
        switch ($content['testType']) {
            case 'apicall':
                self::apicallTest($content['actions'], $content['expectedResults'], $queryPath);
                break;
            case 'phpunit':
                self::phpUnitTest($badge);
                break;
            default:
                throw new Exception("Test type {$content['testType']} doesn't exist");
        }
    }

    public function testAllBadges() {
        $aliases = array_diff(scandir(static::OMEGAUP_BADGES_ROOT), ['..', '.', 'default_icon.svg']);
        foreach ($aliases as $alias) {
            $badgePath = static::OMEGAUP_BADGES_ROOT . "/${alias}";
            if (!is_dir($badgePath)) {
                continue;
            }
            $iconPath = "${badgePath}/" . static::ICON_FILE;
            if (file_exists($iconPath)) {
                $this->assertLessThanOrEqual(
                    static::MAX_BADGE_SIZE,
                    filesize($iconPath),
                    "$alias:> The size of icon.svg must be less than or equal to 20KB."
                );
            }

            $localizationsPath = "${badgePath}/" . static::LOCALIZATIONS_FILE;
            $this->assertTrue(
                file_exists($localizationsPath),
                "$alias:> The file localizations.json doesn't exist."
            );

            $queryPath = "${badgePath}/" . static::QUERY_FILE;
            $this->assertTrue(
                file_exists($queryPath),
                "$alias:> The file query.sql doesn't exist."
            );

            $testPath = "${badgePath}/" . static::TEST_FILE;
            $this->assertTrue(
                file_exists($testPath),
                "$alias:> The file test.json doesn't exist."
            );

            self::runBadgeTest($testPath, $queryPath, $alias);
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
            $badges[] = json_decode($notification['contents'])->badge;
        }
        return $badges;
    }

    public function testListBadges() {
        // Manually creates a new badge
        $newBadge = 'testBadge';
        $newBadgePath = static::OMEGAUP_BADGES_ROOT . "/${newBadge}";
        $results = [];
        try {
            mkdir($newBadgePath);
            $results = BadgeController::apiList(new Request([]));
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
        $userOne = UserFactory::createUser();
        $userTwo = UserFactory::createUser();
        ProblemsFactory::createProblemWithAuthor($userOne);
        ProblemsFactory::createProblemWithAuthor($userTwo);
        ContestsFactory::createContest(new ContestParams(['contestDirector' => $userTwo]));
        $expectedUserOneResults = ['problemSetter'];
        $expectedUserTwoResults = ['contestManager', 'problemSetter'];
        Utils::RunAssignBadges();
        {
            $login = self::login($userOne);
            // Fetch badges through apiMyList
            $userOneBadges = BadgeController::apiMyList(new Request([
                'auth_token' => $login->auth_token,
                'user' => $userOne,
            ]));
            $results = self::getBadgesFromArray($userOneBadges['badges']);
            $this->assertEquals(
                count(array_intersect($expectedUserOneResults, $results)),
                count($expectedUserOneResults)
            );
            $this->assertFalse(in_array('contestManager', $expectedUserOneResults));

            // Fetch badges through apiUserList
            $userTwoBadges = BadgeController::apiUserList(new Request([
                'target_username' => $userTwo->username,
            ]));
            $results = self::getBadgesFromArray($userTwoBadges['badges']);
            $this->assertEquals(
                count(array_intersect($expectedUserTwoResults, $results)),
                count($expectedUserTwoResults)
            );

            // Now check if notifications have been created for both users
            $userOneNotifications = NotificationController::apiMyList(new Request([
                'auth_token' => $login->auth_token,
                'user' => $userOne,
            ]));
            $results = self::getBadgesFromNotificationContents($userOneNotifications['notifications']);
            $this->assertEquals(
                count(array_intersect($expectedUserOneResults, $results)),
                count($expectedUserOneResults)
            );
            $this->assertFalse(in_array('contestManager', $expectedUserOneResults));
        }
        {
            $login = self::login($userTwo);
            $userTwoNotifications = NotificationController::apiMyList(new Request([
                'auth_token' => $login->auth_token,
                'user' => $userOne,
            ]));
            $results = self::getBadgesFromNotificationContents($userTwoNotifications['notifications']);
            $this->assertEquals(
                count(array_intersect($expectedUserTwoResults, $results)),
                count($expectedUserTwoResults)
            );
        }
    }

    public function testGetAssignationTime() {
        $user = UserFactory::createUser();
        ProblemsFactory::createProblemWithAuthor($user);

        $previousTime = Time::get();
        Utils::RunAssignBadges();

        $login = self::login($user);
        $problemSetterResult = BadgeController::apiMyBadgeAssignationTime(new Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
            'badge_alias' => 'problemSetter',
        ]));
        $this->assertNotNull($problemSetterResult['assignation_time']);
        $timeDifference = ($problemSetterResult['assignation_time'] - $previousTime) / 60;
        $this->assertTrue($timeDifference < 10);

        $contestManagerResult = BadgeController::apiMyBadgeAssignationTime(new Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
            'badge_alias' => 'contestManager',
        ]));
        $this->assertNull($contestManagerResult['assignation_time']);
    }

    public function testBadgeDetails() {
        // Creates one owner for ContestManager Badge and no owner for
        // ContestManager, then checks badge details results.
        $user = UserFactory::createUser();

        // For some reason, this method creates a new user also.
        ProblemsFactory::createProblemWithAuthor($user);

        $previousTime = Time::get();
        Utils::RunAssignBadges();

        // In total they must exist 4 users: admintest, test,
        // the user created by createProblemWithAuthor and $user

        $details = BadgeController::apiBadgeDetails(new Request([
            'badge_alias' => 'problemSetter',
        ]));
        $this->assertNotNull($details['first_assignation']);
        $timeDifference = ($details['first_assignation'] - $previousTime) / 60;
        $this->assertTrue($timeDifference < 10);
        $this->assertEquals(25, $details['owners_percentage']);

        $details = BadgeController::apiBadgeDetails(new Request([
            'badge_alias' => 'contestManager',
        ]));
        $this->assertEquals(0, $details['owners_percentage']);
        $this->assertNull($details['first_assignation']);
    }

    public function testBadgeDetailsException() {
        try {
            BadgeController::apiBadgeDetails(new Request([
                'badge_alias' => 'esteBadgeNoExiste',
            ]));
            $this->fail('Should have thrown a NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertEquals($e->getMessage(), 'badgeNotExist');
        }
    }
}
