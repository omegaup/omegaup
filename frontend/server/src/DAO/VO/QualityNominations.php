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
 * Value Object class for table `QualityNominations`.
 *
 * @access public
 */
class QualityNominations extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'qualitynomination_id' => true,
        'user_id' => true,
        'problem_id' => true,
        'nomination' => true,
        'contents' => true,
        'time' => true,
        'status' => true,
    ];

    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = (int)$data['qualitynomination_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['nomination'])) {
            $this->nomination = strval($data['nomination']);
        }
        if (isset($data['contents'])) {
            $this->contents = strval($data['contents']);
        }
        if (isset($data['time'])) {
            /**
             * @var string|int|float $data['time']
             * @var int $this->time
             */
            $this->time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['time']);
        } else {
            $this->time = \OmegaUp\Time::get();
        }
        if (isset($data['status'])) {
            $this->status = strval($data['status']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $qualitynomination_id = 0;

    /**
     * El usuario que nominó el problema
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * El problema que fue nominado
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * El tipo de nominación
     *
     * @var string
     */
    public $nomination = 'suggestion';

    /**
     * Un blob json con el contenido de la nominación
     *
     * @var string|null
     */
    public $contents = null;

    /**
     * Fecha de creacion de esta nominación
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * El estado de la nominación
     *
     * @var string
     */
    public $status = 'open';
}
