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
 * Value Object class for table `QualityNomination_Comments`.
 *
 * @access public
 */
class QualityNominationComments extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'qualitynomination_comment_id' => true,
        'qualitynomination_id' => true,
        'user_id' => true,
        'time' => true,
        'vote' => true,
        'contents' => true,
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
        if (isset($data['qualitynomination_comment_id'])) {
            $this->qualitynomination_comment_id = intval(
                $data['qualitynomination_comment_id']
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
        if (isset($data['vote'])) {
            $this->vote = intval(
                $data['vote']
            );
        }
        if (isset($data['contents'])) {
            $this->contents = is_scalar(
                $data['contents']
            ) ? strval($data['contents']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $qualitynomination_comment_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $qualitynomination_id = null;

    /**
     * El usuario que emitió el comentario
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Fecha de creacion de este comentario
     *
     * @var \OmegaUp\Timestamp
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * El voto emitido en este comentario. En el rango de [-2, +2]
     *
     * @var int|null
     */
    public $vote = null;

    /**
     * El contenido de el comentario
     *
     * @var string|null
     */
    public $contents = null;
}
