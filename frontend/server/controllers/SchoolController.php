<?php

/**
 * Description of SchoolController
 *
 * @author joemmanuel
 */
class SchoolController extends Controller {
	
	/**
	 * Gets a list of schools 
	 * 
	 * @param Request $r
	 */
	public static function apiList(Request $r) {

		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["term"], "term");

		try {
			$schools = SchoolsDAO::findByName($r["term"]);			
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$response = array();
		foreach ($schools as $school) {
			$entry = array("label" => $school->getName(), "value" => $school->getName(), "id" => $school->getSchoolId());
			array_push($response, $entry);
		}

		return $response;
	}
	
	/**
	 * Create new school
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws InvalidParameterException
	 */
	public static function apiCreate(Request $r) {
		
		self::authenticateRequest($r);
		
		Validators::isStringNonEmpty($r["name"], "name");
		Validators::isNumber($r["state_id"], "state_id", false);
		
		if (!is_null($r["state_id"])) {
			try {
				$r["state"] = StatesDAO::getByPK($r["state_id"]);
			} catch (Exception $e) { 
				throw new InvalidDatabaseOperationException($e);
			}
			
			if (is_null($r["state"])) {
				throw new InvalidParameterException("State not found");
			}
		}
		
		// Create school
		$school = new Schools(array("name" => $r["name"], "state_id" => $r["state_id"]));
		
		try {			
			SchoolsDAO::save($school);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		} 
		
		return array("status" => "ok", "school_id" => $school->getSchoolId());
	}
	
}

