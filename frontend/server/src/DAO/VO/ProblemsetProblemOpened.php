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
 * Value Object class for table `Problemset_Problem_Opened`.
 *
 * @access public
 */
class ProblemsetProblemOpened extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'problemset_id' => true,
        'problem_id' => true,
        'identity_id' => true,
        'open_time' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval($data['problemset_id']);
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval($data['problem_id']);
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval($data['identity_id']);
        }
        if (isset($data['open_time'])) {
            /**
             * @var string|int|float $data['open_time']
             * @var int $this->open_time
             */
            $this->open_time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['open_time']);
        } else {
            $this->open_time = \OmegaUp\Time::get();
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
     * Identidad del usuario
     * Llave Primaria
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $open_time;  // CURRENT_TIMESTAMP
}
