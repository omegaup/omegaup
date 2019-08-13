<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table QualityNominations.
 *
 * VO does not have any behaviour.
 * @access public
 */
class QualityNominations extends VO {
    const FIELD_NAMES = [
        'qualitynomination_id' => true,
        'user_id' => true,
        'problem_id' => true,
        'nomination' => true,
        'contents' => true,
        'time' => true,
        'status' => true,
    ];

    /**
     * Constructor de QualityNominations
     *
     * Para construir un objeto de tipo QualityNominations debera llamarse a el constructor
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
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = (int)$data['qualitynomination_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['nomination'])) {
            $this->nomination = $data['nomination'];
        }
        if (isset($data['contents'])) {
            $this->contents = $data['contents'];
        }
        if (isset($data['time'])) {
            $this->time = DAO::fromMySQLTimestamp($data['time']);
        }
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $qualitynomination_id = 0;

    /**
      * El usuario que nominó el problema
      * @access public
      * @var int
     */
    public $user_id;

    /**
      * El problema que fue nominado
      * @access public
      * @var int
     */
    public $problem_id;

    /**
      * El tipo de nominación
      * @access public
      * @var string
     */
    public $nomination = 'suggestion';

    /**
      * Un blob json con el contenido de la nominación
      * @access public
      * @var string
     */
    public $contents;

    /**
      * Fecha de creacion de esta nominación
      * @access public
      * @var int
     */
    public $time = null;  // CURRENT_TIMESTAMP

    /**
      * El estado de la nominación
      * @access public
      * @var string
     */
    public $status = 'open';
}
