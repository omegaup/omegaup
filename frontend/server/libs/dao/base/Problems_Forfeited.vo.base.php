<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problems_Forfeited.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsForfeited extends VO {
    const FIELD_NAMES = [
        'user_id' => true,
        'problem_id' => true,
        'forfeited_date' => true,
    ];

    /**
     * Constructor de ProblemsForfeited
     *
     * Para construir un objeto de tipo ProblemsForfeited debera llamarse a el constructor
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
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['forfeited_date'])) {
            $this->forfeited_date = DAO::fromMySQLTimestamp($data['forfeited_date']);
        }
    }

    /**
      * Identificador de usuario
      * Llave Primaria
      * @access public
      * @var int
     */
    public $user_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $forfeited_date = null;  // CURRENT_TIMESTAMP
}
