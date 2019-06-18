<?php
namespace {
    define('IS_TEST', true);
    // Set timezone to UTC
    date_default_timezone_set('UTC');
    // Set remote address to localhost.
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    define('OMEGAUP_ROOT', __DIR__ . '/..');
    // Load test specific config globals
    // Do not panic if the test-specific override file is not present.
    @include_once(OMEGAUP_ROOT . '/tests/test_config.php');
    require_once(OMEGAUP_ROOT . '/tests/test_config.default.php');
    require_once(OMEGAUP_ROOT . '/server/config.default.php');
    // Load api caller
    require_once(OMEGAUP_ROOT . '/www/api/ApiCaller.php');
    require_once(OMEGAUP_ROOT . '/tests/controllers/ApiCallerMock.php');
    // Load test utils
    require_once(OMEGAUP_ROOT . '/tests/controllers/OmegaupTestCase.php');
    require_once(OMEGAUP_ROOT . '/tests/badges/BadgesTestCase.php');
    require_once(OMEGAUP_ROOT . '/tests/common/Utils.php');
    // Load Factories
    require_once(OMEGAUP_ROOT . '/tests/factories/ProblemsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/ContestsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/ClarificationsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/UserFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/IdentityFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/CoursesFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/RunsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/GroupsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/SchoolsFactory.php');
    require_once(OMEGAUP_ROOT . '/tests/factories/QualityNominationFactory.php');
    require_once(OMEGAUP_ROOT . '/server/libs/Time.php');
    Utils::CleanupFilesAndDb();
    // Clean APC cache
    Cache::clearCacheForTesting();
    QualityNominationFactory::initQualityReviewers();
    QualityNominationFactory::initTags();
    // Mock time
    $currentTime = time();
    Time::setTimeForTesting($currentTime);
    $conn->Execute("SET TIMESTAMP = {$currentTime};");
    Grader::setInstanceForTesting(new NoOpGrader());
}
