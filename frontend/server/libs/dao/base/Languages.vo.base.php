<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Languages.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Languages extends VO {
    /**
     * Constructor de Languages
     *
     * Para construir un objeto de tipo Languages debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['language_id'])) {
            $this->language_id = $data['language_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
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
    public $language_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(45)
      */
    public $name;

    /**
      * Se guarda la relación con el país para defaultear más rápido.
      * @access public
      * @var char(3)
      */
    public $country_id;
}
