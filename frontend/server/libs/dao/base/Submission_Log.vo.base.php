<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Submission_Log.
 *
 * VO does not have any behaviour.
 * @access public
 */
class SubmissionLog extends VO {
    const FIELD_NAMES = [
        'problemset_id' => true,
        'submission_id' => true,
        'user_id' => true,
        'identity_id' => true,
        'ip' => true,
        'time' => true,
    ];

    /**
     * Constructor de SubmissionLog
     *
     * Para construir un objeto de tipo SubmissionLog debera llamarse a el constructor
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
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['submission_id'])) {
            $this->submission_id = (int)$data['submission_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['ip'])) {
            $this->ip = (int)$data['ip'];
        }
        if (isset($data['time'])) {
            $this->time = DAO::fromMySQLTimestamp($data['time']);
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['time']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $submission_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $user_id;

    /**
      * Identidad del usuario
      * @access public
      * @var int
     */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $ip;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $time = null;  // CURRENT_TIMESTAMP
}
