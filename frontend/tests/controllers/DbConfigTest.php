<?php

class DbConfigTest extends \OmegaUp\Test\ControllerTestCase {
    public function testTimeSync() {
        /** @var \OmegaUp\Timestamp|null */
        $dbTime = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT NOW();'
        );

        $this->assertEquals(\OmegaUp\Time::get(), $dbTime->time);
    }

    public function testPhpUtc() {
        $timezone = date_default_timezone_get();

        $this->assertEquals('UTC', $timezone);
    }

    public function testDbUtc() {
        // Go to the DB

        /** @var null|string */
        $timediff = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT TIMEDIFF(NOW(), CONVERT_TZ(NOW(), @@session.time_zone, "+00:00")) d;'
        );

        $this->assertEquals('00:00:00', $timediff);
    }
}
