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
    // This is required before bootstrap.php is invoked.
    @mkdir(OMEGAUP_TEST_ROOT, 0755, true);
    require_once(OMEGAUP_ROOT . '/server/bootstrap.php');
    // Load api caller
    require_once(OMEGAUP_ROOT . '/tests/ApiCallerMock.php');
    // Load test utils
    require_once(OMEGAUP_ROOT . '/tests/ControllerTestCase.php');
    require_once(OMEGAUP_ROOT . '/tests/BadgesTestCase.php');
    require_once(OMEGAUP_ROOT . '/tests/Utils.php');
    // Load Factories
    require_once(OMEGAUP_ROOT . '/tests/Factories/Clarification.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Contest.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Course.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Groups.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Identity.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Problem.php');
    require_once(
        OMEGAUP_ROOT . '/tests/Factories/QualityNomination.php'
    );
    require_once(OMEGAUP_ROOT . '/tests/Factories/Run.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/Schools.php');
    require_once(OMEGAUP_ROOT . '/tests/Factories/User.php');
    \OmegaUp\Test\Utils::cleanupFilesAndDB();

    \OmegaUp\Grader::setInstanceForTesting(new \OmegaUp\Test\NoOpGrader());
}
