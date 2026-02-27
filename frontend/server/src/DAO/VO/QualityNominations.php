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
 * Value Object class for table `QualityNominations`.
 *
 * @access public
 */
class QualityNominations extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'qualitynomination_id' => true,
        'user_id' => true,
        'problem_id' => true,
        'nomination' => true,
        'contents' => true,
        'time' => true,
        'status' => true,
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
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['nomination'])) {
            $this->nomination = is_scalar(
                $data['nomination']
            ) ? strval($data['nomination']) : '';
        }
        if (isset($data['contents'])) {
            $this->contents = is_scalar(
                $data['contents']
            ) ? strval($data['contents']) : '';
        }
        if (isset($data['time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['time']
             * @var \OmegaUp\Timestamp $this->time
             */
            $this->time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['time']
                )
            );
        } else {
            $this->time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['status'])) {
            $this->status = is_scalar(
                $data['status']
            ) ? strval($data['status']) : '';
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
     * @var \OmegaUp\Timestamp
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * El estado de la nominación
     *
     * @var string
     */
    public $status = 'open';
}
