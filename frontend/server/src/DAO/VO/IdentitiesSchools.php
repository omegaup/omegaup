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
 * Value Object class for table `Identities_Schools`.
 *
 * @access public
 */
class IdentitiesSchools extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'identity_school_id' => true,
        'identity_id' => true,
        'school_id' => true,
        'graduation_date' => true,
        'creation_time' => true,
        'end_time' => true,
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
        if (isset($data['identity_school_id'])) {
            $this->identity_school_id = intval(
                $data['identity_school_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
        }
        if (isset($data['graduation_date'])) {
            $this->graduation_date = is_scalar(
                $data['graduation_date']
            ) ? strval($data['graduation_date']) : '';
        }
        if (isset($data['creation_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['creation_time']
             * @var \OmegaUp\Timestamp $this->creation_time
             */
            $this->creation_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['creation_time']
                )
            );
        } else {
            $this->creation_time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['end_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['end_time']
             * @var \OmegaUp\Timestamp $this->end_time
             */
            $this->end_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['end_time']
                )
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
    public $identity_school_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $school_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $graduation_date = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $creation_time;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $end_time = null;
}
