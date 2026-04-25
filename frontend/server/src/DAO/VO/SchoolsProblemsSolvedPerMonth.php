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
 * Value Object class for table `Schools_Problems_Solved_Per_Month`.
 *
 * @access public
 */
class SchoolsProblemsSolvedPerMonth extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'school_pspm_id' => true,
        'school_id' => true,
        'time' => true,
        'problems_solved' => true,
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
        if (isset($data['school_pspm_id'])) {
            $this->school_pspm_id = intval(
                $data['school_pspm_id']
            );
        }
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
        }
        if (isset($data['time'])) {
            $this->time = is_scalar(
                $data['time']
            ) ? strval($data['time']) : '';
        }
        if (isset($data['problems_solved'])) {
            $this->problems_solved = intval(
                $data['problems_solved']
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
    public $school_pspm_id = 0;

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
    public $time = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problems_solved = null;
}
