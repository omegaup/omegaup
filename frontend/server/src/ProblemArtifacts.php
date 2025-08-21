<?php

namespace OmegaUp;

/**
 * Class to abstract access to a problem's artifacts.
 */
class ProblemArtifacts {
    /** @var \Monolog\Logger */
    private $log;

    /** @var string */
    private $alias;

    /** @var string */
    private $revision;

    public function __construct(string $alias, string $revision = 'published') {
        $this->log = \Monolog\Registry::omegaup()->withName('ProblemArtifacts');
        $this->alias = $alias;
        $this->revision = $revision;
    }

    public function get(string $path): string {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->revision, $path)
        );
        $browser->headers[] = 'Accept: application/octet-stream';
        $response = $browser->exec();
        /** @var int */
        $httpStatusCode = curl_getinfo($browser->curl, CURLINFO_HTTP_CODE);
        if ($httpStatusCode != 200) {
            $this->log->error(
                "Failed to get contents for {$this->alias}:{$this->revision}/{$path}. " .
                "HTTP {$httpStatusCode}: \"{$response}\""
            );
            if ($httpStatusCode != 403 && $httpStatusCode != 404) {
                throw new \OmegaUp\Exceptions\ServiceUnavailableException();
            }
            throw new \OmegaUp\Exceptions\NotFoundException(
                'resourceNotFound'
            );
        }
        return $response;
    }

    public function exists(string $path): bool {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->revision, $path)
        );
        $browser->headers[] = 'Accept: application/json';
        curl_setopt($browser->curl, CURLOPT_NOBODY, 1);
        $response = $browser->exec();
        /** @var int */
        $httpStatusCode = curl_getinfo($browser->curl, CURLINFO_HTTP_CODE);
        if (
            $httpStatusCode != 200 &&
            $httpStatusCode != 403 &&
            $httpStatusCode != 404
        ) {
            $this->log->error(
                "Failed to get existence for {$this->alias}:{$this->revision}/{$path}. " .
                "HTTP {$httpStatusCode}: \"{$response}\""
            );
            throw new \OmegaUp\Exceptions\ServiceUnavailableException();
        }
        return $httpStatusCode == 200;
    }

    public function getByRevision(): string {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowRevisionURL(
                $this->alias,
                $this->revision
            )
        );
        $browser->headers[] = 'Accept: application/octet-stream';
        $response = $browser->exec();
        /** @var int */
        $httpStatusCode = curl_getinfo($browser->curl, CURLINFO_HTTP_CODE);
        if ($httpStatusCode != 200) {
            $this->log->error(
                "Failed to get contents for {$this->alias}:{$this->revision}. " .
                "HTTP {$httpStatusCode}: \"{$response}\""
            );
            if ($httpStatusCode != 403 && $httpStatusCode != 404) {
                throw new \OmegaUp\Exceptions\ServiceUnavailableException();
            }
            throw new \OmegaUp\Exceptions\NotFoundException(
                'resourceNotFound'
            );
        }
        return $response;
    }

    /**
     * Returns a list of tree entries.
     *
     * @param string $path The path to display.
     * @return list<array{mode: int, type: string, id: string, name: string, size: int}> The list of
     * direct entries in $path.
     */
    public function lsTree(string $path): array {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL(
                $this->alias,
                $this->revision,
                "{$path}/"
            )
        );
        $browser->headers[] = 'Accept: application/json';
        $response = $browser->exec();
        /** @var int */
        $httpStatusCode = curl_getinfo($browser->curl, CURLINFO_HTTP_CODE);
        if ($httpStatusCode != 200) {
            $this->log->error(
                "Failed to get tree entries for {$this->alias}:{$this->revision}/{$path}. " .
                "HTTP {$httpStatusCode}: \"{$response}\""
            );
            if ($httpStatusCode != 403 && $httpStatusCode != 404) {
                throw new \OmegaUp\Exceptions\ServiceUnavailableException();
            }
            return [];
        }
        /** @var null|array{id: string, entries?: null|list<array{mode: int, type: string, id: string, name: string, size: int}>} */
        $response = json_decode($response, associative: true);
        if (!is_array($response) || !array_key_exists('entries', $response)) {
            $this->log->error(
                "Failed to get entries of {$path} for problem {$this->alias} at revision {$this->revision}"
            );
            return [];
        }
        /** @var null|list<array{mode: int, type: string, id: string, name: string, size: int}> */
        $entries = $response['entries'];
        if (!is_iterable($entries)) {
            $this->log->error(
                "Invalid entries of {$path} for problem {$this->alias} at revision {$this->revision}"
            );
            return [];
        }
        return $entries;
    }

    /**
     * Returns the list of files that are transitively reachable from $path.
     *
     * @param string $path The path to display.
     * @return list<array{id: string, mode: int, path: string, size: int, type: string}> The list of files
     * that are transitively reachable from $path.
     */
    public function lsTreeRecursive(string $path = '.'): array {
        /** @var list<array{id: string, mode: int, path: string, size: int, type: string}> */
        $entries = [];
        /** @var list<string> */
        $queue = [$path];
        while (!empty($queue)) {
            $path = array_shift($queue);
            foreach (self::lsTree($path) as $entry) {
                if ($path == '.') {
                    $entry['path'] = $entry['name'];
                } else {
                    $entry['path'] = "{$path}/{$entry['name']}";
                }
                unset($entry['name']);
                if ($entry['type'] == 'tree') {
                    array_push($queue, $entry['path']);
                    continue;
                }

                $entries[] = $entry;
            }
        }
        usort($entries, function (array $lhs, array $rhs): int {
            if ($lhs['path'] == $rhs['path']) {
                return 0;
            }
            return ($lhs['path'] < $rhs['path']) ? -1 : 1;
        });

        return $entries;
    }

    /**
     * @return null|array{commit: string, tree: string, parents: list<string>, author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string}
     */
    public function commit(): ?array {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowCommitURL($this->alias, $this->revision)
        );
        $browser->headers[] = 'Accept: application/json';
        $response = $browser->exec();
        /** @var int */
        $httpStatusCode = curl_getinfo($browser->curl, CURLINFO_HTTP_CODE);
        if ($httpStatusCode != 200) {
            $this->log->error(
                "Invalid commit for problem {$this->alias} at revision {$this->revision}. " .
                "HTTP {$httpStatusCode}: \"{$response}\""
            );
            if ($httpStatusCode != 403 && $httpStatusCode != 404) {
                throw new \OmegaUp\Exceptions\ServiceUnavailableException();
            }
            return null;
        }
        /** @var null|array{commit: string, tree: string, parents: list<string>, author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string} */
        $response = json_decode($response, associative: true);
        if (!is_array($response)) {
            $this->log->error(
                "Invalid commit for problem {$this->alias} at revision {$this->revision}"
            );
            return null;
        }
        return $response;
    }

    /**
     * @return list<array{commit: string, tree: string, parents: list<string>, author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string}>
     */
    public function log(): array {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildLogURL($this->alias, $this->revision)
        );
        $browser->headers[] = 'Accept: application/json';
        $response = $browser->exec();
        /** @var int */
        $httpStatusCode = curl_getinfo($browser->curl, CURLINFO_HTTP_CODE);
        if ($httpStatusCode != 200) {
            $this->log->error(
                "Failed to get log for problem {$this->alias} at revision {$this->revision}. " .
                "HTTP {$httpStatusCode}: \"{$response}\""
            );
            if ($httpStatusCode != 403 && $httpStatusCode != 404) {
                throw new \OmegaUp\Exceptions\ServiceUnavailableException();
            }
            return [];
        }
        /** @var null|array{log?: null|list<array{commit: string, tree: string, parents: list<string>, author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string}>, next?: string} */
        $response = json_decode($response, associative: true);
        if (!is_array($response) || !array_key_exists('log', $response)) {
            $this->log->error(
                "Failed to get log for problem {$this->alias} at revision {$this->revision}"
            );
            return [];
        }
        /** @var null|list<array{commit: string, tree: string, parents: list<string>, author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string}> */
        $logEntries = $response['log'];
        if (!is_iterable($logEntries)) {
            $this->log->error(
                "Invalid log for problem {$this->alias} at revision {$this->revision}"
            );
            return [];
        }
        return $logEntries;
    }

    public function download(): bool {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildArchiveURL($this->alias, $this->revision),
            passthru: true
        );
        $browser->headers[] = 'Accept: application/zip';
        $response = $browser->exec();
        /** @var int */
        $httpStatusCode = curl_getinfo($browser->curl, CURLINFO_HTTP_CODE);
        if (
            $httpStatusCode != 200 &&
            $httpStatusCode != 403 &&
            $httpStatusCode != 404
        ) {
            $this->log->error(
                "Failed to download {$this->alias}:{$this->revision}. " .
                "HTTP {$httpStatusCode}: \"{$response}\""
            );
            throw new \OmegaUp\Exceptions\ServiceUnavailableException();
        }
        return $httpStatusCode == 200;
    }
}

