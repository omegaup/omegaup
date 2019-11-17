<?php

namespace {
    define('IS_TEST', true);
    // Set timezone to UTC
    date_default_timezone_set('UTC');
    // Set remote address to localhost.
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    define('OMEGAUP_ROOT', dirname(__DIR__));
    // Load test specific config globals
    // Do not panic if the test-specific override file is not present.
    @include_once(OMEGAUP_ROOT . '/tests/test_config.php');
    require_once(OMEGAUP_ROOT . '/tests/test_config.default.php');
    require_once(OMEGAUP_ROOT . '/server/config.default.php');
    require_once(OMEGAUP_ROOT . '/server/bootstrap.php');
    // Load api caller
    require_once(OMEGAUP_ROOT . '/tests/ApiCallerMock.php');
    // Load test utils
    require_once(OMEGAUP_ROOT . '/tests/ControllerTestCase.php');
    require_once(OMEGAUP_ROOT . '/tests/BadgesTestCase.php');
    require_once(OMEGAUP_ROOT . '/tests/Utils.php');
    // Load Factories
    require_once(OMEGAUP_ROOT . '/tests/Factories/Contest.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Course.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Problem.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Run.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/User.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/ClarificationsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/IdentityFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/GroupsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/SchoolsFactory.php');
    require_once(
        OMEGAUP_ROOT . '/tests/factories/QualityNominationFactory.php'
    );
    \OmegaUp\Test\Utils::cleanupFilesAndDB();
    // Clean APC cache
    \OmegaUp\Cache::clearCacheForTesting();
    QualityNominationFactory::initQualityReviewers();
    QualityNominationFactory::initTags();

    \OmegaUp\Grader::setInstanceForTesting(new \OmegaUp\Test\NoOpGrader());
}
