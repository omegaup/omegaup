<?php

namespace OmegaUp\Test;

/**
 * Parent class of all Test cases for omegaUp badges
 *
 * @author carlosabcs
 */
class BadgesTestCase extends \OmegaUp\Test\ControllerTestCase {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';
    const BADGES_TESTS_ROOT = OMEGAUP_ROOT . '/tests/badges';
    const MAX_BADGE_SIZE = 20 * 1024;
    const ICON_FILE = 'icon.svg';
    const LOCALIZATIONS_FILE = 'localizations.json';
    const QUERY_FILE = 'query.sql';
    const TEST_FILE = 'test.json';

    /**
     * @readonly
     * @var \OmegaUp\FileUploader
     */
    private $originalFileUploader;

    public function setUp(): void {
        parent::setUp();
        \OmegaUp\Time::setTimeForTesting(null);
        \OmegaUp\Test\Utils::CleanupDb();
        $this->originalFileUploader = \OmegaUp\FileHandler::getfileUploader();
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );
    }

    public function tearDown(): void {
        parent::tearDown();
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->originalFileUploader
        );
    }

    public static function getSortedResults(string $query) {
        /** @var list<array{user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($query);
        $results = [];
        foreach ($rs as $user) {
            $results[] = $user['user_id'];
        }
        asort($results);
        return $results;
    }
}
