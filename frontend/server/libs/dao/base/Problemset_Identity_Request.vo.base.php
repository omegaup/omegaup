<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Identity_Request.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetIdentityRequest extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'identity_id' => true,
        'problemset_id' => true,
        'request_time' => true,
        'last_update' => true,
        'accepted' => true,
        'extra_note' => true,
    ];

    /**
     * Constructor de ProblemsetIdentityRequest
     *
     * Para construir un objeto de tipo ProblemsetIdentityRequest debera llamarse a el constructor
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
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['request_time'])) {
            /**
             * @var string|int|float $data['request_time']
             * @var int $this->request_time
             */
            $this->request_time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['request_time']);
        } else {
            $this->request_time = \OmegaUp\Time::get();
        }
        if (isset($data['last_update'])) {
            /**
             * @var string|int|float $data['last_update']
             * @var int $this->last_update
             */
            $this->last_update = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['last_update']);
        }
        if (isset($data['accepted'])) {
            $this->accepted = boolval($data['accepted']);
        }
        if (isset($data['extra_note'])) {
            $this->extra_note = strval($data['extra_note']);
        }
    }

    /**
     * Identidad del usuario
     * Llave Primaria
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $request_time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $last_update = null;

    /**
     * [Campo no documentado]
     *
     * @var bool|null
     */
    public $accepted = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $extra_note = null;
}
