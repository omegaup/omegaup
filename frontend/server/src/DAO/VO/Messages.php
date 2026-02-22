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
 * Value Object class for table `Messages`.
 *
 * @access public
 */
class Messages extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'message_id' => true,
        'read' => true,
        'sender_id' => true,
        'recipient_id' => true,
        'message' => true,
        'date' => true,
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
        if (isset($data['message_id'])) {
            $this->message_id = intval(
                $data['message_id']
            );
        }
        if (isset($data['read'])) {
            $this->read = boolval(
                $data['read']
            );
        }
        if (isset($data['sender_id'])) {
            $this->sender_id = intval(
                $data['sender_id']
            );
        }
        if (isset($data['recipient_id'])) {
            $this->recipient_id = intval(
                $data['recipient_id']
            );
        }
        if (isset($data['message'])) {
            $this->message = is_scalar(
                $data['message']
            ) ? strval($data['message']) : '';
        }
        if (isset($data['date'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['date']
             * @var \OmegaUp\Timestamp $this->date
             */
            $this->date = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['date']
                )
            );
        } else {
            $this->date = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $message_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $read = false;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $sender_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $recipient_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $message = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $date;  // CURRENT_TIMESTAMP
}
