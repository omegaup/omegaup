<?php

/**
 * VirtualContestTest
 *
 * @author carlosabcs
 */

class BadgesTest extends OmegaupTestCase {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';
    const QUERY_FILE = 'query.sql';
    const TEST_FILE = 'test.json';

    public function testAllBadges() {
        $aliases = glob(static::OMEGAUP_BADGES_ROOT . '/*', GLOB_ONLYDIR);
        foreach ($aliases as $alias) {
            exec(
                'python3 ' . escapeshellarg(OMEGAUP_ROOT) . '/../stuff/check_badge.py' .
                ' --badge ' . escapeshellarg($alias),
                $output,
                $return
            );
            // If return code is 0, everything was OK
            $this->assertEquals(0, $return);
        }
    }
}
