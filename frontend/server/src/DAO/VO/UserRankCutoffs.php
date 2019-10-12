<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `User_Rank_Cutoffs`.
 *
 * @access public
 */
class UserRankCutoffs extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'score' => true,
        'percentile' => true,
        'classname' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['score'])) {
            $this->score = floatval($data['score']);
        }
        if (isset($data['percentile'])) {
            $this->percentile = floatval($data['percentile']);
        }
        if (isset($data['classname'])) {
            $this->classname = strval($data['classname']);
        }
    }

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $score = null;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $percentile = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $classname = null;
}
