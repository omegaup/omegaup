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
 * Value Object class for table `Problem_Recommendations`.
 *
 * @access public
 */
class ProblemRecommendations extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'solved_problem_id' => true,
        'recommended_problem_id' => true,
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
        if (isset($data['solved_problem_id'])) {
            $this->solved_problem_id = intval(
                $data['solved_problem_id']
            );
        }
        if (isset($data['recommended_problem_id'])) {
            $this->recommended_problem_id = intval(
                $data['recommended_problem_id']
            );
        }
        if (isset($data['score'])) {
            $this->score = floatval(
                $data['score']
            );
        }
    }

    /**
     * El problema resuelto a partir del cual se genera la recomendación
     * Llave Primaria
     *
     * @var int|null
     */
    public $solved_problem_id = null;

    /**
     * El problema recomendado para resolver después
     * Llave Primaria
     *
     * @var int|null
     */
    public $recommended_problem_id = null;

    /**
     * Peso relativo de esta recomendación frente a las demás del mismo problema resuelto
     *
     * @var float|null
     */
    public $score = null;
}
