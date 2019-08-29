<?php

namespace OmegaUp;

/**
 * Class to abstract access to a problem's artifacts.
 *
 * @author lhchavez
 */
class ProblemArtifacts {
    /** @var \Logger */
    private $log;

    /** @var string */
    private $alias;

    /** @var string */
    private $commit;

    public function __construct(string $alias, string $commit = 'published') {
        $this->log = \Logger::getLogger('ProblemArtifacts');
        $this->alias = $alias;
        $this->commit = $commit;
    }

    public function get(string $path, bool $quiet = false) : string {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->commit, $path)
        );
        $browser->headers[] = 'Accept: application/octet-stream';
        /** @var string */
        return $browser->exec();
    }

    public function exists(string $path) : bool {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->commit, $path)
        );
        $browser->headers[] = 'Accept: application/json';
        curl_setopt($browser->curl, CURLOPT_NOBODY, 1);
        return $browser->exec() !== false && curl_getinfo($browser->curl, CURLINFO_HTTP_CODE) == 200;
    }

    /**
     * Returns a list of tree entries.
     *
     * @param string $path The path to display.
     * @return array{mode: int, type: string, name: string}[] The list of
     * direct entries in $path.
     */
    public function lsTree(string $path) : array {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->commit, "{$path}/")
        );
        $browser->headers[] = 'Accept: application/json';
        $response = $browser->exec();
        if (!is_string($response)) {
            $this->log->error(
                "Failed to get entries of {$path} for problem {$this->alias} at commit {$this->commit}"
            );
            return [];
        }
        /** @var null|array{id: string, entries?: null|array{mode: int, type: string, name: string}[]} */
        $response = json_decode($response, /*assoc=*/true);
        if (!is_array($response) || !array_key_exists('entries', $response)) {
            $this->log->error(
                "Failed to get entries of {$path} for problem {$this->alias} at commit {$this->commit}"
            );
            return [];
        }
        /** @var null|array{mode: int, type: string, name: string}[] */
        $entries = $response['entries'];
        if (!is_iterable($entries)) {
            $this->log->error(
                "Invalid entries of {$path} for problem {$this->alias} at commit {$this->commit}"
            );
            return [];
        }
        return $entries;
    }

    /**
     * Returns the list of files that are transitively reachable from $path.
     *
     * @param string $path The path to display.
     * @return array{path: string, mode: int, type: string}[] The list of files
     * that are transitively reachable from $path.
     */
    public function lsTreeRecursive(string $path = '.') : array {
        /** @var array{path: string, mode: int, type: string}[] */
        $entries = [];
        /** @var string[] */
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

                array_push($entries, $entry);
            }
        }
        usort($entries, function (array $lhs, array $rhs) : int {
            if ($lhs['path'] == $rhs['path']) {
                return 0;
            }
            return ($lhs['path'] < $rhs['path']) ? -1 : 1;
        });

        return $entries;
    }

    public function commit() : ?array {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowCommitURL($this->alias, $this->commit)
        );
        $browser->headers[] = 'Accept: application/json';
        $response = $browser->exec();
        if (!is_string($response)) {
            $this->log->error(
                "Invalid commit for problem {$this->alias} at commit {$this->commit}"
            );
            return null;
        }
        /** @var null|array{commit: string, tree: string, parents: string[], author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string} */
        $response = json_decode($response, /*assoc=*/true);
        if (!is_array($response)) {
            $this->log->error(
                "Invalid commit for problem {$this->alias} at commit {$this->commit}"
            );
            return null;
        }
        return $response;
    }

    /**
     * @return array{commit: string, tree: string, parents: string[], author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string}[]
     */
    public function log() : array {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildLogURL($this->alias, $this->commit)
        );
        $browser->headers[] = 'Accept: application/json';
        $response = $browser->exec();
        if (!is_string($response)) {
            $this->log->error(
                "Failed to get log for problem {$this->alias} at commit {$this->commit}"
            );
            return [];
        }
        /** @var null|array{log?: null|array{commit: string, tree: string, parents: string[], author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string}[], next?: string} */
        $response = json_decode($response, /*assoc=*/true);
        if (!is_array($response) || !array_key_exists('log', $response)) {
            $this->log->error(
                "Failed to get log for problem {$this->alias} at commit {$this->commit}"
            );
            return [];
        }
        /** @var null|array{commit: string, tree: string, parents: string[], author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string}[] */
        $logEntries = $response['log'];
        if (!is_iterable($logEntries)) {
            $this->log->error(
                "Invalid log for problem {$this->alias} at commit {$this->commit}"
            );
            return [];
        }
        return $logEntries;
    }

    public function download() : bool {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildArchiveURL($this->alias, $this->commit),
            /*passthru=*/true
        );
        $browser->headers[] = 'Accept: application/zip';
        return $browser->exec() !== false && curl_getinfo($browser->curl, CURLINFO_HTTP_CODE) == 200;
    }
}

class GitServerBrowser {
    /** @var resource */
    public $curl;

    /** @var string[] */
    public $headers = [];

    /** @var bool */
    private $passthru;

    public function __construct(string $alias, string $url, bool $passthru = false) {
        $this->curl = curl_init();
        $this->headers = [
            \OmegaUp\SecurityTools::getGitserverAuthorizationHeader($alias, 'omegaup:system'),
        ];
        $this->passthru = $passthru;
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => !$this->passthru,
            ]
        );
    }

    public static function buildShowURL(
        string $alias,
        string $commit,
        string $path
    ) : string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+/{$commit}/{$path}";
    }

    public static function buildShowCommitURL(
        string $alias,
        string $commit
    ) : string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+/{$commit}";
    }

    public static function buildArchiveURL(
        string $alias,
        string $commit
    ) : string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+archive/{$commit}.zip";
    }

    public static function buildLogURL(
        string $alias,
        string $commit
    ) : string {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+log/{$commit}";
    }

    public function __destruct() {
        curl_close($this->curl);
    }

    /**
     * @return bool|string
     */
    public function exec() {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        return curl_exec($this->curl);
    }
}
