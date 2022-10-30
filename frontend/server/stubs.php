<?php

namespace OmegaUp;

// For some reason, psalm does not recognize this as being defined as a string.
if (!defined('OMEGAUP_ROOT')) {
    define('OMEGAUP_ROOT', strval(dirname(__DIR__)));
}

// We need to let Psalm know that some config variables are configurable.
if (!defined('IS_TEST')) {
    define('IS_TEST', /** @var bool $x */ $x = false);
    define('NEW_RELIC_SCRIPT_HASH', /** @var ?string $x */ $x = '');
    define(
        'OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT',
        /** @var bool $x */ $x = false
    );
    define('OMEGAUP_EMAIL_SENDY_ENABLE', /** @var bool $x */ $x = true);
    define('OMEGAUP_EMAIL_SEND_EMAILS', /** @var bool $x */ $x = false);
    define(
        'OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE',
        /** @var bool $x */ $x = true
    );
    define(
        'OMEGAUP_ENABLE_SOCIAL_MEDIA_RESOURCES',
        /** @var bool $x */ $x = true
    );
    define('OMEGAUP_FORCE_EMAIL_VERIFICATION', /** @var bool $x */ $x = false);
    define('OMEGAUP_GA_TRACK', /** @var bool $x */ $x = false);
    define('OMEGAUP_GRADER_FAKE', /** @var bool $x */ $x = false);
    define('OMEGAUP_LOCKDOWN', /** @var bool $x */ $x = false);
    define('OMEGAUP_LOG_DB_QUERYS', /** @var bool $x */ $x = false);
    define('OMEGAUP_LOG_TO_FILE', /** @var bool $x */ $x = true);
    define('OMEGAUP_MAINTENANCE', /** @var ?string $x */ $x = null);
    define('OMEGAUP_SESSION_CACHE_ENABLED', /** @var bool $x */ $x = true);
    define('OMEGAUP_VALIDATE_CAPTCHA', /** @var bool $x */ $x = false);
}
