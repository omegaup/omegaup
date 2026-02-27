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
 * Value Object class for table `Clarifications`.
 *
 * @access public
 */
class Clarifications extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'clarification_id' => true,
        'author_id' => true,
        'receiver_id' => true,
        'message' => true,
        'answer' => true,
        'time' => true,
        'problem_id' => true,
        'problemset_id' => true,
        'public' => true,
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
        if (isset($data['clarification_id'])) {
            $this->clarification_id = intval(
                $data['clarification_id']
            );
        }
        if (isset($data['author_id'])) {
            $this->author_id = intval(
                $data['author_id']
            );
        }
        if (isset($data['receiver_id'])) {
            $this->receiver_id = intval(
                $data['receiver_id']
            );
        }
        if (isset($data['message'])) {
            $this->message = is_scalar(
                $data['message']
            ) ? strval($data['message']) : '';
        }
        if (isset($data['answer'])) {
            $this->answer = is_scalar(
                $data['answer']
            ) ? strval($data['answer']) : '';
        }
        if (isset($data['time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['time']
             * @var \OmegaUp\Timestamp $this->time
             */
            $this->time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['time']
                )
            );
        } else {
            $this->time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['public'])) {
            $this->public = boolval(
                $data['public']
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
    public $clarification_id = 0;

    /**
     * Autor de la clarificación.
     *
     * @var int|null
     */
    public $author_id = null;

    /**
     * Usuario que recibirá el mensaje
     *
     * @var int|null
     */
    public $receiver_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $message = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $answer = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema o al contest owner si no esta ligado a un problema.
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * Sólo las clarificaciones que el problemsetter marque como publicables aparecerán en la lista que todos pueden ver.
     *
     * @var bool
     */
    public $public = false;
}
