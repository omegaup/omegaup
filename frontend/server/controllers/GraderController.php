<?php

/**
 * Description of GraderController
 *
 * @author joemmanuel
 */
class GraderController extends Controller {
	
	/**
	 * Validate requests for grader apis
	 * 
	 * @param Request $r
	 * @throws ForbiddenAccessException
	 */
	private static function validateRequest(Request $r) {
		self::authenticateRequest($r);

		if (!Authorization::IsSystemAdmin($r["current_user_id"])) {
			throw new ForbiddenAccessException();
		}
	}
	
	/**
	 * Sets embedded runners to $enabled and triggers a config reload in grader
	 * 
	 * @param boolean $enabled
	 * @throws ApiException
	 * @throws InvalidFilesystemOperationException
	 * @throws InvalidParameterException
	 */
	private static function setEmbeddedRunners($enabled) {
		
		try {			
			Logger::log("Reading grader config file");
			$grader_config = FileHandler::ReadFile(OMEGAUP_GRADER_CONFIG_PATH);

			$count = 0;
			if ($enabled === false) {
				
				Logger::log("Turning off embedded runner in config file");				
				$grader_config = str_replace("grader.embedded_runner.enable=true", "grader.embedded_runner.enable=false", $grader_config, $count);
				
			} else {
				
				Logger::log("Turning on embedded runner in config file");				
				$grader_config = str_replace("grader.embedded_runner.enable=false", "grader.embedded_runner.enable=true", $grader_config, $count);				
			}
			
			// If we didn't replace 
			if ($count < 1) {
				throw new InvalidParameterException("0 replacements in config file done");
			}
				
			Logger::log("Saving gradeer config file");
			FileHandler::CreateFile(OMEGAUP_GRADER_CONFIG_PATH, $grader_config);
			
		} catch (ApiException $apiEx) {
			throw $apiEx;
		} catch (Exception $e) {
			throw new InvalidFilesystemOperationException("Unable to edit grader config file", $e);
		}
		
		
		Logger::log("Calling grader/reload-config");
		$grader = new Grader();
		$response = $grader->reloadConfig();
		
		Logger::log("Reload config response: ". $response);
	}
	
	/**
	 * Entry point to configure omegaup in sardina (local) mode
	 * 
	 * @param Request $r
	 * @return type
	 */
	public static function apiGoLocal(Request $r) {
		
		self::validateRequest($r);
		
		self::setEmbeddedRunners(false);
		
		return array("status" => "ok");
		
	}
	
	/**
	 * Entry point to configure omegaup in ec2 mode
	 * 
	 * @param Request $r
	 * @return type
	 */
	public static function apiGoEc2Remote(Request $r) {

		self::validateRequest($r);
		
		self::setEmbeddedRunners(false);
		
		return array("status" => "ok");
		
	}

}

