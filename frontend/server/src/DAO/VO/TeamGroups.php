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
 * Value Object class for table `Team_Groups`.
 *
 * @access public
 */
class TeamGroups extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'team_group_id' => true,
        'acl_id' => true,
        'create_time' => true,
        'alias' => true,
        'name' => true,
        'description' => true,
        'number_of_contestants' => true,
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
        if (isset($data['team_group_id'])) {
            $this->team_group_id = intval(
                $data['team_group_id']
            );
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = intval(
                $data['acl_id']
            );
        }
        if (isset($data['create_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['create_time']
             * @var \OmegaUp\Timestamp $this->create_time
             */
            $this->create_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['create_time']
                )
            );
        } else {
            $this->create_time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['alias'])) {
            $this->alias = is_scalar(
                $data['alias']
            ) ? strval($data['alias']) : '';
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['description'])) {
            $this->description = is_scalar(
                $data['description']
            ) ? strval($data['description']) : '';
        }
        if (isset($data['number_of_contestants'])) {
            $this->number_of_contestants = intval(
                $data['number_of_contestants']
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $team_group_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $acl_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
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

    /**
     * Número de concursantes para los equipos del grupo
     *
     * @var int
     */
    public $number_of_contestants = 3;
}
