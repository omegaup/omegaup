<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Groups_Scoreboards`.
 *
 * @access public
 */
class GroupsScoreboards extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'group_scoreboard_id' => true,
        'group_id' => true,
        'create_time' => true,
        'alias' => true,
        'name' => true,
        'description' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['group_scoreboard_id'])) {
            $this->group_scoreboard_id = intval($data['group_scoreboard_id']);
        }
        if (isset($data['group_id'])) {
            $this->group_id = intval($data['group_id']);
        }
        if (isset($data['create_time'])) {
            /**
             * @var string|int|float $data['create_time']
             * @var int $this->create_time
             */
            $this->create_time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['create_time']);
        } else {
            $this->create_time = \OmegaUp\Time::get();
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
