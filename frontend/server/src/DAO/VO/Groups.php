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
 * Value Object class for table `Groups_`.
 *
 * @access public
 */
class Groups extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'group_id' => true,
        'acl_id' => true,
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
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['group_id'])) {
            $this->group_id = intval(
                $data['group_id']
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
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $group_id = 0;

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
}
