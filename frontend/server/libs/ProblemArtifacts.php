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

    public function get($path) {
        return $this->git->get(['cat-file', 'blob', 'HEAD:' . $path]);
    }

    public function exists($path) {
        try {
            $this->git->get(['cat-file', '-e', 'HEAD:' . $path]);
            return true;
        } catch (Exception $e) {
            // This is expected to fail quite often.
            return false;
        }
    }

    public function lsTree($path) {
        return explode('\n', $this->git->get([
            'ls-tree', '--name-only', 'HEAD:' . $path
        ]));
    }
}

class WorkingDirProblemArtifacts extends ProblemArtifacts {
    public function __construct($path) {
        $this->log = Logger::getLogger('WorkingDirProblemDeployer');
        $this->path = $path;
    }

    public function get($path) {
        return file_get_contents("{$this->path}/$path");
    }

    public function exists($path) {
        return file_exists("{$this->path}/$path");
    }

    public function lsTree($path) {
        $handle = opendir("{$this->path}/$path");
        if ($handle === false) {
            return [];
        }
        $result = [];
        while (($entry = readdir($handle)) !== false) {
            $result[] = $entry;
        }
        closedir($handle);
        return $result;
    }
}
