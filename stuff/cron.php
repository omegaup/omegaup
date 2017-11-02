#/usr/bin/php -d max_execution_time=7200
<?php
/**
* This script is to be called by crontab.
* max_execution_time is set here at 7200 seconds = 2 hours.
**/

require_once('../frontend/server/bootstrap.php');
QualityNominationsDAO::aggregateFeedback();
