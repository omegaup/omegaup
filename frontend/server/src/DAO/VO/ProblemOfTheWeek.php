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
 * Value Object class for table `Problem_Of_The_Week`.
 *
 * @access public
 */
class ProblemOfTheWeek extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'problem_of_the_week_id' => true,
        'problem_id' => true,
        'time' => true,
        'difficulty' => true,
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
        if (isset($data['problem_of_the_week_id'])) {
            $this->problem_of_the_week_id = intval(
                $data['problem_of_the_week_id']
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['time'])) {
            $this->time = is_scalar(
                $data['time']
            ) ? strval($data['time']) : '';
        }
        if (isset($data['difficulty'])) {
            $this->difficulty = is_scalar(
                $data['difficulty']
            ) ? strval($data['difficulty']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $problem_of_the_week_id = 0;

    /**
     * El id del problema escogido como problema de la semana.
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * El inicio de la semana de la cual este problema fue elegido como el mejor de la semana.
     *
     * @var string
     */
    public $time = '2000-01-01';

    /**
     * En algún momento tendremos un problema fácil y uno difícil.
     *
     * @var string|null
     */
    public $difficulty = null;
}
