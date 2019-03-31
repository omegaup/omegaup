<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Countries.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Countries extends VO {
    const FIELD_NAMES = [
        'country_id' => true,
        'name' => true,
    ];

    /**
     * Constructor de Countries
     *
     * Para construir un objeto de tipo Countries debera llamarse a el constructor
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
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime([]);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var string
     */
    public $country_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $name;
}
