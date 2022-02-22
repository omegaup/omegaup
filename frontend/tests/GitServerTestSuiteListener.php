<?php

namespace OmegaUp\Test;

class GitServerTestSuiteListener implements \PHPUnit\Framework\TestListener {
    public function addWarning(
        \PHPUnit\Framework\Test $test,
        \Throwable $e,
        $time
    ): void {
    }

    public function addError(
        \PHPUnit\Framework\Test $test,
        \Throwable $e,
        $time
    ): void {
    }

    public function addFailure(
        \PHPUnit\Framework\Test $test,
        \PHPUnit\Framework\AssertionFailedError $e,
        $time
    ): void {
    }

    public function addIncompleteTest(
        \PHPUnit\Framework\Test $test,
        \Throwable $e,
        $time
    ): void {
    }

    public function addRiskyTest(
        \PHPUnit\Framework\Test $test,
        \Throwable $e,
        $time
    ): void {
    }

    public function addSkippedTest(
        \PHPUnit\Framework\Test $test,
        \Throwable $e,
        $time
    ): void {
    }

    public function startTest(\PHPUnit\Framework\Test $test): void {
    }

    public function endTest(\PHPUnit\Framework\Test $test, $time): void {
    }

    private $openSuiteCount = 0;
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void {
      /**
       * @psalm-suppress UndefinedConstant OMEGAUP_TEST_SHARD is only
       * defined in the test bootstrap.php file
       */
        $this->openSuiteCount += 1;
        if ($this->openSuiteCount == 1) {
            echo('Starting it up! ' . $suite->getName());
            $scriptFilename = __DIR__ . '/controllers/gitserver-start.sh ' .
            OMEGAUP_GITSERVER_PORT . ' ' . OMEGAUP_TEST_ROOT .
            ' /tmp/omegaup/problems-' . OMEGAUP_TEST_SHARD . '.git';
            exec($scriptFilename, $output, $returnVar);
            if ($returnVar != 0) {
                throw new \Throwable(
                    "{$scriptFilename} failed with {$returnVar}:\n" .
                    implode("\n", $output)
                );
            }
        }
    }

    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void {
        $this->openSuiteCount -= 1;
        if ($this->openSuiteCount == 0) {
            echo('Shutting it down! ' . $suite->getName());
            $scriptFilename = __DIR__ . '/controllers/gitserver-stop.sh ' . OMEGAUP_TEST_ROOT;
            exec($scriptFilename, $output, $returnVar);
            if ($returnVar != 0) {
                throw new \Throwable(
                    "{$scriptFilename} failed with {$returnVar}:\n" .
                    implode("\n", $output)
                );
            }
        }
    }
}
