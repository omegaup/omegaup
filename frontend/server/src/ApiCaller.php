<?php

namespace OmegaUp;

/**
 * Encapsulates calls to the API and provides initialization and
 * error handling (try catch) logic for logging and alerting
 *
 */
class ApiCaller {
    /** @var \Monolog\Logger */
    public static $log;

    /**
     * Execute the request and return the response as associative
     * array.
     *
     * @param \OmegaUp\Request $request
     * @return array<int, mixed>|array<string, mixed>
     */
    public static function call(\OmegaUp\Request $request): array {
        try {
            if (self::isCSRFAttempt()) {
                throw new \OmegaUp\Exceptions\CSRFException();
            }
            $response = $request->execute();
            if (
                self::isAssociativeArray($response) &&
                !isset($response['status'])
            ) {
                $response['status'] = 'ok';
            }
            \OmegaUp\Metrics::getInstance()->apiStatus(
                strval($request->methodName),
                200
            );
            return $response;
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // The controller has explicitly requested to exit.
            exit;
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            $apiException = $e;
        } catch (\Exception $e) {
            $apiException = new \OmegaUp\Exceptions\InternalServerErrorException(
                'generalError',
                $e
            );
        }

        self::logException($apiException);
        \OmegaUp\Metrics::getInstance()->apiStatus(
            strval($request->methodName),
            intval($apiException->getCode())
        );
        /** @var array<string, mixed> */
        return $apiException->asResponseArray();
    }

    /**
     * Detects CSRF attempts.
     *
     * @return bool whether this was a CSRF attempt.
     */
    private static function isCSRFAttempt(): bool {
        $httpReferer = \OmegaUp\Request::getServerVar('HTTP_REFERER');
        if (empty($httpReferer)) {
            // This API request was explicitly created.
            return false;
        }
        $referrerHost = parse_url($httpReferer, PHP_URL_HOST);
        if (!is_string($referrerHost)) {
            // Malformed referrer. Fail closed and prefer to not allow this.
            self::$log->error(
                "CSRF attempt, no referrer found in '{$httpReferer}'"
            );
            return true;
        }
        $omegaUpURLHost = parse_url(OMEGAUP_URL, PHP_URL_HOST);
        if (!is_string($omegaUpURLHost)) {
            // Malformed OMEGAUP_URL. Fail closed and prefer to not allow this.
            self::$log->error(
                "CSRF attempt, invalid OMEGAUP_URL '" . OMEGAUP_URL . "'"
            );
            return true;
        }
        // Instead of attempting to exactly match the whole URL, just ensure
        // the host is the same. Otherwise this would break tests and local
        // development environments.
        $allowedHosts = [
            $omegaUpURLHost,
            OMEGAUP_LOCKDOWN_DOMAIN,
            ...OMEGAUP_CSRF_HOSTS,
        ];
        if (!in_array($referrerHost, $allowedHosts, strict: true)) {
            self::$log->error(
                "CSRF attempt, referrer host '{$referrerHost}' not in " .
                json_encode($allowedHosts)
            );
            return true;
        }

        return false;
    }

    /**
     * Handles main API workflow. All HTTP API calls start here.
     */
    public static function httpEntryPoint(): string {
        try {
            $r = self::createRequest();
            $response = self::call($r);
        } catch (\OmegaUp\Exceptions\ApiException $apiException) {
            $r = null;
            self::logException($apiException);
            $response = $apiException->asResponseArray();
        }
        return self::render($response, $r);
    }

    /**
     * Determines whether the array is associative or packed.
     *
     * @param array<int, mixed>|array<string, mixed> $array the input array.
     *
     * @return bool whether the array is associative.
     */
    private static function isAssociativeArray(array $array): bool {
        $i = 0;
        /** @var mixed $_ */
        foreach ($array as $key => $_) {
            if ($key !== $i++) {
                return true;
            }
        }
        return false;
    }

