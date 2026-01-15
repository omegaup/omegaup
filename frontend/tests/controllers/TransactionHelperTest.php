<?php

namespace OmegaUp\Test\Controllers;

use OmegaUp\TransactionHelper;
use OmegaUp\Exceptions\DatabaseOperationException;

class TransactionHelperTest extends \OmegaUp\Test\ControllerTestCase {
    public function testExecuteWithRetrySuccess(): void {
        $result = TransactionHelper::executeWithRetry(function () {
            // Simulate a successful transaction
            \OmegaUp\DAO\DAO::transBegin();
            \OmegaUp\DAO\DAO::transEnd();
            return 'success';
        });

        $this->assertSame('success', $result);
    }

    public function testExecuteWithRetryDeadlock(): void {
        $this->expectException(DatabaseOperationException::class);
        $this->expectExceptionMessage('Deadlock found when trying to get lock');

        $retryCount = 0;
        TransactionHelper::executeWithRetry(function () use (&$retryCount) {
            \OmegaUp\DAO\DAO::transBegin();
            $retryCount++;
            \OmegaUp\DAO\DAO::transRollback();

            // Simulate a deadlock error
            throw new DatabaseOperationException(
                'Deadlock found when trying to get lock',
                1213 // MySQL deadlock error code
            );
        }, maxRetries: 2);

        $this->assertSame(2, $retryCount);
    }

    public function testExecuteWithRetryNonDeadlockException(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Non-deadlock exception');

        TransactionHelper::executeWithRetry(function () {
            \OmegaUp\DAO\DAO::transBegin();
            \OmegaUp\DAO\DAO::transRollback();

            // Simulate a non-deadlock exception
            throw new \Exception('Non-deadlock exception');
        });
    }
}
