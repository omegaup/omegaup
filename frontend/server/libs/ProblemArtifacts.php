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
        $curl = curl_init();
        try {
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => OMEGAUP_GITSERVER_URL . "/{$this->alias}/+/{$this->commit}/{$path}",
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/octet-stream',
                        SecurityTools::getGitserverAuthorizationHeader($this->alias, 'omegaup:system'),
                    ],
                ]
            );
            return curl_exec($curl);
        } finally {
            curl_close($curl);
        }
    }

    public function exists($path) {
        $curl = curl_init();
        try {
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => OMEGAUP_GITSERVER_URL . "/{$this->alias}/+/{$this->commit}/{$path}",
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        SecurityTools::getGitserverAuthorizationHeader($this->alias, 'omegaup:system'),
                    ],
                    CURLOPT_NOBODY => 1,
                ]
            );
            return curl_exec($curl) !== false && curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200;
        } finally {
            curl_close($curl);
        }
    }

    public function lsTree($path) {
        $curl = curl_init();
        try {
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL => OMEGAUP_GITSERVER_URL . "/{$this->alias}/+/{$this->commit}/{$path}/",
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        SecurityTools::getGitserverAuthorizationHeader($this->alias, 'omegaup:system'),
                    ],
                ]
            );
            $response = json_decode(curl_exec($curl), JSON_OBJECT_AS_ARRAY);
            return $response['entries'];
        } finally {
            curl_close($curl);
        }
    }
}

class WorkingDirProblemArtifacts extends ProblemArtifacts {
    public function __construct($path) {
        $this->log = Logger::getLogger('WorkingDirProblemDeployer');
        $this->path = $path;
    }

    public function get($path, $quiet = false) {
        return file_get_contents("{$this->path}/$path");
    }

    public function exists($path) {
        return file_exists("{$this->path}/$path");
    }

    public function lsTree($path) {
        $S_IFDIR = 0x0040000;

        $handle = opendir("{$this->path}/$path");
        if ($handle === false) {
            return [];
        }
        $result = [];
        while (($entry = readdir($handle)) !== false) {
            $fullPath = "{$this->path}/$path/$entry";
            $st = lstat($fullPath);
            $isBlob = ($st['mode'] & $S_IFDIR) == 0;
            $objectId = '0000000000000000000000000000000000000000';
            if ($isBlob) {
                $header = sprintf('blob %d\0', $st['size']);
                $objectId = sha1($header . file_get_contents($fullPath));
            }
            $result[] = [
                'mode' => $st['mode'],
                'type' => ($isBlob) ? 'blob' : 'tree',
                'id' => $objectId,
                'name' => $entry,
            ];
        }
        closedir($handle);
        return $result;
    }
}
