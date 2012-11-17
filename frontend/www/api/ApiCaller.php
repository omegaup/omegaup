<?php

require_once("..\..\server\bootstrap.php");

/**
 * Encapsulates calls to the API and provides initialization and
 * error handling (try catch) logic for logging and alerting
 * 
 */
class ApiCaller{

	/**
	 * Initializes the Request before calling API
	 * 
	 * @return Request
	 */
	private static function init() {
		return self::parseUrl();
	}

	/**
	 * Execute the request and return the response as associative
	 * array.
	 * 
	 * @param Request $request
	 * @return array
	 */
	public static function call(Request $request) {
            
		try {
			
			$response = $request->execute();
			
		} catch (ApiException $apiException) {
			Logger::error($e);
			$response = $e->asArray();

		} catch (Exception $e){
			Logger::error($e);
			$apiException = new InternalServerError($e);
			$response = $apiException->asArray();
		}

		return $response;
	}

	/**
	 *Handles main API workflow. All HTTP API calls start here.
	 * 
	 */
	public static function httpEntryPoint() {
		$r = NULL;
		try {
			$r = self::init();			
			$response = self::call($r);

		} catch (ApiException $apiException) {
			Logger::error($apiException);
			$response = $apiException->asArray();

		} catch (Exception $e){
			Logger::error($e);
			$apiException = new InternalServerError($e);
			$response = $apiException->asArray();
		}

		self::render($response, $r);
	}

	/**
	 * Renders the response properly and, in the case of HTTP API,
	 * sets the header
	 * 
	 * @param array $response
	 * @param Request $r
	 */
	private static function render(array $response, Request $r = NULL) {
		if (!is_null($r) && $r->renderFormat == Request::HtmlFormat){
			$smarty->assign("EXPLORER_RESPONSE", $response);
			$smarty->display("../templates/explorer.tpl");
		} else {				
			self::setHttpHeaders($response);			
			$json_result = json_encode($response);
			
			if ($json_result === false) {
				Logger::error("json_encode failed for: ". implode(",", $response));
				$apiException = new InternalServerError();
				$json_result = json_encode($apiException->asArray());
			}
			
			echo $json_result;						
		}
	}

	/**
	 * Parses the URI from $_SERVER and determines which controller and
	 * function to call.
	 * 
	 * @return Request
	 * @throws NotFoundException
	 */
	private static function parseUrl() {
		$apiAsUrl = $_SERVER["REQUEST_URI"];
		$args = explode("/", $apiAsUrl);
		
		if ($args === false || count($args) < 2) {
			Logger::error("Api called with URI with less args than expected: ".count($args));
			throw new NotFoundException("Api requested not found.");
		}
		
		$controllerName = ucfirst($args[2]);
		
		// Making "view" as default method
		if (!isset($args[3])) {
			$methodName = "View";
		} else {
			$methodName = ucfirst($args[3]);
		}
				
		$controllerName = str_remove(chr(0), '', $controllerName);
		$methodName = str_remove(chr(0), '', $methodName);

		$controllerName = $controllerName."Controller";
		$methodName = "api".$methodName;
		
		if(!class_exists($controllerName)) {
			Logger::error("Controller name was not found: ". $controllerName);
			throw new NotFoundException("Api requested not found.");
		}

		$request = new Request($_REQUEST);

		// Just to double check that we are
		// only instatiate a controller.
		switch($controllerName) {
			case "SesionController":
			case "UserController":
				$request->method = $controllerName . "::" . $methodName;
				break;
			default:
				Logger::error("Controller name was not found: ". $controllerName);
				throw new NotFoundException("Api requested not found.");
				break;
		}
		
		return $request;
	}
	
	/**
	 * Sets all required headers for the API called via HTTP
	 * 
	 * @param array $response
	 */
	private static function setHttpHeaders(array $response) {
		
		// Scumbag IE y su cache agresivo.
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		// Set header accordingly
		if ($response["status"] === "error" && isset($response["header"])) {
			header($response["header"]);
		} else {
			header('Content-Type: application/json');
		}		
	}
}



