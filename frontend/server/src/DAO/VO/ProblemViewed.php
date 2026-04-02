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
 * Value Object class for table `Problem_Viewed`.
 *
 * @access public
 */
class ProblemViewed extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'problem_id' => true,
        'identity_id' => true,
        'view_time' => true,
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
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['view_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['view_time']
             * @var \OmegaUp\Timestamp $this->view_time
             */
            $this->view_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['view_time']
                )
            );
        } else {
            $this->view_time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * Identidad del usuario
     * Llave Primaria
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $view_time;  // CURRENT_TIMESTAMP
}
