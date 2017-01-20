<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table States.
 *
 * VO does not have any behaviour.
 * @access public
 */
class States extends VO {
    /**
     * Constructor de States
     *
     * Para construir un objeto de tipo States debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['state_id'])) {
            $this->state_id = $data['state_id'];
        }
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
        }
        if (isset($data['state_code'])) {
            $this->state_code = $data['state_code'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime([]);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $state_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(3)
      */
    public $country_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(
      */
    public $state_code;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $name;
}
