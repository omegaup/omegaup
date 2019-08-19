<?php

/**
 * Parent class of all Test cases for omegaUp badges
 *
 * @author carlosabcs
 */
class BadgesTestCase extends OmegaupTestCase {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';
    const BADGES_TESTS_ROOT = OMEGAUP_ROOT . '/tests/badges';
    const MAX_BADGE_SIZE = 20 * 1024;
    const ICON_FILE = 'icon.svg';
    const LOCALIZATIONS_FILE = 'localizations.json';
    const QUERY_FILE = 'query.sql';
    const TEST_FILE = 'test.json';

    public function setUp() {
        parent::setUp();
        Time::setTimeForTesting(null);
        Utils::CleanupDb();
    }

    public static function getSortedResults(string $query) {
        $rs = MySQLConnection::getInstance()->GetAll($query);
        $results = [];
        foreach ($rs as $user) {
            $results[] = $user['user_id'];
        }
        asort($results);
        return $results;
    }
}
