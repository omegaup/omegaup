<?php

/**
 * VirtualContestTest
 *
 * @author carlosabcs
 */

class BadgesTest extends OmegaupTestCase {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';
    const MAX_BADGE_SIZE = 20 * 1024;
    const ICON_FILE = 'icon.svg';
    const LOCALIZATIONS_FILE = 'localizations.json';
    const QUERY_FILE = 'query.sql';
    const TEST_FILE = 'test.json';

    public function testAllBadges() {
        $aliases = glob(static::OMEGAUP_BADGES_ROOT . '/*', GLOB_ONLYDIR);
        foreach ($aliases as $alias) {
            // Gets the string after last '/'
            $badge = substr($alias, strrpos($alias, '/') + 1);

            $icon_path = $alias . '/' . static::ICON_FILE;
            if (file_exists($icon_path)) {
                $this->assertLessThanOrEqual(
                    static:: MAX_BADGE_SIZE,
                    filesize($icon_path),
                    "$badge:> The size of icon.svg must be less than or equal to 20KB."
                );
            }

            $loc_path = $alias . '/' . static::LOCALIZATIONS_FILE;
            $this->assertTrue(
                file_exists($loc_path),
                "$badge:> The file localizations.json doesn't exist."
            );

            $query_path = $alias . '/' . static::QUERY_FILE;
            $this->assertTrue(
                file_exists($query_path),
                "$badge:> The file query.sql doesn't exist."
            );

            $test_path = $alias . '/' . static::TEST_FILE;
            $this->assertTrue(
                file_exists($test_path),
                "$badge:> The file test.json doesn't exist."
            );

            // From here I must run the test.json + query.sql
        }
    }
}
