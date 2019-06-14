<?php

require_once OMEGAUP_ROOT . '/www/api/ApiCaller.php';
require_once 'libs/FileHandler.php';
require_once 'libs/FileUploader.php';

/**
 * Test to ensure that all the badges are in the correct format.
 *
 * @author carlosabcs
 */
class BadgesTest extends OmegaupTestCase {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';
    const MAX_BADGE_SIZE = 20 * 1024;
    const ICON_FILE = 'icon.svg';
    const LOCALIZATIONS_FILE = 'localizations.json';
    const QUERY_FILE = 'query.sql';
    const TEST_FILE = 'test.json';

    private static function cleanDb() {
        Utils::deleteAllSuggestions();
        Utils::deleteAllRanks();
        Utils::deleteAllPreviousRuns();
        Utils::deleteAllProblemsOfTheWeek();
        Utils::deleteAllCodersOfTheMonth();
        Utils::deleteAllProblems();
    }

    private static function getSortedResults($query) {
        global $conn;
        $rs = $conn->GetAll($query);
        $results = [];
        foreach ($rs as $user) {
            $results[] = $user['user_id'];
        }
        asort($results);
        return $results;
    }

    private static function getSortedExpectedResults($expected) {
        $results = [];
        foreach ($expected as $username) {
            // From each username, obtaining its ID
            $user = UsersDAO::FindByUsername($username);
            $results[] = $user->user_id;
        }
        asort($results);
        return $results;
    }

    private static function RunRequests($apicall) {
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
            $r = new Request($params);
            if (array_key_exists('files', $req)) {
                $_FILES['problem_contents']['tmp_name'] = $req['files']['problem_contents'];
            }
            $r->method = $req['api'];
            $fullResponse = ApiCaller::call($r);
            if ($fullResponse['status'] !== 'ok') {
                throw new Exception($fullResponse['error']);
            }
            if ($r->method === 'RunController::apiCreate') {
                Utils::gradeRun(null, $fullResponse['guid']);
            }
        }
    }

    public function testAllBadges() {
        global $conn;
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

            if (file_exists($testPath) && file_exists($queryPath)) {
                self::cleanDb();
                FileHandler::SetFileUploader($this->createFileUploaderMock());
                $content = json_decode(file_get_contents($testPath), true);

                // omegaUp admin user must be created always.
                $omegaup = UserFactory::createAdminUser(new UserParams([
                    'username' => 'omegaup_admin',
                    'password' => 'omegaup_admin',
                ]));

                foreach ($content['actions'] as $action) {
                    switch ($action['type']) {
                        case 'changeTime':
                            $time = strtotime($action['time']);
                            Time::setTimeForTesting($time);
                            break;

                        case 'apicalls':
                            foreach ($action['apicalls'] as $apicall) {
                                self::RunRequests($apicall);
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
                                        throw new Exception('Script ' . $script . " doesn't exist.");
                                }
                            }
                            break;
                        default:
                            throw new Exception('Action ' . $action['type'] . " doesn't exist");
                    }
                }

                $results = self::getSortedResults(file_get_contents($queryPath));
                $expected = self::getSortedExpectedResults($content['expectedResults']);
                $this->assertEquals($results, $expected);
            }
        }
        // Time will be automatically reset after last apicalls
        Time::setTimeForTesting(null);
        self::cleanDb();
    }
}
