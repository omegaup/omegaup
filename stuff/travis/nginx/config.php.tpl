<?php
define('OMEGAUP_ROOT', '${OMEGAUP_ROOT}/frontend');
define('OMEGAUP_DB_HOST', 'localhost');
define('OMEGAUP_DB_NAME', 'omegaup');
define('OMEGAUP_DB_PASS', '');
define('OMEGAUP_DB_USER', 'travis');
// Setting non-development mode to make all requests faster by avoiding parsing
// Smarty templates every time.
define('OMEGAUP_DEVELOPMENT_MODE', 'false');
define('SMARTY_CACHE_DIR', '/tmp');
define('OMEGAUP_LOG_FILE', '/tmp/omegaup.log');
define('OMEGAUP_CSP_LOG_FILE', '/tmp/csp.log');
define('RUNS_PATH', '/tmp/omegaup/submissions');
define('GRADE_PATH', '/tmp/omegaup/grade');
define('PROBLEMS_GIT_PATH', '/tmp/omegaup/problems.git');
