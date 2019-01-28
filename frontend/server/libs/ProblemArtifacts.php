<?php

/**
 * Class to abstract access to a problem's artifacts.
 *
 * @author lhchavez
 */
class ProblemArtifacts {
    public function __construct(string $alias, string $commit = 'HEAD') {
        $this->log = Logger::getLogger('ProblemArtifacts');
        $this->alias = $alias;
        $this->commit = $commit;
    }

    public function get($path, $quiet = false) {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->commit, $path)
        );
        $browser->headers[] = 'Accept: application/octet-stream';
        return $browser->exec();
    }

    public function exists($path) {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->commit, $path)
        );
        $browser->headers[] = 'Accept: application/json';
        curl_setopt($browser->curl, CURLOPT_NOBODY, 1);
        return $browser->exec() !== false && curl_getinfo($browser->curl, CURLINFO_HTTP_CODE) == 200;
    }

    public function lsTree($path) {
        $browser = new GitServerBrowser(
            $this->alias,
            GitServerBrowser::buildShowURL($this->alias, $this->commit, "{$path}/")
        );
        $browser->headers[] = 'Accept: application/json';
        $response = json_decode($browser->exec(), JSON_OBJECT_AS_ARRAY);
        return $response['entries'];
    }

    public function download() {
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
    public $curl = null;
    public $headers = [];

    public function __construct(string $alias, string $url, bool $passthru = false) {
        $this->curl = curl_init();
        $this->headers = [
            SecurityTools::getGitserverAuthorizationHeader($alias, 'omegaup:system'),
        ];
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => !$passthru,
            ]
        );
    }

    public static function buildShowURL(
        string $alias,
        string $commit,
        string $path
    ) {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+/{$commit}/{$path}";
    }

    public static function buildArchiveURL(
        string $alias,
        string $commit
    ) {
        return OMEGAUP_GITSERVER_URL . "/{$alias}/+archive/{$commit}.zip";
    }

    public function __destruct() {
        curl_close($this->curl);
    }

    public function exec() {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        return curl_exec($this->curl);
    }
}
