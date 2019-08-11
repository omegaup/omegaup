<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Permissions.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Permissions extends VO {
    const FIELD_NAMES = [
        'permission_id' => true,
        'name' => true,
        'description' => true,
    ];

    /**
     * Constructor de Permissions
     *
     * Para construir un objeto de tipo Permissions debera llamarse a el constructor
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
        if (isset($data['permission_id'])) {
            $this->permission_id = (int)$data['permission_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $permission_id;

    /**
      * El nombre corto del permiso.
      * @access public
      * @var string
     */
    public $name;

    /**
      * La descripción humana del permiso.
      * @access public
      * @var string
     */
    public $description;
}
