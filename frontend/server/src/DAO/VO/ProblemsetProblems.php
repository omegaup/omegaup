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
 * Value Object class for table `Problemset_Problems`.
 *
 * @access public
 */
class ProblemsetProblems extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'problemset_id' => true,
        'problem_id' => true,
        'commit' => true,
        'version' => true,
        'points' => true,
        'order' => true,
        'is_extra_problem' => true,
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
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['commit'])) {
            $this->commit = is_scalar(
                $data['commit']
            ) ? strval($data['commit']) : '';
        }
        if (isset($data['version'])) {
            $this->version = is_scalar(
                $data['version']
            ) ? strval($data['version']) : '';
        }
        if (isset($data['points'])) {
            $this->points = floatval(
                $data['points']
            );
        }
        if (isset($data['order'])) {
            $this->order = intval(
                $data['order']
            );
        }
        if (isset($data['is_extra_problem'])) {
            $this->is_extra_problem = boolval(
                $data['is_extra_problem']
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * El hash SHA1 del commit en la rama master del problema.
     *
     * @var string
     */
    public $commit = 'published';

    /**
     * El hash SHA1 del árbol de la rama private.
     *
     * @var string|null
     */
    public $version = null;

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $points = 1.00;

    /**
     * Define el orden de aparición de los problemas en una lista de problemas
     *
     * @var int
     */
    public $order = 1;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $is_extra_problem = false;
}
