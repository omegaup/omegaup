<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Clarifications.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Clarifications extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'clarification_id' => true,
        'author_id' => true,
        'receiver_id' => true,
        'message' => true,
        'answer' => true,
        'time' => true,
        'problem_id' => true,
        'problemset_id' => true,
        'public' => true,
    ];

    /**
     * Constructor de Clarifications
     *
     * Para construir un objeto de tipo Clarifications debera llamarse a el constructor
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
        if (isset($data['clarification_id'])) {
            $this->clarification_id = (int)$data['clarification_id'];
        }
        if (isset($data['author_id'])) {
            $this->author_id = (int)$data['author_id'];
        }
        if (isset($data['receiver_id'])) {
            $this->receiver_id = (int)$data['receiver_id'];
        }
        if (isset($data['message'])) {
            $this->message = strval($data['message']);
        }
        if (isset($data['answer'])) {
            $this->answer = strval($data['answer']);
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
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['public'])) {
            $this->public = boolval($data['public']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $clarification_id = 0;

    /**
     * Autor de la clarificación.
     *
     * @var int|null
     */
    public $author_id = null;

    /**
     * Usuario que recibirá el mensaje
     *
     * @var int|null
     */
    public $receiver_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $message = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $answer = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema o al contest owner si no esta ligado a un problema.
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * Sólo las clarificaciones que el problemsetter marque como publicables aparecerán en la lista que todos pueden ver.
     *
     * @var bool
     */
    public $public = false;
}
