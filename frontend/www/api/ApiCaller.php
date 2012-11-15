<?php

require_once("..\..\server\bootstrap.php");


class ApiCaller{

	private static function init() {
		return self::parseUrl();
	}

	public static function call(Request $request) {
		try {
			$res = $request->execute();

		} catch (ApiException $apiException) {
			Logger::error($e);
			$res = $e->asArray();

		} catch (Exception $e){
			Logger::error($e);
			$apiException = new InternalServerError($e);
			$res = $apiException->asArray();
		}

		return $res;
	}

	public static function httpEntryPoint() {
		$r = NULL;
		try {
			$r = self::init();
			var_dump($r);
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

	private static function render(array $response, Request $r = NULL) {

		if (!is_null($r) && $r->renderFormat == Request::HtmlFormat){
			$smarty->assign("EXPLORER_RESPONSE", $response);
			$smarty->display("../templates/explorer.tpl");

		}else {
			echo json_encode($response);

		}
	}

	private static function parseUrl() {
		$apiAsUrl = $_SERVER["REQUEST_URI"];

		$args = explode("/", $apiAsUrl);

		$controllerName = ucfirst($args[2]) . "Controller";

		if(!class_exists($controllerName))
		{
			throw new NotFoundException("Api not found.");
		}

		$request = new Request();

		// Just to double check that we are
		// only instatiate a controller.
		switch($controllerName)
		{
			case "SesionController":
			case "UserController":
				$request->method = $controllerName . "::" . $args[3];
				break;
			default:
				throw new NotFoundException("Api not found.");
		}

		return $request;
	}


}



