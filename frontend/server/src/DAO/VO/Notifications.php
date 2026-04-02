<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Notifications`.
 *
 * @access public
 */
class Notifications extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'notification_id' => true,
        'user_id' => true,
        'timestamp' => true,
        'read' => true,
        'contents' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['notification_id'])) {
            $this->notification_id = intval(
                $data['notification_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['timestamp'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['timestamp']
             * @var \OmegaUp\Timestamp $this->timestamp
             */
            $this->timestamp = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['timestamp']
                )
            );
        } else {
            $this->timestamp = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['read'])) {
            $this->read = boolval(
                $data['read']
            );
        }
        if (isset($data['contents'])) {
            $this->contents = is_scalar(
                $data['contents']
            ) ? strval($data['contents']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $notification_id = 0;

    /**
     * Identificador de usuario
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $timestamp;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $read = false;

    /**
     * JSON con el contenido de la notificación
     *
     * @var string|null
     */
    public $contents = null;
}
