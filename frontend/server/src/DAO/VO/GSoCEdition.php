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
 * Value Object class for table `GSoC_Edition`.
 *
 * @access public
 */
class GSoCEdition extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'edition_id' => true,
        'year' => true,
        'is_active' => true,
        'application_deadline' => true,
        'created_at' => true,
        'updated_at' => true,
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
        if (isset($data['edition_id'])) {
            $this->edition_id = intval(
                $data['edition_id']
            );
        }
        if (isset($data['year'])) {
            $this->year = intval(
                $data['year']
            );
        }
        if (isset($data['is_active'])) {
            $this->is_active = boolval(
                $data['is_active']
            );
        }
        if (isset($data['application_deadline'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['application_deadline']
             * @var \OmegaUp\Timestamp $this->application_deadline
             */
            $this->application_deadline = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['application_deadline']
                )
            );
        }
        if (isset($data['created_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['created_at']
             * @var \OmegaUp\Timestamp $this->created_at
             */
            $this->created_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['created_at']
                )
            );
        } else {
            $this->created_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['updated_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['updated_at']
             * @var \OmegaUp\Timestamp $this->updated_at
             */
            $this->updated_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['updated_at']
                )
            );
        } else {
            $this->updated_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
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
    public $edition_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $year = null;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $is_active = false;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $application_deadline = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $updated_at;  // CURRENT_TIMESTAMP
}
