<?php

namespace OmegaUp;

class FieldType {
    const TYPE_STRING = 0;
    const TYPE_INT = 1;
    const TYPE_FLOAT = 2;
    const TYPE_BOOL = 3;
    const TYPE_TIMESTAMP = 4;
}

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

    /** @var \Monolog\Logger|null */
    private static $_typesLogger = null;

    /**
     * Returns the singleton instance of this class. It also registers a
     * shutdown function to flush any outstanding queries upon script
     * termination.
     */
    public static function getInstance(): MySQLConnection {
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
        $this->connect();
    }

    private function connect(): void {
        $this->_connection = \mysqli_init();
        $this->_connection->options(MYSQLI_READ_DEFAULT_GROUP, 0);
        $this->_connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

        if (
            !@$this->_connection->real_connect(
                'p:' . OMEGAUP_DB_HOST,
                OMEGAUP_DB_USER,
                OMEGAUP_DB_PASS,
                OMEGAUP_DB_NAME
            )
        ) {
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                'Failed to connect to MySQL (' . \mysqli_connect_errno() . '): '
                . strval(\mysqli_connect_error()),
                \mysqli_connect_errno()
            );
        }
        $this->_connection->autocommit(false);
        $this->_connection->set_charset('utf8mb4');
        $this->_connection->query(
            'SET NAMES "utf8mb4" COLLATE "utf8mb4_unicode_ci";',
            MYSQLI_STORE_RESULT
        );
    }

    /**
     * Prepares to serialize the database connection. The database connection
     * cannot really be serialized, so we need to just flush any outstanding
     * queries.
     *
     * @return string[] The list of variables that will be serialized.
     */
    public function __sleep(): array {
        $this->Flush();
        return [];
    }

    /**
     * Deserializes the database connection. Just connects to the database again.
     */
    public function __wakeup(): void {
        $this->connect();
    }

    /**
     * Commits any outstanding transactions.
     */
    private function Flush(): void {
        if ($this->_transactionCount > 0) {
            $this->_transactionCount = 1;
            $this->CompleteTrans();
        }
        // Even if there was no transaction, given that we disable autocommit,
        // there is an implicit transaction per connection that needs to be
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
    private function BindQueryParams(string $sql, array $params): string {
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
            } elseif ($params[$i] instanceof \OmegaUp\Timestamp) {
                $chunks[] = "FROM_UNIXTIME({$params[$i]->time})";
            } elseif (is_int($params[$i]) || is_float($params[$i])) {
                $chunks[] = $params[$i];
            } elseif (is_bool($params[$i])) {
                $chunks[] = $params[$i] ? '1' : '0';
            } else {
                $chunks[] = "'" . $this->_connection->real_escape_string(
                    is_scalar($params[$i]) || is_object($params[$i]) ?
                    strval($params[$i]) :
                    ''
                ) . "'";
            }
            $chunks[] = $inputChunks[$i + 1];
        }

        return implode('', $chunks);
    }

    private function MapFieldType(int $fieldType): int {
        switch ($fieldType) {
            case MYSQLI_TYPE_DECIMAL:
            case MYSQLI_TYPE_DOUBLE:
            case MYSQLI_TYPE_FLOAT:
            case MYSQLI_TYPE_NEWDECIMAL:
                return FieldType::TYPE_FLOAT;

            case MYSQLI_TYPE_BIT:
            case MYSQLI_TYPE_TINY:
                return FieldType::TYPE_BOOL;

            case MYSQLI_TYPE_INT24:
            case MYSQLI_TYPE_LONG:
            case MYSQLI_TYPE_LONGLONG:
            case MYSQLI_TYPE_SHORT:
            case MYSQLI_TYPE_YEAR:
                return FieldType::TYPE_INT;

            case MYSQLI_TYPE_DATE:
            case MYSQLI_TYPE_TIME:
            case MYSQLI_TYPE_NEWDATE:
                return FieldType::TYPE_STRING;

            case MYSQLI_TYPE_TIMESTAMP:
            case MYSQLI_TYPE_DATETIME:
                return FieldType::TYPE_TIMESTAMP;

            case MYSQLI_TYPE_ENUM:
            case MYSQLI_TYPE_SET:
            case MYSQLI_TYPE_STRING:
            case MYSQLI_TYPE_VAR_STRING:
                return FieldType::TYPE_STRING;

            case MYSQLI_TYPE_TINY_BLOB:
            case MYSQLI_TYPE_MEDIUM_BLOB:
            case MYSQLI_TYPE_LONG_BLOB:
            case MYSQLI_TYPE_BLOB:
            case MYSQLI_TYPE_JSON:
                return FieldType::TYPE_STRING;

            default:
                throw new \Exception("Unknown field type: {$fieldType}");
        }
    }

    /**
     * @return array<string, int>
     */
    private function MapFieldTypes(\mysqli_result &$result): array {
        $fieldTypes = [];
        /** @var object $field */
        foreach ($result->fetch_fields() as $field) {
            /**
             * @var string $field->name
             * @var int $field->type
             */
            $fieldTypes[$field->name] = self::MapFieldType($field->type);
        }
        return $fieldTypes;
    }

    /**
     * @param mixed $value
     * @return null|int|bool|float|string|\OmegaUp\Timestamp
     */
    private function MapValue($value, int $fieldType) {
        if (is_null($value)) {
            return null;
        }
        switch ($fieldType) {
            case FieldType::TYPE_BOOL:
                return boolval($value);

            case FieldType::TYPE_INT:
                return intval($value);

            case FieldType::TYPE_FLOAT:
                return floatval($value);

            case FieldType::TYPE_TIMESTAMP:
                return new \OmegaUp\Timestamp(strtotime(
                    is_scalar($value) || is_object($value) ? strval($value) : ''
                ));

            case FieldType::TYPE_STRING:
            default:
                return (
                    is_scalar($value) || is_object($value) ? strval($value) : ''
                );
        }
    }

    /**
     * @param null|array<string, mixed> $row
     * @param array<string, int> $fieldTypes
     * @return null|array<string, mixed>
     */
    private function MapRow(?array $row, array $fieldTypes): ?array {
        if (is_null($row)) {
            return null;
        }
        /** @var mixed $value */
        foreach ($row as $key => $value) {
            $row[$key] = self::MapValue($value, $fieldTypes[$key]);
        }
        return $row;
    }

    private function PsalmType(object $field): string {
        $typeName = 'string';
        switch (self::MapFieldType(intval($field->type))) {
            case FieldType::TYPE_BOOL:
                $typeName = 'bool';
                break;

            case FieldType::TYPE_INT:
                $typeName = 'int';
                break;

            case FieldType::TYPE_FLOAT:
                $typeName = 'float';
                break;

            case FieldType::TYPE_TIMESTAMP:
                $typeName = '\\OmegaUp\\Timestamp';
                break;
        }
        if ((intval($field->flags) & MYSQLI_NOT_NULL_FLAG) == 0) {
            $typeAtoms = ['null', $typeName];
            sort($typeAtoms);
            return join('|', $typeAtoms);
        }
        return $typeName;
    }

    private function DumpMySQLQueryResultTypes(\mysqli_result $result): void {
        $caller = debug_backtrace()[1];
        $fieldTypes = [];
        /** @var object $field */
        foreach ($result->fetch_fields() as $field) {
            $fieldTypes[] = "{$field->name}: " . self::PsalmType($field);
        }
        sort($fieldTypes);
        MySQLConnection::getTypesLogger()->info(
            "{$caller['file']}:{$caller['line']} array{" .
            join(', ', $fieldTypes) .
            '}'
        );
    }

    private function DumpMySQLQueryResultTypeSingleField(\mysqli_result $result): void {
        $caller = debug_backtrace()[1];
        MySQLConnection::getTypesLogger()->info(
            "{$caller['file']}:{$caller['line']} " .
            self::PsalmType($result->fetch_field_direct(0))
        );
    }

    private static function getTypesLogger(): \Monolog\Logger {
        if (is_null(MySQLConnection::$_typesLogger)) {
            MySQLConnection::$_typesLogger = new \Monolog\Logger('mysqltypes');
            MySQLConnection::$_typesLogger->pushHandler(
                (new \Monolog\Handler\StreamHandler(
                    OMEGAUP_MYSQL_TYPES_LOG_FILE
                ))->
                    setFormatter(
                        new \Monolog\Formatter\LineFormatter(
                            "%message%\n"
                        )
                    )
            );
        }
        return MySQLConnection::$_typesLogger;
    }

    /**
     * Attempts to executes a MySQL query.
     */
    private function QueryAttempt(
        string $query,
        int $resultmode
    ): ?\mysqli_result {
        try {
            $result = $this->_connection->query($query, $resultmode);
            if ($result === false) {
                $errorMessage = "Failed to query MySQL ({$this->_connection->errno}): {$this->_connection->error}";
                throw new \OmegaUp\Exceptions\DatabaseOperationException(
                    $errorMessage,
                    intval($this->_connection->errno)
                );
            } elseif ($result === true) {
                return null;
            }
            /** @var \mysqli_result */
            return $result;
        } catch (\mysqli_sql_exception $e) {
            $errorMessage = "Failed to query MySQL ({$e->getCode()}): {$e->getMessage()}";
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                $errorMessage,
                $e->getCode()
            );
        }
    }

    /**
     * Executes a MySQL query.
     */
    private function Query(
        string $sql,
        array $params,
        int $resultmode
    ): ?\mysqli_result {
        $query = $this->BindQueryParams($sql, $params);
        try {
            return $this->QueryAttempt($query, $resultmode);
        } catch (\OmegaUp\Exceptions\DatabaseOperationException $e) {
            if (
                $this->_needsFlushing === false &&
                $e->isGoneAway()
            ) {
                // If there have not been any non-committed updates to the
                // database, let's try to reconnect and do this one more time.
                $this->connect();
                return $this->QueryAttempt($query, $resultmode);
            }
            \Monolog\Registry::omegaup()->withName(
                'mysql'
            )->debug(
                $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Executes a MySQL query.
     */
    public function Execute(string $sql, array $params = []): void {
        $this->Query($sql, $params, MYSQLI_STORE_RESULT);
        $this->_needsFlushing = true;
    }

    /**
     * Executes a MySQL query and returns the first row as an associative array.
     *
     * @return array<string, mixed>|null
     */
    public function GetRow(string $sql, array $params = []): ?array {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        if (is_null($result)) {
            return null;
        }
        try {
            if (defined('DUMP_MYSQL_QUERY_RESULT_TYPES')) {
                self::DumpMySQLQueryResultTypes($result);
            }
            $fieldTypes = self::MapFieldTypes($result);
            /** @var array<string, mixed> */
            return self::MapRow($result->fetch_assoc(), $fieldTypes);
        } finally {
            $result->free();
        }
    }

    /**
     * Executes a MySQL query and returns all rows as associative arrays.
     *
     * @return list<array<string, mixed>>
     */
    public function GetAll(string $sql, array $params = []): array {
        $result = $this->Query($sql, $params, MYSQLI_USE_RESULT);
        if (is_null($result)) {
            return [];
        }
        try {
            if (defined('DUMP_MYSQL_QUERY_RESULT_TYPES')) {
                self::DumpMySQLQueryResultTypes($result);
            }
            $fieldTypes = self::MapFieldTypes($result);
            $resultArray = [];
            /** @var array<string, mixed> $row */
            while (!is_null($row = $result->fetch_assoc())) {
                /** @var array<string, mixed> This cannot be null. */
                $resultArray[] = self::MapRow($row, $fieldTypes);
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
            if (defined('DUMP_MYSQL_QUERY_RESULT_TYPES')) {
                self::DumpMySQLQueryResultTypeSingleField($result);
            }
            $row = $result->fetch_row();
            if (empty($row)) {
                return null;
            }
            /** @var object */
            $field = $result->fetch_field_direct(0);
            /** @var int $field->type */
            return self::MapValue(
                $row[0],
                self::MapFieldType($field->type)
            );
        } finally {
            $result->free();
        }
    }

    /**
     * Returns the number of rows affected by the previous query.
     */
    public function Affected_Rows(): int {
        /** @var int */
        return $this->_connection->affected_rows;
    }

    /**
     * Returns the last AUTO_INCREMENT ID that was inserted.
     */
    public function Insert_ID(): int {
        /** @var int */
        return $this->_connection->insert_id;
    }

    /**
     * Returns the provided string escaped in a way that can be used in a query
     * without having SQL injections.
     */
    public function Escape(string $s): string {
        return $this->_connection->real_escape_string($s);
    }

    /**
     * Starts a transaction.
     */
    public function StartTrans(): void {
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
    public function CompleteTrans(): void {
        if ($this->_transactionCount <= 0) {
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                'Called FailTrans() outside of a transaction',
                0
            );
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
    public function FailTrans(): void {
        if ($this->_transactionCount <= 0) {
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                'Called FailTrans() outside of a transaction',
                0
            );
        }
        $this->_transactionOk = false;
    }
}
