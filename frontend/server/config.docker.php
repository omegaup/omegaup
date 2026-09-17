<?php

/**
 * Fallback defines for the docker development image when config.php is
 * incomplete. Each constant is set only if config.php did not define it.
 * Values match stuff/docker/usr/bin/developer-environment.sh.
 */
if (!defined('OMEGAUP_GITSERVER_SECRET_KEY')) {
    define(
        'OMEGAUP_GITSERVER_SECRET_KEY',
        'GdhxduUWe/y18iCnEWbTFX+JE4O8vSQPTUkjWtWf6ASAoSDkmUg4DUGwjERNliGN35kZyFj+tl5AzQaF4Ba9fA=='
    );
}
if (!defined('OMEGAUP_GITSERVER_PUBLIC_KEY')) {
    define(
        'OMEGAUP_GITSERVER_PUBLIC_KEY',
        'gKEg5JlIOA1BsIxETZYhjd+ZGchY/rZeQM0GheAWvXw='
    );
}
if (!defined('OMEGAUP_GRADER_SECRET')) {
    define('OMEGAUP_GRADER_SECRET', 'secret');
}
if (!defined('OMEGAUP_COURSE_CLONE_SECRET_KEY')) {
    define(
        'OMEGAUP_COURSE_CLONE_SECRET_KEY',
        '6f8xSU_xkrelmCTSahbbxl3PRovgAfkrThyrqQ9JesE'
    );
}
if (!defined('OMEGAUP_GOOGLE_SECRET')) {
    define('OMEGAUP_GOOGLE_SECRET', 'acmtr0Y37vnTVJV4BwmdhOsK');
}
if (!defined('OMEGAUP_GOOGLE_CLIENTID')) {
    define(
        'OMEGAUP_GOOGLE_CLIENTID',
        '982542692060-lf9htvij4ba13fiufpqeldic0qqqvird.apps.googleusercontent.com'
    );
}
