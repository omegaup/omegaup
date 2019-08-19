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
    const FIELD_NAMES = [
        'country_id' => true,
        'state_id' => true,
        'name' => true,
    ];

    /**
     * Constructor de States
     *
     * Para construir un objeto de tipo States debera llamarse a el constructor
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
            $this->country_id = strval($data['country_id']);
        }
        if (isset($data['state_id'])) {
            $this->state_id = strval($data['state_id']);
        }
        if (isset($data['name'])) {
            $this->name = strval($data['name']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var string|null
     */
    public $country_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var string|null
     */
    public $state_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;
}
