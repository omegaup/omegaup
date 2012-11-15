<?php


require_once("..\..\server\bootstrap.php");

class ApiCaller{

	private static function init() {
		
	}
	
	
	private static function call(Request $request) {
		try {
			$res = $request->exec();

		} catch (ApiException $apiException) {
			Logger::error($e);
			$res = $e->asArray();

		} catch (Exception $e){
			Logger::error($e);
			$res = new InternalServerError($e)->asArray();
		}

		return $res;
	}

	public static function httpEntryPoint(){
		$r = self::init();
		$response = self::call($r);
		self::render($r, $response);
	}

	private static function render(Response $r, array $response) {
		if ($r->renderFormat == Request::HtmlFormat){
			$smarty->assign("EXPLORER_RESPONSE", $response)
			$smarty->display("../templates/explorer.tpl");

		}else {
			echo json_encode($response);

		}
	}

	private static function parseurl( $url ) {
		
	}


}



