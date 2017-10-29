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
}