class GitServerBrowser {
    /** @var \CurlHandle */
    public $curl;

    /** @var list<string> */
    public $headers = [];

    /** @var bool */
    private $passthru;

    /**
     * @var string
     * @readonly
     */
    private $alias;

    /**
     * @var string
     * @readonly
     */
    private $url;

    public function __construct(
        string $alias,
        string $url,
        bool $passthru = false
    ) {
        $this->alias = $alias;
        $this->url = $url;
        $this->curl = curl_init();
        $this->headers = [
            \OmegaUp\SecurityTools::getGitserverAuthorizationHeader(
                $this->alias,
                'omegaup:system'
            ),
        ];
        $this->passthru = $passthru;
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => !$this->passthru,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 10,
            ]
        );
    }

    public static function buildShowURL(
        string $alias,
        string $revision,
        string $path
    ): string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+/{$revision}/{$path}";
    }

    public static function buildShowRevisionURL(
        string $alias,
        string $revision
    ): string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+/{$revision}";
    }

    public static function buildShowCommitURL(
        string $alias,
        string $revision
    ): string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+/{$revision}";
    }

    public static function buildArchiveURL(
        string $alias,
        string $revision
    ): string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+archive/{$revision}.zip";
    }

    public static function buildLogURL(
        string $alias,
        string $revision
    ): string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+log/{$revision}";
    }

    public function __destruct() {
        curl_close($this->curl);
    }

    public function exec(): string {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($this->curl);
        if (!is_string($response)) {
            $curlErrno = curl_errno($this->curl);
            $curlError = curl_error($this->curl);
            // Only log error if we're not in passthru mode to avoid sending output before headers
            if (!$this->passthru) {
                \Monolog\Registry::omegaup()->withName('GitBrowser')->error(
                    "Failed to get contents for {$this->url}. " .
                    "cURL {$curlErrno}: \"{$curlError}\""
                );
            }
            throw new \OmegaUp\Exceptions\ServiceUnavailableException();
        }
        return $response;
    }
}
