<?php

/**
 * Simple test for legacy user Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_legacyUserTest extends \OmegaUp\Test\BadgesTestCase {
    public function addProblemRun(\OmegaUp\DAO\VO\Identities $identity): void {
        $newProblem = \OmegaUp\Test\Factories\Problem::createProblem();
        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $newProblem,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run);
    }

    public function testLegacyUserOnlyProblems() {
        //Change date to today less two years
        $today = date('Y-m-d', \OmegaUp\Time::get());
        $date = date_create($today);
        date_add(
            $date,
            date_interval_create_from_date_string(
                '-2 year'
            )
        );
        $date = date_format($date, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($date));

        ['user' => $user1, 'identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        self::addProblemRun($identity1);
        self::addProblemRun($identity2);

        //Change date to today less one year
        $date = date_create($date);
        date_add(
            $date,
            date_interval_create_from_date_string(
                '1 year'
            )
        );
        $date = date_format($date, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($date));

        self::addProblemRun($identity1);
        self::addProblemRun($identity2);

        //Change date to today less one year
        $date = date_create($date);
        date_add(
            $date,
            date_interval_create_from_date_string(
                '1 year'
            )
        );
        $date = date_format($date, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($date));
        self::addProblemRun($identity1);

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/legacyUser/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$user1->user_id];
        $this->assertSame($expected, $results);
    }

    public function testLegacyUserMix() {
        //Change date to today less two years
        $today = date('Y-m-d', \OmegaUp\Time::get());
        $date = date_create($today);
        date_add(
            $date,
            date_interval_create_from_date_string(
                '-2 year'
            )
        );
        $date = date_format($date, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($date));

        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];
        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);
        $this->assertSame('ok', $response['status']);

        $user1 = $problemData['authorUser'];
        $identity1 = $problemAuthor;
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        //Change date to today less one year
        $date = date_create($date);
        date_add(
            $date,
            date_interval_create_from_date_string(
                '1 year'
            )
        );
        $date = date_format($date, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($date));

        self::addProblemRun($identity1);
        self::addProblemRun($identity2);

        //Change date to today less one year
        $date = date_create($date);
        date_add(
            $date,
            date_interval_create_from_date_string(
                '1 year'
            )
        );
        $date = date_format($date, 'Y-m-d');
        \OmegaUp\Time::setTimeForTesting(strtotime($date));

        $login = self::login($identity1);
        \OmegaUp\Controllers\Contest::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'visibility' => 1,
            'title' => 'Prueba',
            'alias' => 'prueba',
            'description' => 'Concurso de prueba',
            'start_time' => '1560872590',
            'finish_time' => '1560958990',
            'window_length' => '0',
            'scoreboard' => 100,
            'points_decay_factor' => 0,
            'score_mode' => 'partial',
            'submissions_gap' => 1200,
            'penalty' => 0,
            'feedback' => 'detailed',
            'penalty_type' => 'contest_start',
            'languages' => 'c11-gcc',
            'penalty_calc_policy' => 'sum',
            'admission_mode' => 'private',
            'show_scoreboard_after' => 'true',
        ]));

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/legacyUser/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$user1->user_id];
        $this->assertSame($expected, $results);
    }
}
