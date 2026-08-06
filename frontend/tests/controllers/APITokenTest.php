<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class APITokenTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        \OmegaUp\Time::setTimeForTesting(1234567890);
    }

    public function tearDown(): void {
        unset($_SERVER['HTTP_AUTHORIZATION']);
        parent::tearDown();
        \OmegaUp\Time::setTimeForTesting(null);
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            new \OmegaUp\SessionManager()
        );
    }

    public function testAPITokensWork() {
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser();

        $apiToken = new \OmegaUp\DAO\VO\APITokens([
            'user_id' => $user->user_id,
            'token' => \OmegaUp\Test\Utils::CreateRandomString(),
            'name' => 'my-token',
        ]);
        \OmegaUp\DAO\APITokens::create($apiToken);

        $session = \OmegaUp\Controllers\Session::apiCurrentSession();
        $this->assertFalse($session['session']['valid']);

        $mockSessionManager = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->setMethods(['setHeader'])
            ->getMock();
        $mockSessionManager
            ->expects($this->exactly(3))
            ->method('setHeader')
            ->withConsecutive(
                [$this->equalTo('X-RateLimit-Limit: 10')],
                [$this->equalTo('X-RateLimit-Remaining: 9')],
                [$this->equalTo('X-RateLimit-Reset: 1234569600')],
            );
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $mockSessionManager
        );

        $_SERVER['HTTP_AUTHORIZATION'] = "token {$apiToken->token}";
        $session = \OmegaUp\Controllers\Session::apiCurrentSession();
        $this->assertTrue($session['session']['valid']);
    }

    public function testAPITokensListWork() {
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser();

        $token = \OmegaUp\Test\Utils::CreateRandomString();
        \OmegaUp\DAO\APITokens::create(
            new \OmegaUp\DAO\VO\APITokens([
                'user_id' => $user->user_id,
                'token' => $token,
                'name' => 'my-token',
                'last_used' => new \OmegaUp\Timestamp(1234566000),
                'use_count' => 1,
            ]),
        );
        \OmegaUp\DAO\APITokens::create(
            new \OmegaUp\DAO\VO\APITokens([
                'user_id' => $user->user_id,
                'token' => \OmegaUp\Test\Utils::CreateRandomString(),
                'name' => 'my-token-2',
                'last_used' => new \OmegaUp\Timestamp(1234562400),
                'use_count' => OMEGAUP_SESSION_API_HOURLY_LIMIT,
            ]),
        );

        $mockSessionManager = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->setMethods(['setHeader'])
            ->getMock();
        $mockSessionManager
            ->expects($this->any())
            ->method('setHeader');
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $mockSessionManager
        );

        $_SERVER['HTTP_AUTHORIZATION'] = "token {$token}";
        $this->assertEquals(
            [
                'tokens' => [
                    [
                        'name' => 'my-token',
                        'timestamp' => new \OmegaUp\Timestamp(1234567890),
                        'last_used' => new \OmegaUp\Timestamp(1234566000),
                        'rate_limit' => [
                            'reset' => new \OmegaUp\Timestamp(1234569600),
                            'limit' => OMEGAUP_SESSION_API_HOURLY_LIMIT,
                            'remaining' => OMEGAUP_SESSION_API_HOURLY_LIMIT - 2,
                        ],
                    ],
                    [
                        'name' => 'my-token-2',
                        'timestamp' => new \OmegaUp\Timestamp(1234567890),
                        'last_used' => new \OmegaUp\Timestamp(1234562400),
                        'rate_limit' => [
                            'reset' => new \OmegaUp\Timestamp(1234569600),
                            'limit' => OMEGAUP_SESSION_API_HOURLY_LIMIT,
                            'remaining' => OMEGAUP_SESSION_API_HOURLY_LIMIT,
                        ],
                    ],
                ],
            ],
            \OmegaUp\Controllers\User::apiListAPITokens(
                new \OmegaUp\Request(
                    []
                )
            ),
        );
    }

    public function testAPITokensRateLimitsWork() {
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser();

        $apiToken = new \OmegaUp\DAO\VO\APITokens([
            'user_id' => $user->user_id,
            'token' => \OmegaUp\Test\Utils::CreateRandomString(),
            'name' => 'my token',
        ]);
        \OmegaUp\DAO\APITokens::create($apiToken);

        $mockSessionManager = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->setMethods(['setHeader'])
            ->getMock();
        $mockSessionManager
            ->expects($this->any())
            ->method('setHeader');
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $mockSessionManager
        );

        $_SERVER['HTTP_AUTHORIZATION'] = "token {$apiToken->token}";
        for ($i = 0; $i < OMEGAUP_SESSION_API_HOURLY_LIMIT - 1; $i++) {
            $session = \OmegaUp\Controllers\Session::apiCurrentSession();
            $this->assertTrue($session['session']['valid']);
        }
        try {
            \OmegaUp\Controllers\Session::apiCurrentSession();
            $this->fail('Should not have been able to access the API');
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            $this->assertSame(
                'apiTokenRateLimitExceeded',
                $e->getMessage(),
            );
        }
    }

    public function testAPITokensWithExtendedSyntaxWork() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $apiToken = new \OmegaUp\DAO\VO\APITokens([
            'user_id' => $user->user_id,
            'token' => \OmegaUp\Test\Utils::CreateRandomString(),
            'name' => 'my token',
        ]);
        \OmegaUp\DAO\APITokens::create($apiToken);

        $mockSessionManager = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->setMethods(['setHeader'])
            ->getMock();
        $mockSessionManager
            ->expects($this->any())
            ->method('setHeader');
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $mockSessionManager
        );

        $_SERVER['HTTP_AUTHORIZATION'] = "token Credential={$apiToken->token},Username={$identity->username}";
        $session = \OmegaUp\Controllers\Session::apiCurrentSession();
        $this->assertTrue($session['session']['valid']);
        $this->assertSame(
            $identity->username,
            $session['session']['identity']->username,
        );
    }

    public function testAPITokensWithAssociatedIdentityWork() {
        \OmegaUp\Time::setTimeForTesting(strtotime('2022-01-01T00:00:00Z'));
        [
            'user' => $creator,
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = \OmegaUp\Test\Utils::createRandomPassword();
        \OmegaUp\Controllers\Identity::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => $username,
                'name' => $identityName,
                'password' => $password,
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                'group_alias' => $group['group']->alias,
            ]),
        );
        unset($creatorLogin);

        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiAssociateIdentity(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'password' => $password,
            ]),
        );
        unset($login);

        $apiToken = new \OmegaUp\DAO\VO\APITokens([
            'user_id' => $user->user_id,
            'token' => \OmegaUp\Test\Utils::CreateRandomString(),
            'name' => 'my token',
        ]);
        \OmegaUp\DAO\APITokens::create($apiToken);

        $mockSessionManager = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->setMethods(['setHeader'])
            ->getMock();
        $mockSessionManager
            ->expects($this->any())
            ->method('setHeader');
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $mockSessionManager
        );

        $_SERVER['HTTP_AUTHORIZATION'] = "token Credential={$apiToken->token},Username={$username}";
        $session = \OmegaUp\Controllers\Session::apiCurrentSession();
        $this->assertTrue($session['session']['valid']);
        $this->assertSame(
            $identity->username,
            $session['session']['loginIdentity']->username,
        );
        $this->assertSame(
            $username,
            $session['session']['identity']->username,
        );
    }

    public function testAPITokensWithNonAssociatedIdentityFails() {
        \OmegaUp\Time::setTimeForTesting(strtotime('2022-01-01T00:00:00Z'));
        [
            'user' => $creator,
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = \OmegaUp\Test\Utils::createRandomPassword();
        \OmegaUp\Controllers\Identity::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => $username,
                'name' => $identityName,
                'password' => $password,
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                'group_alias' => $group['group']->alias,
            ]),
        );
        unset($creatorLogin);

        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $apiToken = new \OmegaUp\DAO\VO\APITokens([
            'user_id' => $user->user_id,
            'token' => \OmegaUp\Test\Utils::CreateRandomString(),
            'name' => 'my token',
        ]);
        \OmegaUp\DAO\APITokens::create($apiToken);

        $_SERVER['HTTP_AUTHORIZATION'] = "token Credential={$apiToken->token},Username={$username}";
        try {
            \OmegaUp\Controllers\Session::apiCurrentSession();
            $this->fail('Should not have been able to access the API');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame(
                'loginRequired',
                $e->getMessage(),
            );
        }
    }

    public function testAPITokensWithUnrelatedUserFails() {
        [
            'identity' => $unrelatedIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $apiToken = new \OmegaUp\DAO\VO\APITokens([
            'user_id' => $user->user_id,
            'token' => \OmegaUp\Test\Utils::CreateRandomString(),
            'name' => 'my token',
        ]);
        \OmegaUp\DAO\APITokens::create($apiToken);

        $_SERVER['HTTP_AUTHORIZATION'] = "token Credential={$apiToken->token},Username={$unrelatedIdentity->username}";
        try {
            \OmegaUp\Controllers\Session::apiCurrentSession();
            $this->fail('Should not have been able to access the API');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame(
                'loginRequired',
                $e->getMessage(),
            );
        }
    }

    public function testAPITokenNameUniqueness() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiCreateAPIToken(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => 'my-token',
            ]),
        );
        try {
            \OmegaUp\Controllers\User::apiCreateAPIToken(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => 'my-token',
                ]),
            );
            $this->fail(
                'Should not have been able to create a duplicate token name'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame(
                'apiTokenNameAlreadyInUse',
                $e->getMessage(),
            );
        }
    }

    public function testAPITokenLimit() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Controllers\User::apiCreateAPIToken(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => "my-token-{$i}",
                ]),
            );
        }
        try {
            \OmegaUp\Controllers\User::apiCreateAPIToken(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => 'my-token-5',
                ]),
            );
            $this->fail('Should not have been able to create a sixth token');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame(
                'apiTokenLimitExceeded',
                $e->getMessage(),
            );
        }
    }

    public function testAPITokensCanBeRevoked() {
        $mockSessionManager = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->setMethods(['setHeader'])
            ->getMock();
        $mockSessionManager
            ->expects($this->any())
            ->method('setHeader');
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $mockSessionManager
        );

        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $token = \OmegaUp\Controllers\User::apiCreateAPIToken(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => 'my-token',
            ]),
        )['token'];
        unset($login);
        \OmegaUp\Controllers\Session::invalidateCache();
        \OmegaUp\Controllers\Session::invalidateLocalCache();

        $_SERVER['HTTP_AUTHORIZATION'] = "token {$token}";
        $session = \OmegaUp\Controllers\Session::apiCurrentSession();
        $this->assertTrue($session['session']['valid']);
        $this->assertSame(
            $identity->username,
            $session['session']['identity']->username,
        );
        unset($_SERVER['HTTP_AUTHORIZATION']);
        \OmegaUp\Controllers\Session::invalidateCache();
        \OmegaUp\Controllers\Session::invalidateLocalCache();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiRevokeAPIToken(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => 'my-token',
            ]),
        );
        unset($login);
        \OmegaUp\Controllers\Session::invalidateCache();
        \OmegaUp\Controllers\Session::invalidateLocalCache();

        $_SERVER['HTTP_AUTHORIZATION'] = "token {$token}";
        try {
            \OmegaUp\Controllers\Session::apiCurrentSession();
            $this->fail('Should not have been able to access the API');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame(
                'loginRequired',
                $e->getMessage()
            );
        }
    }

    public function testAPITokensWithUnknownNamesCannotBeRevoked() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\User::apiRevokeAPIToken(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => 'my-token',
                ]),
            );
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame(
                'recordNotFound',
                $e->getMessage()
            );
        }
    }
}
