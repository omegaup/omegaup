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
 * Value Object class for table `School_Of_The_Month`.
 *
 * @access public
 */
class SchoolOfTheMonth extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'school_of_the_month_id' => true,
        'school_id' => true,
        'time' => true,
        'ranking' => true,
        'selected_by' => true,
        'score' => true,
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
        if (isset($data['school_of_the_month_id'])) {
            $this->school_of_the_month_id = intval(
                $data['school_of_the_month_id']
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
        if (isset($data['ranking'])) {
            $this->ranking = intval(
                $data['ranking']
            );
        }
        if (isset($data['selected_by'])) {
            $this->selected_by = intval(
                $data['selected_by']
            );
        }
        if (isset($data['score'])) {
            $this->score = floatval(
                $data['score']
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
    public $school_of_the_month_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $school_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $time = '2000-01-01';

    /**
     * El lugar que tuvo la escuela en el mes.
     *
     * @var int|null
     */
    public $ranking = null;

    /**
     * Identidad que seleccionó a la escuela.
     *
     * @var int|null
     */
    public $selected_by = null;

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $score = 0.00;
}
