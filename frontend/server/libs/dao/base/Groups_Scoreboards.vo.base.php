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
            $this->group_scoreboard_id = (int)$data['group_scoreboard_id'];
        }
        if (isset($data['group_id'])) {
            $this->group_id = (int)$data['group_id'];
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
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime(['create_time']);
            return;
        }
        parent::toUnixTime($fields);
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
