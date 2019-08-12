<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problem_Viewed.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemViewed extends VO {
    const FIELD_NAMES = [
        'problem_id' => true,
        'identity_id' => true,
        'view_time' => true,
    ];

    /**
     * Constructor de ProblemViewed
     *
     * Para construir un objeto de tipo ProblemViewed debera llamarse a el constructor
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
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['view_time'])) {
            $this->view_time = DAO::fromMySQLTimestamp($data['view_time']);
        }
    }

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
    public $view_time = null;  // CURRENT_TIMESTAMP
}
