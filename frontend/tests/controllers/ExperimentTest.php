<?php

class ExperimentsTest extends OmegaupTestCase {
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

        $this->assertEquals(
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

        $this->assertEquals(
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
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
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

        $this->assertEquals(
            self::$kKnownExperiments,
            $experiments->getEnabledExperiments()
        );
        $this->assertTrue($experiments->isEnabled(self::TEST));
    }
}
