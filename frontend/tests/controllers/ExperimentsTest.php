<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class ExperimentsTest extends \OmegaUp\Test\ControllerTestCase {
    const TEST = 'experiment_test';

    private static $kKnownExperiments = [
        self::TEST,
    ];

    private static function getRequestForExperiments(array $experiments): string {
        $kvp = [];
        foreach ($experiments as $name) {
            $kvp[] = $name . '=' . \OmegaUp\Experiments::getExperimentHash(
                $name
            );
        }
        return implode(',', $kvp);
    }

    public function testConfigExperiments() {
        $defines = [
            \OmegaUp\Experiments::EXPERIMENT_PREFIX . strtoupper(
                self::TEST
            ) => true,
        ];
        $experiments = new
            \OmegaUp\Experiments(
                null,
                null,
                $defines,
                self::$kKnownExperiments
            );

        $this->assertSame(
            self::$kKnownExperiments,
            $experiments->getEnabledExperiments()
        );
        $this->assertTrue($experiments->isEnabled(self::TEST));
    }

    public function testRequestExperiments() {
        $experiments = new
            \OmegaUp\Experiments(
                self::getRequestForExperiments([self::TEST]),
                null,
                [],
                self::$kKnownExperiments
            );

        $this->assertSame(
            self::$kKnownExperiments,
            $experiments->getEnabledExperiments()
        );
        $this->assertTrue($experiments->isEnabled(self::TEST));
    }

    public function testRequestUnknownExperiments() {
        $experiments = new
            \OmegaUp\Experiments(
                self::getRequestForExperiments(['foo']),
                null,
                [],
                self::$kKnownExperiments
            );

        $this->assertEmpty($experiments->getEnabledExperiments());
        $this->assertFalse($experiments->isEnabled('foo'));
    }

    public function testRequestInvalidExperiments() {
        $experiments = new
            \OmegaUp\Experiments(
                self::TEST . '=invalid_hash',
                null,
                [],
                self::$kKnownExperiments
            );

        $this->assertEmpty($experiments->getEnabledExperiments());
        $this->assertFalse($experiments->isEnabled(self::TEST));
    }

    public function testUserExperiments() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $experiments = new
            \OmegaUp\Experiments(
                null,
                $identity,
                [],
                self::$kKnownExperiments
            );

        $this->assertEmpty($experiments->getEnabledExperiments());
        $this->assertFalse($experiments->isEnabled(self::TEST));

        // After adding the identity-experiment relationship to the database, the
        // experiment should be enabled.
        \OmegaUp\DAO\UsersExperiments::create(new \OmegaUp\DAO\VO\UsersExperiments([
            'user_id' => $identity->user_id,
            'experiment' => self::TEST,
        ]));

        $experiments = new
            \OmegaUp\Experiments(
                null,
                $identity,
                [],
                self::$kKnownExperiments
            );

        $this->assertSame(
            self::$kKnownExperiments,
            $experiments->getEnabledExperiments()
        );
        $this->assertTrue($experiments->isEnabled(self::TEST));
    }

    public function testSupportTeamMemberCanAddAndRemoveExperiment() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        [
            'identity' => $supportIdentity,
        ] = \OmegaUp\Test\Factories\User::createSupportUser();
        $login = self::login($supportIdentity);

        $response = \OmegaUp\Controllers\User::apiAddExperiment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username,
            'experiment' => \OmegaUp\Experiments::USER_README,
        ]));

        $this->assertSame('ok', $response['status']);
        $this->assertSame(
            [\OmegaUp\Experiments::USER_README],
            array_column(
                \OmegaUp\DAO\UsersExperiments::getByUserId(
                    intval(
                        $user->user_id
                    )
                ),
                'experiment'
            )
        );

        $response = \OmegaUp\Controllers\User::apiRemoveExperiment(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity->username,
                'experiment' => \OmegaUp\Experiments::USER_README,
            ])
        );

        $this->assertSame('ok', $response['status']);
        $this->assertEmpty(
            \OmegaUp\DAO\UsersExperiments::getByUserId(intval($user->user_id))
        );
    }

    public function testUnauthorizedUserCannotAddOrRemoveExperiment() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        [
            'identity' => $unauthorizedIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($unauthorizedIdentity);
        $request = [
            'auth_token' => $login->auth_token,
            'username' => $identity->username,
            'experiment' => \OmegaUp\Experiments::USER_README,
        ];

        try {
            \OmegaUp\Controllers\User::apiAddExperiment(
                new \OmegaUp\Request($request)
            );
            $this->fail('Should not have allowed adding an experiment');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
        $this->assertEmpty(
            \OmegaUp\DAO\UsersExperiments::getByUserId(intval($user->user_id))
        );

        \OmegaUp\DAO\UsersExperiments::create(new \OmegaUp\DAO\VO\UsersExperiments([
            'user_id' => $user->user_id,
            'experiment' => \OmegaUp\Experiments::USER_README,
        ]));
        try {
            \OmegaUp\Controllers\User::apiRemoveExperiment(
                new \OmegaUp\Request($request)
            );
            $this->fail('Should not have allowed removing an experiment');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
        $this->assertCount(
            1,
            \OmegaUp\DAO\UsersExperiments::getByUserId(intval($user->user_id))
        );
    }

    public function testAdminCanStillAddAndRemoveExperiment() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        [
            'identity' => $adminIdentity,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($adminIdentity);
        $request = [
            'auth_token' => $login->auth_token,
            'username' => $identity->username,
            'experiment' => \OmegaUp\Experiments::USER_README,
        ];

        $response = \OmegaUp\Controllers\User::apiAddExperiment(
            new \OmegaUp\Request($request)
        );
        $this->assertSame('ok', $response['status']);
        $this->assertCount(
            1,
            \OmegaUp\DAO\UsersExperiments::getByUserId(intval($user->user_id))
        );

        $response = \OmegaUp\Controllers\User::apiRemoveExperiment(
            new \OmegaUp\Request($request)
        );
        $this->assertSame('ok', $response['status']);
        $this->assertEmpty(
            \OmegaUp\DAO\UsersExperiments::getByUserId(intval($user->user_id))
        );
    }
}
