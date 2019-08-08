<?php
/**
 * A minimalistic database access layer that has an interface mostly compatible
 * with ADOdb.
 */
class MySQLConnection {
    /**
     * The MySQLi connection.
     */
    private $_connection = null;

    /**
     * The number of nested transactions that are currently active.
     */
    private $_transactionCount = 0;

    /**
     * Whether the current transaction will be committed (or rolled back) once
     * the transaction is marked as completed.
     */
    private $_transactionOk = true;

    /**
     * Whether there are uncommitted queries.
     */
    private $_needsFlushing = false;

    public function __construct(
        string $hostname,
        string $username,
        string $password,
        string $databaseName
    ) {
        $this->_connection = @mysqli_init();
        if (is_null($this->_connection)) {
            throw new DatabaseOperationException('Failed to initialize MySQLi connection');
        }
        $this->_connection->options(MYSQLI_READ_DEFAULT_GROUP, false);
        $this->_connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);

        if (!$this->_connection->real_connect(
            "p:{$hostname}",
            $username,
            $password,
            $databaseName
        )) {
            throw new DatabaseOperationException(
                'Failed to connect to MySQL (' . mysqli_connect_errno() . '): '
                . mysqli_connect_error()
            );
        }
        $this->_connection->autocommit(false);
        $this->_connection->set_charset('utf8');
        $this->_connection->query('SET NAMES "utf8";', MYSQLI_STORE_RESULT);

        // Even though the connection's destructor will call Flush(), this
        // ensures that it is also called if die() is invoked.
        register_shutdown_function(function () {
            $this->Flush();
        });
    }

    public function __destruct() {
        $this->Flush();
    }

    /**
     * Commits any outstanding transactions.
     */
    private function Flush() : void {
        if ($this->_transactionCount > 0) {
            $this->_transactionCount = 1;
            $this->CompleteTrans();
        }
        // Even if there was no transaction, given that we disable autocommit,
        // there is an implicit transaction per connection that neeeds to be
        // explicitly committed. But we only do so if there have been any calls
        // to Execute().
        if (!$this->_needsFlushing) {
            return;
        }
        $this->_connection->query('COMMIT;', MYSQLI_STORE_RESULT);
        $this->_needsFlushing = false;
    }

    /**
     * Binds the query parameters and returns a query that can be passed into
     * mysqli_query.
     */
    private function BindQueryParams(string $sql, array $params) : string {
        if (empty($params)) {
            return $sql;
        }

        $inputChunks = explode('?', $sql);
        if (count($params) != count($inputChunks) - 1) {
            throw new DatabaseOperationException(
                'Mismatched number of parameters. Expected '
                        . (count($inputChunks) - 1) . ', got ' . count($params)
            );
        }

        $chunks = [$inputChunks[0]];
        for ($i = 0; $i < count($params); ++$i) {
            if (is_null($params[$i])) {
                $chunks[] = 'NULL';
            } elseif (is_int($params[$i]) || is_float($params[$i])) {
                $chunks[] = $params[$i];
            } elseif (is_bool($params[$i])) {
                $chunks[] = $params[$i] ? '1' : '0';
            } else {
                $chunks[] = "'" . $this->_connection->real_escape_string((string) $params[$i]) . "'";
            }
            $chunks[] = $inputChunks[$i + 1];
        }

        return implode('', $chunks);
    }

    /**
     * Executes a MySQL query.
     */
    private function Query(string $sql, array $params, int $resultmode) : ?mysqli_result {
        $result = $this->_connection->query($this->BindQueryParams($sql, $params), $resultmode);
        if ($result === false) {
            throw new DatabaseOperationException(
                "Failed to query MySQL ({$this->_connection->errno}): {$this->_connection->error}",
                $this->_connection->errno
            );
        } elseif ($result === true) {
            return null;
        }
        return $result;
    }

    /**
     * Executes a MySQL query.
     */
    public function Execute(string $sql, array $params = []) : void {
        $this->Query($sql, $params, MYSQLI_STORE_RESULT);
        $this->_needsFlushing = true;
    }

    /**
     * Executes a MySQL query and returns the first row as an associative array.
     */
    public function GetRow(string $sql, array $params = []) : ?array {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        try {
            return $result->fetch_assoc();
        } finally {
            $result->free();
        }
    }

    /**
     * Executes a MySQL query and returns all rows as associative arrays.
     */
    public function GetAll(string $sql, array $params = []) : ?array {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        try {
            $resultArray = [];
            while (!is_null($row = $result->fetch_assoc())) {
                $resultArray[] = $row;
            }
            return $resultArray;
        } finally {
            $result->free();
        }
    }

    /**
     * Executes a MySQL query and returns the first field of the first row.
     */
    public function GetOne(string $sql, array $params = []) {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        try {
            $row = $result->fetch_row();
            if (empty($row)) {
                return null;
            }
            return $row[0];
        } finally {
            $result->free();
        }
    }

    /**
     * Returns the number of rows affected by the previous query.
     */
    public function Affected_Rows() : int {
        return $this->_connection->affected_rows;
    }

    /**
     * Returns the last AUTO_INCREMENT ID that was inserted.
     */
    public function Insert_ID() : int {
        return $this->_connection->insert_id;
    }

    /**
     * Returns the provided string escaped in a way that can be used in a query
     * without having iSQL injections.
     */
    public function Escape(string $s) : string {
        return $this->_connection->real_escape_string($s);
    }

    /**
     * Starts a transaction.
     */
    public function StartTrans() : bool {
        if (++$this->_transactionCount > 1) {
            return true;
        }
        $this->Execute('BEGIN;');
        $this->_transactionOk = true;
        return true;
    }

    /**
     * Marks the transaction as complete and commits it to the database. Will
     * roll it back if FailTrans() was called.
     */
    public function CompleteTrans() : bool {
        if ($this->_transactionCount <= 0) {
            throw new DatabaseOperationException('Called FailTrans() outside of a transaction');
        }
        if (--$this->_transactionCount > 0) {
            return true;
        }
        if ($this->_transactionOk) {
            $this->Execute('COMMIT;');
        } else {
            $this->Execute('ROLLBACK;');
        }
        $this->_needsFlushing = false;
        return true;
    }

    /**
     * Marks the transaction as failed. When CompleteTrans() is called, it will
     * be rolled back.
     */
    public function FailTrans() : void {
        if ($this->_transactionCount <= 0) {
            throw new DatabaseOperationException('Called FailTrans() outside of a transaction');
        }
        $this->_transactionOk = false;
    }
}
