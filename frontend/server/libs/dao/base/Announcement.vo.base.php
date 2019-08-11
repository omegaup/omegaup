<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Announcement.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Announcement extends VO {
    const FIELD_NAMES = [
        'announcement_id' => true,
        'user_id' => true,
        'time' => true,
        'description' => true,
    ];

    /**
     * Constructor de Announcement
     *
     * Para construir un objeto de tipo Announcement debera llamarse a el constructor
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
        if (isset($data['announcement_id'])) {
            $this->announcement_id = (int)$data['announcement_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['time'])) {
            $this->time = DAO::fromMySQLTimestamp($data['time']);
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['time']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      * Identificador del aviso
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $announcement_id;

    /**
      * UserID del autor de este aviso
      * @access public
      * @var int
     */
    public $user_id;

    /**
      * Fecha de creacion de este aviso
      * @access public
      * @var int
     */
    public $time = null;  // CURRENT_TIMESTAMP

    /**
      * Mensaje de texto del aviso
      * @access public
      * @var string
     */
    public $description;
}
