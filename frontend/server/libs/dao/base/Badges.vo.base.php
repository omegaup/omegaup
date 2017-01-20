<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Badges.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Badges extends VO {
    /**
     * Constructor de Badges
     *
     * Para construir un objeto de tipo Badges debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['badge_id'])) {
            $this->badge_id = $data['badge_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['image_url'])) {
            $this->image_url = $data['image_url'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['hint'])) {
            $this->hint = $data['hint'];
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
    public $badge_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(45)
      */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(45)
      */
    public $image_url;

    /**
      * La descripcion habla de como se obtuvo el badge, de forma corta.
      * @access public
      * @var varchar(500)
      */
    public $description;

    /**
      * Tip de como desbloquear el badge.
      * @access public
      * @var varchar(100)
      */
    public $hint;
}
