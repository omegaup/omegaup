<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Contest_Log.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ContestLog extends VO {
    const FIELD_NAMES = [
        'public_contest_id' => true,
        'contest_id' => true,
        'user_id' => true,
        'from_admission_mode' => true,
        'to_admission_mode' => true,
        'time' => true,
    ];

    /**
     * Constructor de ContestLog
     *
     * Para construir un objeto de tipo ContestLog debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['public_contest_id'])) {
            $this->public_contest_id = (int)$data['public_contest_id'];
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = (int)$data['contest_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['from_admission_mode'])) {
            $this->from_admission_mode = strval($data['from_admission_mode']);
        }
        if (isset($data['to_admission_mode'])) {
            $this->to_admission_mode = strval($data['to_admission_mode']);
        }
        if (isset($data['time'])) {
            /**
             * @var string|int|float $data['time']
             * @var int $this->time
             */
            $this->time = DAO::fromMySQLTimestamp($data['time']);
        } else {
            $this->time = Time::get();
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $public_contest_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $contest_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $from_admission_mode = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $to_admission_mode = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP
}
