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
 * Value Object class for table `Problems_Forfeited`.
 *
 * @access public
 */
class ProblemsForfeited extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'user_id' => true,
        'problem_id' => true,
        'forfeited_date' => true,
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
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['forfeited_date'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['forfeited_date']
             * @var \OmegaUp\Timestamp $this->forfeited_date
             */
            $this->forfeited_date = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['forfeited_date']
                )
            );
        } else {
            $this->forfeited_date = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * Identificador de usuario
     * Llave Primaria
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $forfeited_date;  // CURRENT_TIMESTAMP
}
