<?php

/**
 * Class to abstract access to a problem's artifacts.
 *
 * @author lhchavez
 */
class ProblemArtifacts {
    public function __construct($alias) {
        $this->log = Logger::getLogger('ProblemDeployer');
        $this->git = new Git(PROBLEMS_GIT_PATH . DIRECTORY_SEPARATOR . $alias);
    }

    public function get($path, $quiet = false) {
        return $this->git->get(
            ['cat-file', 'blob', 'HEAD:' . $path],
            null /* $cwd_override */,
            $quiet
        );
    }

    public function exists($path) {
        try {
            $this->git->get(
                ['cat-file', '-e', 'HEAD:' . $path],
                null /* cwd_override */,
                true /* quiet */
            );
            return true;
        } catch (Exception $e) {
            // This is expected to fail quite often.
            return false;
        }
    }

    public function lsTree($path) {
        $entries = explode("\0", trim($this->git->get(
            ['ls-tree', '-z', 'HEAD:' . $path],
            null /* cwd_override */,
            true /* quiet */
        )));
        $result = [];
        foreach ($entries as $entry) {
            if (preg_match('/^([^ ]+) ([^ ]+) ([^\t]+)\t(.*)$/', $entry, $matches) !== 1) {
                continue;
            }
            $result[] = [
                'mode' => $matches[1],
                'type' => $matches[2],
                'object' => $matches[3],
                'name' => $matches[4],
            ];
        }
        return $result;
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
                'object' => $objectId,
                'name' => $entry,
            ];
        }
        closedir($handle);
        return $result;
    }
}
