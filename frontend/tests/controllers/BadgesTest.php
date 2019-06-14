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

    private static function RunRequests($apicall) {
        $identity = new stdClass();
        $identity->username = $apicall['username'];
        $identity->password = $apicall['password'];
        $login = self::login($identity);
        foreach ($apicall['requests'] as $req) {
            $r = new Request();
            if (array_key_exists('params', $req)) {
                $req['params']['auth_token'] = $login->auth_token;
                $r = new Request($req['params']);
            }
            if (array_key_exists('files', $req)) {
                $_FILES['problem_contents']['tmp_name'] = $req['files']['problem_contents'];
            }
            $r->method = $req['api'];
            $fullResponse = ApiCaller::call($r);
            if ($fullResponse['status'] !== 'ok') {
                throw new Exception($fullResponse['error']);
            }
            if ($r->method === 'RunController::apiCreate') {
                $response['response']['guid'] = $fullResponse['guid'];
                RunsFactory::gradeRun($response);
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
                $contents = json_decode(file_get_contents($testPath), true);

                // omegaUp admin user must be created always.
                $omegaup = UserFactory::createAdminUser(new UserParams([
                    'username' => 'omegaup_admin',
                    'password' => 'omegaup_admin',
                ]));

                $time = strtotime($contents['first_change_time']);
                Time::setTimeForTesting($time);

                // Running the first apicalls
                foreach ($contents['first_apicalls'] as $apicall) {
                    FileHandler::SetFileUploader($this->createFileUploaderMock());
                    self::runRequests($apicall);
                }

                // Running scripts
                foreach ($contents['scripts'] as $script) {
                    switch ($script) {
                        case 'update_user_rank.py':
                            Utils::RunUpdateUserRank();
                            break;
                        case 'aggregate_feedback.py':
                            Utils::RunAggregateFeedback();
                            break;
                        default:
                            throw new Exception('El script solicitado no existe');
                    }
                }

                $time = strtotime($contents['last_change_time']);
                Time::setTimeForTesting($time);

                // Running the last apicalls
                foreach ($contents['last_apicalls'] as $apicall) {
                    FileHandler::SetFileUploader($this->createFileUploaderMock());
                    self::RunRequests($apicall);
                }

                // Time will be automatically reset after last apicalls
                Time::setTimeForTesting(null);

                $sql = file_get_contents($queryPath);
                $rs = $conn->GetAll($sql);
                $results = [];
                foreach ($rs as $user) {
                    $results[] = $user['user_id'];
                }
                asort($results);

                $expected = [];
                foreach ($contents['expected_results'] as $username) {
                    $user = UsersDAO::FindByUsername($username);
                    $expected[] = $user->user_id;
                }
                asort($expected);

                $this->assertEquals($results, $expected);
            }
        }
        self::cleanDb();
    }
}
