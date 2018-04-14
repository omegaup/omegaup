<?php

require_once(__DIR__.'/../../server/bootstrap.php');

/**
 * Encapsulates calls to the API and provides initialization and
 * error handling (try catch) logic for logging and alerting
 *
 */
class ApiCaller {
    public static $log;

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
        } catch (InvalidCredentialsException $e) {
            // No log because the code that threw it already logged.
            $response = $e->asResponseArray();
        } catch (ApiException $e) {
            self::$log->error($e);
            $response = $e->asResponseArray();
        } catch (Exception $e) {
            self::$log->error($e);
            $apiException = new InternalServerErrorException($e);
            $response = $apiException->asResponseArray();
        }

        return $response;
    }

    /**
     * Detects CSRF attempts.
     *
     * @return whether this was a CSRF attempt.
     */
    private static function isCSRFAttempt() {
        if (empty($_SERVER['HTTP_REFERER'])) {
            // This API request was explicitly created.
            return false;
        }
        $referrer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        if ($referrer_host === false) {
            // Malformed referrer. Fail closed and prefer to not allow this.
            return true;
        }
        // Instead of attempting to exactly match the whole URL, just ensure
        // the host is the same. Otherwise this would break tests and local
        // development environments.
        $omegaup_url_host = parse_url(OMEGAUP_URL, PHP_URL_HOST);
        return $referrer_host !== $omegaup_url_host;
    }

    /**
     *Handles main API workflow. All HTTP API calls start here.
     *
     */
    public static function httpEntryPoint() {
        $r = null;
        try {
            $r = self::init();
            if (self::isCSRFAttempt()) {
                throw new CSRFException();
            }
            $response = self::call($r);
        } catch (ApiException $apiException) {
            self::$log->error($apiException);
            $response = $apiException->asResponseArray();
        } catch (Exception $e) {
            self::$log->error($e);
            $apiException = new InternalServerErrorException($e);
            $response = $apiException->asResponseArray();
        }

        if (is_null($response) || !is_array($response)) {
            $apiException = new InternalServerErrorException(new Exception('Api did not return an array.'));
            self::$log->error($apiException);
            $response = $apiException->asResponseArray();
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
    private static function render(array $response, Request $r = null) {
        if (!is_null($r) && $r->renderFormat == Request::HTML_FORMAT) {
            $smarty->assign('EXPLORER_RESPONSE', $response);
            $smarty->display('../templates/explorer.tpl');
        } else {
            static::setHttpHeaders($response);
            $json_result = json_encode($response);

            if ($json_result === false) {
                self::$log->warn('json_encode failed for: '. print_r($response, true));
                if (json_last_error() == JSON_ERROR_UTF8) {
                    // Attempt to recover gracefully, removing any unencodeable
                    // elements from the response. This should at least prevent
                    // completely and premanently breaking some scenarios, like
                    // trying to fix a problem with illegal UTF-8 codepoints.
                    $json_result = json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
                }
                if ($json_result === false) {
                    $apiException = new InternalServerErrorException();
                    $json_result = json_encode($apiException->asResponseArray());
                }
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
        $apiAsUrl = $_SERVER['REQUEST_URI'];
        // Spliting only by '/' results in URIs with parameters like this:
        //		/api/problem/list/?page=1
        //						 ^^
        // Adding '?' as a separator results in URIs like this:
        //		/api/problem/list?page=1
        //						 ^
        $args = preg_split('/[\/?]/', $apiAsUrl);

        if ($args === false || count($args) < 2) {
            self::$log->error('Api called with URI with less args than expected: '.count($args));
            throw new NotFoundException('apiNotFound');
        }

        $controllerName = ucfirst($args[2]);

        // Removing NULL bytes
        $controllerName = str_replace(chr(0), '', $controllerName);
        $methodName = str_replace(chr(0), '', $args[3]);

        $controllerName = $controllerName.'Controller';

        if (!class_exists($controllerName)) {
            self::$log->error('Controller name was not found: '. $controllerName);
            throw new NotFoundException('apiNotFound');
        }

        // Create request
        $request = new Request($_REQUEST);

        // Prepend api
        $methodName = 'api'.$methodName;

        // Check the method
        if (!method_exists($controllerName, $methodName)) {
            self::$log->error('Method name was not found: '. $controllerName.'::'.$methodName);
            throw new NotFoundException('apiNotFound');
        }

        // Get the auth_token and user data from cookies
        $cs = SessionController::apiCurrentSession()['session'];

        // If we got an auth_token from cookies, replace it
        if (!is_null($cs['auth_token'])) {
            $request['auth_token'] = $cs['auth_token'];
        }

        for ($i = 4; ($i+1) < sizeof($args); $i += 2) {
            $request[$args[$i]] = urldecode($args[$i+1]);
        }

        $request->method = $controllerName . '::' . $methodName;

        return $request;
    }

    /**
     * Sets all required headers for the API called via HTTP
     *
     * @param array $response
     */
    private static function setHttpHeaders(array $response) {
        // Scumbag IE y su cache agresivo.
        header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Set header accordingly
        if (isset($response['header'])) {
            header($response['header']);
            if ($response['header'] == 'HTTP/1.1 401 UNAUTHORIZED') {
                header('WWW-Authenticate: omegaUp location="/login"');
            }
        } else {
            header('Content-Type: application/json');
        }
    }
}

ApiCaller::$log = Logger::getLogger('ApiCaller');
