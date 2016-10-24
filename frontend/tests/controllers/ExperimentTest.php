<?php

class ExperimentsTest extends PHPUnit_Framework_TestCase {
    const TEST = 'experiment_test';

    private static array $kKnownExperiments = array(
        self::TEST,
    );

    private static function getRequestForExperiments(array $experiments) {
        $kvp = array();
        foreach ($experiments as $name) {
            $kvp[] = $name . '=' . Experiments::getExperimentHash($name);
        }
        return array(
            Experiments::EXPERIMENT_REQUEST_NAME => implode(',', $kvp),
        );
    }

    public function testConfigExperiments() {
        $defines = array(
            Experiments::EXPERIMENT_PREFIX . strtoupper(self::TEST) => true,
        );
        $experiments = new
            Experiments(array(), $defines, self::$kKnownExperiments);

        $this->assertEquals(
            self::$kKnownExperiments,
            $experiments->getEnabledExperiments()
        );
        $this->assertTrue($experiments->isEnabled(self::TEST));
    }

    public function testRequestExperiments() {
        $experiments = new
            Experiments(
                self::getRequestForExperiments(array(self::TEST)),
                array(),
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
                self::getRequestForExperiments(array('foo')),
                array(),
                self::$kKnownExperiments
            );

        $this->assertEmpty($experiments->getEnabledExperiments());
        $this->assertFalse($experiments->isEnabled('foo'));
    }

    public function testRequestInvalidExperiments() {
        $experiments = new
            Experiments(
                array(
                    Experiments::EXPERIMENT_REQUEST_NAME =>
                        self::TEST . '=invalid_hash',
                ),
                array(),
                self::$kKnownExperiments
            );

        $this->assertEmpty($experiments->getEnabledExperiments());
        $this->assertFalse($experiments->isEnabled(self::TEST));
    }
}
