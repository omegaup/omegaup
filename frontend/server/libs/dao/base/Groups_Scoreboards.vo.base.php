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
            /**
             * @var string|int|float $data['create_time']
             * @var int $this->create_time
             */
            $this->create_time = DAO::fromMySQLTimestamp($data['create_time']);
        } else {
            $this->create_time = Time::get();
        }
        if (isset($data['alias'])) {
            $this->alias = strval($data['alias']);
        }
        if (isset($data['name'])) {
            $this->name = strval($data['name']);
        }
        if (isset($data['description'])) {
            $this->description = strval($data['description']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $group_scoreboard_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $group_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $create_time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $alias = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $description = null;
}
