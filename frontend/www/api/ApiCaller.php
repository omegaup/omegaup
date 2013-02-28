<?php

require_once("../../server/bootstrap.php");

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
			
		} catch (ApiException $e) {
			Logger::error($e);
			$response = $e->asArray();

		} catch (Exception $e){
			Logger::error($e);
			$apiException = new InternalServerErrorException($e);
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
			$apiException = new InternalServerErrorException($e);
			$response = $apiException->asArray();
		}
		
		if (is_null($response) || !is_array($response)) {
			$apiException = new InternalServerErrorException(new Exception("Api did not return an array."));
			Logger::log($apiException);
			$response = $apiException->asArray();
		}
		
		return self::render($response, $r);
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
			static::setHttpHeaders($response);
			$json_result = json_encode($response);

			if ($json_result === false) {
				Logger::error("json_encode failed for: ". implode(",", $response));
				$apiException = new InternalServerErrorException();
				$json_result = json_encode($apiException->asArray());
			}							
			
			// Print the result using late static binding semantics
			// Return needed for testability purposes, for production it 
			// returns void.
			return static::printResult($json_result);
		}
	}
	
	/**
	 * In production, prints the result.
	 * Decoupled for testability purposes
	 * 
	 * @param string $string
	 */
	private static function printResult($string) {
		echo $string;
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

		// Removing NULL bytes
		$controllerName = str_replace(chr(0), '', $controllerName);
		$methodName = str_replace(chr(0), '', $args[3]);

		$controllerName = $controllerName."Controller";

		if(!class_exists($controllerName)) {
			Logger::error("Controller name was not found: ". $controllerName);
			throw new NotFoundException("Api requested not found.");
		}

		// Create request
		$request = new Request($_REQUEST);

		// Prepend api
		$methodName = "api".$methodName;

		// Check the method
		if(!method_exists($controllerName, $methodName)) {
			Logger::error("Method name was not found: ". $controllerName."::".$methodName);
			throw new NotFoundException("Api requested not found.");
		}

		$cs = SessionController::apiCurrentSession();
		$request["auth_token"] = $cs["auth_token"];

		// Figure out if its POST or GET
		switch( $_SERVER['REQUEST_METHOD'] ) {
			case 'GET':
				for ($i = 4; ($i+1) < sizeof( $args ); $i += 2) {
					$request[$args[$i]] = urldecode($args[$i+1]);
				}
			break;
			case 'POST':
				// Parameters are already in the Request object
			break;
		}
	


		$request->method = $controllerName . "::" . $methodName;
	
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
		if (isset($response["header"])) {
			header($response["header"]);
		} else {
			header("Content-Type: application/json");
		}
	}
}



