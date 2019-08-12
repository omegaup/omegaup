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
    const FIELD_NAMES = [
        'language_id' => true,
        'name' => true,
        'country_id' => true,
    ];

    /**
     * Constructor de Languages
     *
     * Para construir un objeto de tipo Languages debera llamarse a el constructor
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
        if (isset($data['language_id'])) {
            $this->language_id = (int)$data['language_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $language_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $name;

    /**
      * Se guarda la relación con el país para defaultear más rápido.
      * @access public
      * @var ?string
     */
    public $country_id;
}
