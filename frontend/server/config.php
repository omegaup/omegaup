<?php
  # #####################################
  # DATABASE CONFIG
  # ####################################
  define('OMEGAUP_DB_USER',         'omegaup');
  define('OMEGAUP_DB_PASS',         'omegaup');
  define('OMEGAUP_DB_HOST',         'localhost');
  define('OMEGAUP_DB_NAME',         'omegaup');	
  define('OMEGAUP_DB_DRIVER',       'mysqlt');
  define('OMEGAUP_DB_DEBUG',        false);	
  define('OMEGAUP_ROOT',            '/home/lhchavez/omegaup/frontend');

  define('OMEGAUP_LOG_TO_FILE',     true);
  define('OMEGAUP_LOG_ACCESS_FILE', '/home/lhchavez/omegaup/frontend/log/omegaup.log');
  define('OMEGAUP_LOG_ERROR_FILE',  '/home/lhchavez/omegaup/frontend/log/omegaup.log');
  define('OMEGAUP_LOG_TRACKBACK',   false);
  define('OMEGAUP_LOG_DB_QUERYS',   true);

  ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . OMEGAUP_ROOT . '/server');
