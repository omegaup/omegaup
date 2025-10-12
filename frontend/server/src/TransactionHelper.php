<?php

namespace OmegaUp;

/**
 * Helper class for MySQL transactions with deadlock retry logic
 */
class TransactionHelper {
    /**
     * Execute a function within a MySQL transaction with automatic deadlock retry
     *
     * @param callable $transactionFunction The function to execute within transaction
     * @param int $maxRetries Maximum number of retry attempts (default: 3)
     * @return mixed The result of the transaction function
     * @throws \Exception The last exception if all retries fail
     */
    public static function executeWithRetry(
        callable $transactionFunction,
        int $maxRetries = 3
    ) {
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $maxRetries) {
            try {
                \OmegaUp\DAO\DAO::transBegin();
                $result = $transactionFunction();
                \OmegaUp\DAO\DAO::transEnd();

                // Transaction succeeded, return result
                return $result;
            } catch (\OmegaUp\Exceptions\DatabaseOperationException $e) {
                \OmegaUp\DAO\DAO::transRollback();
                $retryCount++;
                $lastException = $e;

                // Only retry for deadlock errors
                if (!$e->isDeadlock() || $retryCount >= $maxRetries) {
                    throw $e;
                }

                // Exponential backoff with jitter for deadlock retries
                $waitTime = max(
                    value: 0,
                    values: intval(
                        value: min(pow(2, $retryCount - 1) * 100000, 1000000)
                    )
                );
                $jitter = rand(0, 50000); // Add up to 50ms jitter
                usleep($waitTime + $jitter);

                // Log the retry attempt if NewRelic is available
                if (extension_loaded('newrelic')) {
                    \newrelic_notice_error(
                        "MySQL deadlock retry attempt {$retryCount}/{$maxRetries}: " . $e->getMessage(),
                        $e
                    );
                }
            } catch (\Exception $e) {
                \OmegaUp\DAO\DAO::transRollback();
                throw $e;
            }
        }

        // This should never be reached, but just in case
        throw new \RuntimeException(
            'Maximum retry attempts exceeded for transaction',
            0,
            $lastException
        );
    }
}
