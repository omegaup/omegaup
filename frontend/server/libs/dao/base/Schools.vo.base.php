<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Schools.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Schools extends VO {
    const FIELD_NAMES = [
        'school_id' => true,
        'country_id' => true,
        'state_id' => true,
        'name' => true,
    ];

    /**
     * Constructor de Schools
     *
     * Para construir un objeto de tipo Schools debera llamarse a el constructor
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
        if (isset($data['school_id'])) {
            $this->school_id = (int)$data['school_id'];
        }
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
        }
        if (isset($data['state_id'])) {
            $this->state_id = $data['state_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $school_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $country_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $state_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $name;
}
