<?php

namespace OmegaUp;

class RabbitMQConnection {
    /**
     * The singleton instance of this class.
     * @var null|RabbitMQConnection
     */
    private static $_instance = null;

    /**
     * @psalm-readonly
     */
    public \PhpAmqpLib\Connection\AMQPStreamConnection $connection;

    /**
     * Returns the singleton instance of this class. It also registers a
     * shutdown function to close the connection upon script
     * termination.
     */
    public static function getInstance(): RabbitMQConnection {
        if (self::$_instance === null) {
            self::$_instance = new RabbitMQConnection();
            register_shutdown_function(function () {
                if (self::$_instance === null) {
                    return;
                }
                self::$_instance->connection->close();
                self::$_instance = null;
            });
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
            OMEGAUP_RABBITMQ_HOST,
            OMEGAUP_RABBITMQ_PORT,
            OMEGAUP_RABBITMQ_USERNAME,
            OMEGAUP_RABBITMQ_PASSWORD,
        );
    }

    public function channel(): \PhpAmqpLib\Channel\AMQPChannel {
        return $this->connection->channel();
    }
}
