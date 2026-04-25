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
 * Value Object class for table `QualityNomination_Reviewers`.
 *
 * @access public
 */
class QualityNominationReviewers extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'qualitynomination_id' => true,
        'user_id' => true,
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
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = intval(
                $data['qualitynomination_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $qualitynomination_id = null;

    /**
     * El revisor al que fue asignado esta nominación
     * Llave Primaria
     *
     * @var int|null
     */
    public $user_id = null;
}
