<?php

namespace OmegaUp;

/**
 * A minimalistic database access layer that has an interface mostly compatible
 * with ADOdb.
 */
class MySQLConnection {
    /**
     * The MySQLi connection.
     * @var \mysqli
     */
    private $_connection;

    /**
     * The number of nested transactions that are currently active.
     * @var int
     */
    private $_transactionCount = 0;

    /**
     * Whether the current transaction will be committed (or rolled back) once
     * the transaction is marked as completed.
     * @var bool
     */
    private $_transactionOk = true;

    /**
     * Whether there are uncommitted queries.
     * @var bool
     */
    private $_needsFlushing = false;

    /**
     * The singleton instance of this class.
     * @var null|MySQLConnection
     */
    private static $_instance = null;

    /**
     * Returns the singleton instance of this class. It also registers a
     * shutdown function to flush any outstanding queries upon script
     * termination.
     */
    public static function getInstance() : MySQLConnection {
        if (is_null(self::$_instance)) {
            self::$_instance = new MySQLConnection();

            register_shutdown_function(function () {
                if (is_null(self::$_instance)) {
                    return;
                }
                self::$_instance->Flush();
                self::$_instance = null;
            });
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->_connect();
    }

    private function _connect() : void {
        $this->_connection = mysqli_init();
        $this->_connection->options(MYSQLI_READ_DEFAULT_GROUP, false);
        $this->_connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);

        if (!$this->_connection->real_connect(
            'p:' . OMEGAUP_DB_HOST,
            OMEGAUP_DB_USER,
            OMEGAUP_DB_PASS,
            OMEGAUP_DB_NAME
        )) {
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                'Failed to connect to MySQL (' . mysqli_connect_errno() . '): '
                . mysqli_connect_error(),
                mysqli_connect_errno()
            );
        }
        $this->_connection->autocommit(false);
        $this->_connection->set_charset('utf8');
        $this->_connection->query('SET NAMES "utf8";', MYSQLI_STORE_RESULT);
    }

    /**
     * Prepares to serialize the database connection. The database connection
     * cannot really be serialized, so we need to just flush any outstanding
     * queries.
     *
     * @return string[] The list of variables that will be serialized.
     */
    public function __sleep() : array {
        $this->Flush();
        return [];
    }

    /**
     * Deserializes the database connection. Just connects to the database again.
     */
    public function __wakeup() : void {
        $this->_connect();
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
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                'Mismatched number of parameters. Expected '
                        . (count($inputChunks) - 1) . ', got ' . count($params),
                0
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
                $chunks[] = "'" . $this->_connection->real_escape_string(strval($params[$i])) . "'";
            }
            $chunks[] = $inputChunks[$i + 1];
        }

        return implode('', $chunks);
    }

    /**
     * Executes a MySQL query.
     */
    private function Query(string $sql, array $params, int $resultmode) : ?\mysqli_result {
        /** @var \mysqli_result|bool */
        $result = $this->_connection->query($this->BindQueryParams($sql, $params), $resultmode);
        if ($result === false) {
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                "Failed to query MySQL ({$this->_connection->errno}): {$this->_connection->error}",
                intval($this->_connection->errno)
            );
        } elseif ($result === true) {
            return null;
        }
        /** @var \mysqli_result */
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
     *
     * @return mixed[]|null
     *
     * @psalm-return array<string, mixed>|null
     */
    public function GetRow(string $sql, array $params = []) : ?array {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        if (is_null($result)) {
            return null;
        }
        try {
            /** @var array<string, mixed> */
            return $result->fetch_assoc();
        } finally {
            $result->free();
        }
    }

    /**
     * Executes a MySQL query and returns all rows as associative arrays.
     *
     * @return array{string: mixed}[]
     *
     * @psalm-return array<int, array<string, mixed>>
     */
    public function GetAll(string $sql, array $params = []) : array {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        if (is_null($result)) {
            return [];
        }
        try {
            /** @var array<int, array<string, mixed>> */
            $resultArray = [];
            /** @var array<string, mixed> $row */
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
     *
     * @return mixed|null
     */
    public function GetOne(string $sql, array $params = []) {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        if (is_null($result)) {
            return null;
        }
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
        /** @var int */
        return $this->_connection->affected_rows;
    }

    /**
     * Returns the last AUTO_INCREMENT ID that was inserted.
     */
    public function Insert_ID() : int {
        /** @var int */
        return $this->_connection->insert_id;
    }

    /**
     * Returns the provided string escaped in a way that can be used in a query
     * without having SQL injections.
     */
    public function Escape(string $s) : string {
        return $this->_connection->real_escape_string($s);
    }

    /**
     * Starts a transaction.
     */
    public function StartTrans() : void {
        if (++$this->_transactionCount > 1) {
            return;
        }
        $this->Execute('BEGIN;');
        $this->_transactionOk = true;
    }

    /**
     * Marks the transaction as complete and commits it to the database. Will
     * roll it back if FailTrans() was called.
     */
    public function CompleteTrans() : void {
        if ($this->_transactionCount <= 0) {
            throw new \OmegaUp\Exceptions\DatabaseOperationException('Called FailTrans() outside of a transaction', 0);
        }
        if (--$this->_transactionCount > 0) {
            return;
        }
        if ($this->_transactionOk) {
            $this->Execute('COMMIT;');
        } else {
            $this->Execute('ROLLBACK;');
        }
        $this->_needsFlushing = false;
    }

    /**
     * Marks the transaction as failed. When CompleteTrans() is called, it will
     * be rolled back.
     */
    public function FailTrans() : void {
        if ($this->_transactionCount <= 0) {
            throw new \OmegaUp\Exceptions\DatabaseOperationException('Called FailTrans() outside of a transaction', 0);
        }
        $this->_transactionOk = false;
    }
}
