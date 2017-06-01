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
    /**
     * Constructor de QualityNominations
     *
     * Para construir un objeto de tipo QualityNominations debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = $data['qualitynomination_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = $data['problem_id'];
        }
        if (isset($data['nomination'])) {
            $this->nomination = $data['nomination'];
        }
        if (isset($data['contents'])) {
            $this->contents = $data['contents'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['time']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $qualitynomination_id;

    /**
      * El usuario que nominó el problema
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      * El problema que fue nominado
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      * Si se está nominando el problema a promoción o democión
      * @access public
      * @var enum('promotion',
      */
    public $nomination;

    /**
      * Un blob json con el contenido de la nominación
      * @access public
      * @var text
      */
    public $contents;

    /**
      * Fecha de creacion de esta nominación
      * @access public
      * @var timestamp
      */
    public $time;

    /**
      * El estado de la nominación
      * @access public
      * @var enum('open',
      */
    public $status;
}
