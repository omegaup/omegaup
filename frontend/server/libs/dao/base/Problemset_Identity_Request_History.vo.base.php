<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Identity_Request_History.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetIdentityRequestHistory extends VO {
    const FIELD_NAMES = [
        'history_id' => true,
        'identity_id' => true,
        'problemset_id' => true,
        'time' => true,
        'accepted' => true,
        'admin_id' => true,
    ];

    /**
     * Constructor de ProblemsetIdentityRequestHistory
     *
     * Para construir un objeto de tipo ProblemsetIdentityRequestHistory debera llamarse a el constructor
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
        if (isset($data['history_id'])) {
            $this->history_id = (int)$data['history_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
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
        if (isset($data['accepted'])) {
            $this->accepted = boolval($data['accepted']);
        }
        if (isset($data['admin_id'])) {
            $this->admin_id = (int)$data['admin_id'];
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $history_id = 0;

    /**
     * Identidad del usuario
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var bool|null
     */
    public $accepted = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $admin_id = null;
}
