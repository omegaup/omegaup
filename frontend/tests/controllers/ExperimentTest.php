<?php

class ExperimentsTest extends PHPUnit_Framework_TestCase {
    const TEST = 'experiment_test';

    private static $kKnownExperiments = [
        self::TEST,
    ];

    private static function getRequestForExperiments(array $experiments) {
        $kvp = [];
        foreach ($experiments as $name) {
            $kvp[] = $name . '=' . Experiments::getExperimentHash($name);
        }
        return [
            Experiments::EXPERIMENT_REQUEST_NAME => implode(',', $kvp),
        ];
    }

    public function testConfigExperiments() {
        $defines = [
            Experiments::EXPERIMENT_PREFIX . strtoupper(self::TEST) => true,
        ];
        $experiments = new
            Experiments([], null, $defines, self::$kKnownExperiments);

        $this->assertEquals(
            self::$kKnownExperiments,
            $experiments->getEnabledExperiments()
        );
        $this->assertTrue($experiments->isEnabled(self::TEST));
    }

    public function testRequestExperiments() {
        $experiments = new
            Experiments(
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
            Experiments(
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
            Experiments(
                [
                    Experiments::EXPERIMENT_REQUEST_NAME =>
                        self::TEST . '=invalid_hash',
                ],
                null,
                [],
                self::$kKnownExperiments
            );

        $this->assertEmpty($experiments->getEnabledExperiments());
        $this->assertFalse($experiments->isEnabled(self::TEST));
    }

    public function testUserExperiments() {
        $user = UserFactory::createUser();
        $experiments = new
            Experiments(
                [],
                $user,
                [],
                self::$kKnownExperiments
            );

        $this->assertEmpty($experiments->getEnabledExperiments());
        $this->assertFalse($experiments->isEnabled(self::TEST));

        // After adding the user-experiment relationship to the database, the
        // experiment should be enabled.
        UsersExperimentsDAO::save(new UsersExperiments([
            'user_id' => $user->user_id,
            'experiment' => self::TEST,
        ]));

        $experiments = new
            Experiments(
                [],
                $user,
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
