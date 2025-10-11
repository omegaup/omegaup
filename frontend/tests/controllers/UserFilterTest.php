<?php
/**
 * Tests the /api/user/validateFilter/ API.
 */
class UserFilterTest extends \OmegaUp\Test\ControllerTestCase {
    public function testInvalidFilter() {
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => 'invalid',
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    public function testUnauthorizedAccess() {
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => '/all-events',
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testInsufficientPrivileges() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => '/all-events',
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testAllEventsWithAdmin() {
        [
            'identity' => $identityAdmin,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => '/all-events',
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertTrue($response['admin']);
    }

    public function testMyEvents() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/user/{$identity->username}",
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertSame($identity->username, $response['user']);
        $this->assertFalse($response['admin']);
        $this->assertEmpty($response['problem_admin']);
        $this->assertEmpty($response['contest_admin']);
        $this->assertEmpty($response['problemset_admin']);
    }

    public function testOtherUsersEvents() {
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity1);
        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/user/{$identity2->username}",
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testOtherUsersEventsWithAdmin() {
        [
            'identity' => $identityAdmin,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/user/{$identity->username}",
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertSame(true, $response['admin']);
    }

    public function testPublicProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => "/problemset/{$contest->problemset_id}",
        ]));
    }

    public function testPublicContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => "/contest/{$contest->alias}",
        ]));
    }

    public function testAnonymousPublicProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/problemset/{$contest->problemset_id}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousPublicContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest()['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/contest/{$contest->alias}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousProblemsetAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/problemset/{$contest->problemset_id}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousContestAccess() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/contest/{$contest->alias}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    public function testAnonymousProblemsetWithToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );
        $problemsetId = $problemset->problemset_id;
        $scoreboardUrl = $problemset->scoreboard_url;

        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/problemset/{$problemsetId}/{$scoreboardUrl}",
            ])
        );
        $this->assertEmpty($response['contest_admin']);
        $this->assertEmpty($response['problemset_admin']);
    }

    public function testAnonymousContestWithToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );
        $contestAlias = $contest->alias;
        $scoreboardUrl = $problemset->scoreboard_url;

        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/contest/{$contestAlias}/{$scoreboardUrl}",
            ])
        );
        $this->assertEmpty($response['contest_admin']);
        $this->assertEmpty($response['problemset_admin']);
    }

    public function testAnonymousProblemsetWithAdminToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );
        $problemsetId = $problemset->problemset_id;
        $scoreboardUrlAdmin = $problemset->scoreboard_url_admin;

        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/problemset/{$problemsetId}/{$scoreboardUrlAdmin}",
            ])
        );
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertContains($problemsetId, $response['problemset_admin']);
        $this->assertNull($response['user']);
    }

    public function testAnonymousContestWithAdminToken() {
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        )['contest'];
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );
        $contestAlias = $contest->alias;
        $scoreboardUrlAdmin = $problemset->scoreboard_url_admin;

        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/contest/{$contestAlias}/{$scoreboardUrlAdmin}",
            ])
        );
        $this->assertContains($contest->alias, $response['contest_admin']);
        $this->assertContains(
            $problemset->problemset_id,
            $response['problemset_admin']
        );
        $this->assertNull($response['user']);
    }

    public function testPublicProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/problem/{$problem->alias}",
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertSame($identity->username, $response['user']);
    }

    public function testAnonymousPublicProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem()['problem'];

        $response = \OmegaUp\Controllers\User::apiValidateFilter(
            new \OmegaUp\Request([
                'filter' => "/problem/{$problem->alias}",
            ])
        );
        $this->assertNull($response['user']);
    }

    public function testAnonymousProblemAccess() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
            ])
        )['problem'];

        try {
            \OmegaUp\Controllers\User::apiValidateFilter(new \OmegaUp\Request([
                'filter' => "/problem/{$problem->alias}",
            ]));
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('problemIsPrivate', $e->getMessage());
        }
    }
}
