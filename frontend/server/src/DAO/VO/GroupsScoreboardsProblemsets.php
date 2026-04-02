<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Groups_Scoreboards_Problemsets`.
 *
 * @access public
 */
class GroupsScoreboardsProblemsets extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'group_scoreboard_id' => true,
        'problemset_id' => true,
        'only_ac' => true,
        'weight' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['group_scoreboard_id'])) {
            $this->group_scoreboard_id = intval(
                $data['group_scoreboard_id']
            );
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['only_ac'])) {
            $this->only_ac = boolval(
                $data['only_ac']
            );
        }
        if (isset($data['weight'])) {
            $this->weight = intval(
                $data['weight']
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $group_scoreboard_id = null;

    /**
     * Conjunto de problemas del scoreboard
     * Llave Primaria
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $only_ac = false;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $weight = 1;
}
