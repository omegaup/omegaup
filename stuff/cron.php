#/usr/bin/php -d max_execution_time=720

<?php
/**
* This script is to be called by crontab.
* max_execution_time is set here at 7200 seconds = 2 hours.
**/

require_once('/opt/omegaup/frontend/server/bootstrap.php');
require_once('/opt/omegaup/frontend/server/controllers/QualityNominationController.php');
QualityNominationController::aggregateFeedback(new Request([]));