    /**
     * Renders the response properly and sets the HTTP header.
     *
     * @param array<string, mixed>|array<int, mixed> $response
     * @param \OmegaUp\Request $r
     */
    private static function render(
        array $response,
        ?\OmegaUp\Request $r = null
    ): string {
        // Only add the request ID if the response is an associative array. This
        // allows the APIs that return a flat array to return the right type.
        if (self::isAssociativeArray($response)) {
            $response['_id'] = \OmegaUp\Request::requestId();
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
            self::$log->warning(
                'json_encode failed for: ' . print_r(
                    $response,
                    true
                )
            );
            if (json_last_error() == JSON_ERROR_UTF8) {
                // Attempt to recover gracefully, removing any unencodeable
                // elements from the response. This should at least prevent
                // completely and premanently breaking some scenarios, like
                // trying to fix a problem with illegal UTF-8 codepoints.
                $jsonResult = json_encode(
                    $response,
                    $jsonEncodeFlags | JSON_PARTIAL_OUTPUT_ON_ERROR
                );
            }
            if ($jsonResult === false) {
                $apiException = new \OmegaUp\Exceptions\InternalServerErrorException();
                self::logException($apiException);
                $jsonResult = json_encode($apiException->asResponseArray());
            }
        }
        return $jsonResult;
    }

