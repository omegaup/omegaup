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
	private static function setEmbeddedRunners($value) {

		Logger::log("Calling grader/reload-config");

		$grader = new Grader();
		$response = $grader->reloadConfig(array(
			"overrides" => array(
				"grader.embedded_runner.enable" => $value
			)
				));

		Logger::log("Reload config response: " . $response);
		
		return $response;
	}

	/**
	 * Entry point to configure omegaup in sardina (local) mode
	 * 
	 * @param Request $r
	 * @return type
	 */
	public static function apiScaleIn(Request $r) {

		self::validateRequest($r);		

		return self::setEmbeddedRunners("true");
	}

	/**
	 * Entry point to configure omegaup in ec2 mode
	 * 
	 * @param Request $r
	 * @return type
	 */
	public static function apiScaleOut(Request $r) {

		self::validateRequest($r);

		return self::setEmbeddedRunners("false");
	}

	/**
	 * Calls to /status grader
	 * 
	 * @param Request $r
	 * @return array
	 */
	public static function apiStatus(Request $r) {

		self::validateRequest($r);

		$response = array();
		
		Logger::log("Getting grader /status");
		$grader = new Grader();
		$response["grader"] = $grader->status();
		
		Logger::log("Getting EC2 status");
		$ec2_describe_output = array();
		$return_var = 0;
		exec("ec2-describe-instances --region us-west-1 --simple", $ec2_describe_output, $return_var);
		if ($return_var !== 0) {
			// D:
			Logger::error("ec2-describe-instances --region us-west-1 --simple " . $return_var);
			throw new InvalidFilesystemOperationException("Error executing ec2-describe-instances. Please check log for details");
		}
		
		$response["cloud"] = $ec2_describe_output;
		return $response;
	}

}

