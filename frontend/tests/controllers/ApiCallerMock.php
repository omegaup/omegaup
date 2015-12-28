<?php

/**
 * Replaces some logic of ApiCaller to make it phpunit-safe
 *
 * @author joemmanuel
 */
class ApiCallerMock extends ApiCaller{
	/**
	 * Returns the string instad of echoing it
	 *
	 * @param sring $string
	 * @return string
	 */
	public static function printResult($string) {
		return $string;
	}

	/**
	 * headers() is not phpunit-safe. This is a no-op for test
	 *
	 * @param array $response
	 * @return void
	 */
	public static function setHttpHeaders(array $response) {
		return;
	}
}

