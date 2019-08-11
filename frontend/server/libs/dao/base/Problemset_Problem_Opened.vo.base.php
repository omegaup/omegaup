<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Problem_Opened.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetProblemOpened extends VO {
    const FIELD_NAMES = [
        'problemset_id' => true,
        'problem_id' => true,
        'identity_id' => true,
        'open_time' => true,
    ];

    /**
     * Constructor de ProblemsetProblemOpened
     *
     * Para construir un objeto de tipo ProblemsetProblemOpened debera llamarse a el constructor
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
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['open_time'])) {
            $this->open_time = DAO::fromMySQLTimestamp($data['open_time']);
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['open_time']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $problem_id;

    /**
      * Identidad del usuario
      * Llave Primaria
      * @access public
      * @var int
     */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $open_time = null;  // CURRENT_TIMESTAMP
}
