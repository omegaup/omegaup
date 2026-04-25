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
 * Value Object class for table `Runs_Groups`.
 *
 * @access public
 */
class RunsGroups extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'case_run_id' => true,
        'run_id' => true,
        'group_name' => true,
        'score' => true,
        'verdict' => true,
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
        if (isset($data['case_run_id'])) {
            $this->case_run_id = intval(
                $data['case_run_id']
            );
        }
        if (isset($data['run_id'])) {
            $this->run_id = intval(
                $data['run_id']
            );
        }
        if (isset($data['group_name'])) {
            $this->group_name = is_scalar(
                $data['group_name']
            ) ? strval($data['group_name']) : '';
        }
        if (isset($data['score'])) {
            $this->score = floatval(
                $data['score']
            );
        }
        if (isset($data['verdict'])) {
            $this->verdict = is_scalar(
                $data['verdict']
            ) ? strval($data['verdict']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $case_run_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $run_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $group_name = null;

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $score = 0.00;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $verdict = null;
}
