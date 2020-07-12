<?php

namespace OmegaUp\Test;

/**
 * Parent class of all Test cases for omegaUp badges
 *
 * @author carlosabcs
 */
class BadgesTestCase extends \OmegaUp\Test\ControllerTestCase {
    /** @psalm-suppress MixedOperand OMEGAUP_ROOT is definitely defined. */
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';
    /** @psalm-suppress MixedOperand OMEGAUP_ROOT is definitely defined. */
    const BADGES_TESTS_ROOT = OMEGAUP_ROOT . '/tests/badges';
    const MAX_BADGE_SIZE = 20 * 1024;
    const ICON_FILE = 'icon.svg';
    const LOCALIZATIONS_FILE = 'localizations.json';
    const QUERY_FILE = 'query.sql';
    const TEST_FILE = 'test.json';

    /**
     * @var \OmegaUp\FileUploader|null
     */
    private $originalFileUploader = null;

    public function setUp(): void {
        parent::setUp();
        \OmegaUp\Time::setTimeForTesting(null);
        $this->originalFileUploader = \OmegaUp\FileHandler::getFileUploader();
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );
    }

    public function tearDown(): void {
        parent::tearDown();
        $this->assertNotNull($this->originalFileUploader);
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->originalFileUploader
        );
    }

    /**
     * @return list<int>
     */
    public static function getSortedResults(string $query) {
        /** @var list<array{user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($query);
        $results = [];
        foreach ($rs as $user) {
            $results[] = $user['user_id'];
        }
        sort($results);
        return $results;
    }
}
