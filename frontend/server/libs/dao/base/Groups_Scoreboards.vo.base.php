<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Groups_Scoreboards.
 *
 * VO does not have any behaviour.
 * @access public
 */
class GroupsScoreboards extends VO {
    /**
     * Constructor de GroupsScoreboards
     *
     * Para construir un objeto de tipo GroupsScoreboards debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['group_scoreboard_id'])) {
            $this->group_scoreboard_id = $data['group_scoreboard_id'];
        }
        if (isset($data['group_id'])) {
            $this->group_id = $data['group_id'];
        }
        if (isset($data['create_time'])) {
            $this->create_time = $data['create_time'];
        }
        if (isset($data['alias'])) {
            $this->alias = $data['alias'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto GroupsScoreboards en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() {
        return json_encode([
            'group_scoreboard_id' => $this->group_scoreboard_id,
            'group_id' => $this->group_id,
            'create_time' => $this->create_time,
            'alias' => $this->alias,
            'name' => $this->name,
            'description' => $this->description,
        ]);
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['create_time']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $group_scoreboard_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $group_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $create_time;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $alias;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(256)
      */
    public $description;
}
