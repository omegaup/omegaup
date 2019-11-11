<?php

class DbConfigTest extends OmegaupTestCase {
    public function testTimeSync() {
        /** @var string|null */
        $dbTime = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT NOW();'
        );
        $phpTime = date('Y-m-d H:i:s', \OmegaUp\Time::get());

        $this->assertEquals($phpTime, $dbTime);
    }

    public function testPhpUtc() {
        $timezone = date_default_timezone_get();

        $this->assertEquals('UTC', $timezone);
    }

    public function testDbUtc() {
        // Go to the DB

        $sql = "select timediff(now(),convert_tz(now(),@@session.time_zone,'+00:00')) d";
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql);

        $this->assertEquals('00:00:00', $rs['d']);
    }
}
