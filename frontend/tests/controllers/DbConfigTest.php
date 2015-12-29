<?php

class DbConfigTest extends PHPUnit_Framework_TestCase
{
    public function testTimeSync()
    {
        $db_time = Utils::GetDbDatetime();
        $php_time = date('Y-m-d H:i:s');

        $this->assertEquals($php_time, $db_time);
    }

    public function testPhpUtc()
    {
        $timezone = date_default_timezone_get();

        $this->assertEquals('UTC', $timezone);
    }

    public function testDbUtc()
    {
        // Go to the DB
        global $conn;

        $sql = "select timediff(now(),convert_tz(now(),@@session.time_zone,'+00:00')) d";
        $rs = $conn->GetRow($sql);

        $this->assertEquals('00:00:00', $rs['d']);
    }
}
