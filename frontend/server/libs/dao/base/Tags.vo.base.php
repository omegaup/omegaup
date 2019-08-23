<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Tags.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Tags extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'tag_id' => true,
        'name' => true,
    ];

    /**
     * Constructor de Tags
     *
     * Para construir un objeto de tipo Tags debera llamarse a el constructor
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
        if (isset($data['tag_id'])) {
            $this->tag_id = (int)$data['tag_id'];
        }
        if (isset($data['name'])) {
            $this->name = strval($data['name']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $tag_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;
}
