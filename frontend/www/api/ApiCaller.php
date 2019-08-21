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
     * Execute the request and return the response as associative
     * array.
     *
     * @param Request $request
     * @return array
     */
    public static function call(Request $request) {
        try {
            $response = $request->execute();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
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
        $allowed_hosts = [
            parse_url(OMEGAUP_URL, PHP_URL_HOST),
            OMEGAUP_LOCKDOWN_DOMAIN,
        ];
        return !in_array($referrer_host, $allowed_hosts, true);
    }

    /**
     * Handles main API workflow. All HTTP API calls start here.
     */
    public static function httpEntryPoint() : string {
        $r = null;
        $apiException = null;
        try {
            if (self::isCSRFAttempt()) {
                throw new CSRFException();
            }
            $r = self::createRequest();
            $response = $r->execute();
            if (is_null($response) || !is_array($response)) {
                $apiException = new InternalServerErrorException(
                    new Exception('API did not return an array.')
                );
            }
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            $apiException = $e;
        } catch (Exception $e) {
            $apiException = new InternalServerErrorException($e);
        }

        if (!is_null($apiException)) {
            self::$log->error($apiException);
            if (extension_loaded('newrelic') && $apiException->getCode() == 500) {
                newrelic_notice_error($apiException);
            }
            $response = $apiException->asResponseArray();
        }

        return self::render($response, $r);
    }

    /**
     * Determines whether the array is associative or packed.
     *
     * @param array $array the input array.
     *
     * @return boolean whether the array is associative.
     */
    static function isAssociativeArray(array &$array) {
        if (!is_array($array)) {
            return false;
        }
        $i = 0;
        foreach ($array as $key => &$value) {
            if ($key != $i++) {
                return true;
            }
        }
        return false;
    }

    /**
     * Renders the response properly and sets the HTTP header.
     *
     * @param array $response
     * @param Request $r
     */
    private static function render(array $response, ?Request $r = null) : string {
        // Only add the request ID if the response is an associative array. This
        // allows the APIs that return a flat array to return the right type.
        if (self::isAssociativeArray($response)) {
            $response['_id'] = Request::requestId();
        }
        $jsonEncodeFlags = 0;
        // If this request is being explicitly made from the browser,
        // pretty-print the response.
        if (!is_null($r) && $r['prettyprint'] == 'true') {
            $jsonEncodeFlags = JSON_PRETTY_PRINT;
        }
        static::setHttpHeaders($response);
        $jsonResult = json_encode($response, $jsonEncodeFlags);

        if ($jsonResult === false) {
            self::$log->warn('json_encode failed for: '. print_r($response, true));
            if (json_last_error() == JSON_ERROR_UTF8) {
                // Attempt to recover gracefully, removing any unencodeable
                // elements from the response. This should at least prevent
                // completely and premanently breaking some scenarios, like
                // trying to fix a problem with illegal UTF-8 codepoints.
                $jsonResult = json_encode(
                    $response,
                    $jsonEncodeFlags|JSON_PARTIAL_OUTPUT_ON_ERROR
                );
            }
            if ($jsonResult === false) {
                $apiException = new InternalServerErrorException();
                self::$log->error($apiException);
                if (extension_loaded('newrelic')) {
                    newrelic_notice_error($apiException);
                }
                $jsonResult = json_encode($apiException->asResponseArray());
            }
        }
        return $jsonResult;
    }

    /**
     * Parses the URI from $_SERVER and determines which controller and
     * function to call in order to build a Request object.
     *
     * @return Request
     * @throws NotFoundException
     */
    private static function createRequest() {
        $apiAsUrl = $_SERVER['REQUEST_URI'];
        // Spliting only by '/' results in URIs with parameters like this:
        //      /api/problem/list/?page=1
        //                       ^^
        // Adding '?' as a separator results in URIs like this:
        //      /api/problem/list?page=1
        //                       ^
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
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', Time::get()) . ' GMT');
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

    /**
     * Handles an exception by displaying an error to the end user and
     * terminates the request.
     *
     * @param Exception $e the thrown exception.
     */
    public static function handleException(Exception $e) : void {
        $apiException = null;
        if ($e instanceof \OmegaUp\Exceptions\ApiException) {
            $apiException = $e;
        } else {
            $apiException = new InternalServerErrorException($e);
        }

        if ($apiException->getCode() == 401) {
            ApiCaller::$log->info("{$apiException}");
            header('Location: /login/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            die();
        }
        if ($apiException->getCode() == 403) {
            ApiCaller::$log->info("{$apiException}");
            // Even though this is forbidden, we pretend the resource did not
            // exist.
            header('HTTP/1.1 404 Not Found');
            die(file_get_contents(__DIR__ . '/../404.html'));
        }
        if ($apiException->getcode() == 404) {
            ApiCaller::$log->info("{$apiException}");
            header('HTTP/1.1 404 Not Found');
            die(file_get_contents(__DIR__ . '/../404.html'));
        }
        ApiCaller::$log->error("{$apiException}");
        if (extension_loaded('newrelic') && $apiException->getCode() == 500) {
            newrelic_notice_error($apiException);
        }
        header('HTTP/1.1 500 Internal Server Error');
        die(file_get_contents(__DIR__ . '/../500.html'));
    }
}

ApiCaller::$log = Logger::getLogger('ApiCaller');