    /**
     * Returns whether the API method is state-changing (requires POST, not GET).
     * Method name is lowercased and matched by substring against $mutatingPatterns;
     * if a read-only method's name matches (e.g. listAssociatedIdentities has "associate"),
     * add its lowercase name to $readOnlyAllowlist so GET is still allowed.
     *
     * @param string $methodName The method name (e.g. "Update", "Create").
     * @return bool True if the method is considered mutating.
     */
    private static function isMutatingMethod(string $methodName): bool {
        $lower = strtolower($methodName);

        $readOnlyAllowlist = [
            'listassociatedidentities',
            'statusverified',
        ];
        if (in_array($lower, $readOnlyAllowlist, true)) {
            return false;
        }

        $mutatingPatterns = [
            'add', 'arbitrate', 'archive', 'associate', 'bulkcreate',
            'change', 'confirm', 'create', 'delete', 'disqualify',
            'execute', 'expire', 'forfeit', 'generate', 'invalidate',
            'login', 'logout', 'read', 'refresh', 'register',
            'rejudge', 'remove', 'requalify', 'resolve', 'revoke',
            'select', 'set', 'toggle', 'update', 'verify',
        ];
        foreach ($mutatingPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parses the URI from $_SERVER and determines which controller and
     * function to call in order to build a Request object.
     *
     * @return \OmegaUp\Request
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\MethodNotAllowedException
     */
    private static function createRequest() {
        $apiAsUrl = \OmegaUp\Request::getServerVar('REQUEST_URI') ?? '/';
        // Splitting only by '/' results in URIs with parameters like this:
        //      /api/problem/list/?page=1
        //                       ^^
        // Adding '?' as a separator results in URIs like this:
        //      /api/problem/list?page=1
        //                       ^
        $args = preg_split('/[\/?]/', $apiAsUrl);

        if ($args === false || count($args) < 4) {
            self::$log->error(
                'Api called with URI with less args than expected: ' . count(
                    $args
                )
            );
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }

        $controllerName = ucfirst($args[2]);

        // Removing NULL bytes
        $controllerName = str_replace(chr(0), '', $controllerName);
        $methodName = str_replace(chr(0), '', $args[3]);

        $controllerFqdn = "\\OmegaUp\\Controllers\\{$controllerName}";

        if (!class_exists($controllerFqdn)) {
            self::$log->error(
                "Controller name was not found: {$controllerFqdn}"
            );
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }

        // Create request
        $request = new \OmegaUp\Request($_REQUEST);

        // Prepend api
        $apiMethodName = "api{$methodName}";

        // Check the method
        if (!method_exists($controllerFqdn, $apiMethodName)) {
            self::$log->error(
                "Method name was not found: {$controllerFqdn}::{$apiMethodName}"
            );
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }

        // Reject GET for mutating endpoints
        $requestMethod = \OmegaUp\Request::getServerVar('REQUEST_METHOD');
        if (
            !is_null($requestMethod) &&
            strtoupper($requestMethod) === 'GET' &&
            self::isMutatingMethod($methodName)
        ) {
            throw new \OmegaUp\Exceptions\MethodNotAllowedException(
                'methodNotAllowed'
            );
        }

        // Get the auth_token and user data from cookies
        $session = \OmegaUp\Controllers\Session::getCurrentSession();

        // If we got an auth_token from cookies, replace it
        if (!empty($session['auth_token'])) {
            $request['auth_token'] = $session['auth_token'];
        }

        for ($i = 4; ($i + 1) < sizeof($args); $i += 2) {
            $request[$args[$i]] = urldecode($args[$i + 1]);
        }

        $request->methodName = strtolower("{$controllerName}.{$methodName}");
        /** @var callable-string */
        $request->method = "{$controllerFqdn}::{$apiMethodName}";

        return $request;
    }

    /**
     * Sets all required headers for the API called via HTTP
     *
     * @param array<string, mixed>|array<int, mixed> $response
     */
    private static function setHttpHeaders(array $response): void {
        // Scumbag IE y su cache agresivo.
        header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
        header(
            'Last-Modified: ' . gmdate(
                'D, d M Y H:i:s',
                \OmegaUp\Time::get()
            ) . ' GMT'
        );
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('X-Robots-Tag: noindex');

        // Set header accordingly
        if (isset($response['header']) && is_string($response['header'])) {
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
     * @param \Exception $e the thrown exception.
     * @return no-return
     */
    public static function handleException(\Exception $e): void {
        $apiException = null;
        if ($e instanceof \OmegaUp\Exceptions\ApiException) {
            $apiException = $e;
        } else {
            $apiException = new \OmegaUp\Exceptions\InternalServerErrorException(
                'generalError',
                $e
            );
        }

        self::logException($apiException);

        if ($apiException->getcode() == 400) {
            header('HTTP/1.1 400 Bad Request');
            /** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
            die(
                file_get_contents(
                    sprintf('%s/www/400.html', strval(OMEGAUP_ROOT))
                )
            );
        }
        if ($apiException->getCode() == 401) {
            $uri = \OmegaUp\Request::getServerVar('REQUEST_URI') ?? '/';
            $newURI = str_replace('startfresh/', '', $uri);
            header('Location: /login/?redirect=' . urlencode($newURI));
            die();
        }
        if ($apiException->getCode() == 403) {
            // Even though this is forbidden, we pretend the resource did not
            // exist.
            header('HTTP/1.1 404 Not Found');
            /** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
            die(
                file_get_contents(
                    sprintf('%s/www/404.html', strval(OMEGAUP_ROOT))
                )
            );
        }
        if ($apiException->getcode() == 404) {
            header('HTTP/1.1 404 Not Found');
            /** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
            die(
                file_get_contents(
                    sprintf('%s/www/404.html', strval(OMEGAUP_ROOT))
                )
            );
        }
        header('HTTP/1.1 500 Internal Server Error');
        /** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
        die(
            file_get_contents(
                sprintf('%s/www/500.html', strval(OMEGAUP_ROOT))
            )
        );
    }

    public static function logException(
        \OmegaUp\Exceptions\ApiException $apiException
    ): void {
        $stringifiedException = strval($apiException);
        if ($apiException->getCode() >= 500 && $apiException->getCode() < 600) {
            self::$log->error($stringifiedException);
            \OmegaUp\NewRelicHelper::noticeError($stringifiedException);
        } else {
            self::$log->info($stringifiedException);
        }
    }
}

\OmegaUp\ApiCaller::$log = \Monolog\Registry::omegaup()->withName('ApiCaller');
