<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table QualityNomination_Comments.
 *
 * VO does not have any behaviour.
 * @access public
 */
class QualityNominationComments extends VO {
    const FIELD_NAMES = [
        'qualitynomination_comment_id' => true,
        'qualitynomination_id' => true,
        'user_id' => true,
        'time' => true,
        'vote' => true,
        'contents' => true,
    ];

    /**
     * Constructor de QualityNominationComments
     *
     * Para construir un objeto de tipo QualityNominationComments debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['qualitynomination_comment_id'])) {
            $this->qualitynomination_comment_id = (int)$data['qualitynomination_comment_id'];
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = (int)$data['qualitynomination_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['time'])) {
            /**
             * @var string|int|float $data['time']
             * @var int $this->time
             */
            $this->time = DAO::fromMySQLTimestamp($data['time']);
        } else {
            $this->time = Time::get();
        }
        if (isset($data['vote'])) {
            $this->vote = (int)$data['vote'];
        }
        if (isset($data['contents'])) {
            $this->contents = strval($data['contents']);
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
     * @var int
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
