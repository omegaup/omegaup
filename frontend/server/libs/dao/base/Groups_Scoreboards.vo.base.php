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
    const FIELD_NAMES = [
        'group_scoreboard_id' => true,
        'group_id' => true,
        'create_time' => true,
        'alias' => true,
        'name' => true,
        'description' => true,
    ];

    /**
     * Constructor de GroupsScoreboards
     *
     * Para construir un objeto de tipo GroupsScoreboards debera llamarse a el constructor
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
        if (isset($data['group_scoreboard_id'])) {
            $this->group_scoreboard_id = (int)$data['group_scoreboard_id'];
        }
        if (isset($data['group_id'])) {
            $this->group_id = (int)$data['group_id'];
        }
        if (isset($data['create_time'])) {
            $this->create_time = DAO::fromMySQLTimestamp($data['create_time']);
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
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $group_scoreboard_id = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $group_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $create_time = null;  // CURRENT_TIMESTAMP

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $alias;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $description;
}
