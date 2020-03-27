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
 * Value Object class for table `Schools_Monthly_Count`.
 *
 * @access public
 */
class SchoolsMonthlyCount extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'school_monthly_count_id' => true,
        'school_id' => true,
        'year' => true,
        'month' => true,
        'count' => true,
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
        if (isset($data['school_monthly_count_id'])) {
            $this->school_monthly_count_id = intval(
                $data['school_monthly_count_id']
            );
        }
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
        }
        if (isset($data['year'])) {
            $this->year = strval(
                $data['year']
            );
        }
        if (isset($data['month'])) {
            $this->month = strval(
                $data['month']
            );
        }
        if (isset($data['count'])) {
            $this->count = intval(
                $data['count']
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
    public $school_monthly_count_id = 0;

    /**
     * Identificador de escuela
     *
     * @var int|null
     */
    public $school_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $year = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $month = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $count = null;
}
