<?php
/**
* This script is to be called by crontab using the following parameters:
* php -d max_execution_time=7200 /opt/omegaup/stuff/cron.php
* 
* max_execution_time is set here at 7200 seconds = 2 hours.
**/

require_once('/opt/omegaup/frontend/server/bootstrap.php');
require_once('/opt/omegaup/frontend/server/controllers/QualityNominationController.php');
QualityNominationController::aggreateFeedback(new Request([]));
