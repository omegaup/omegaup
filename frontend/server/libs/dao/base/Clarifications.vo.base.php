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
class Clarifications extends VO {
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
            $this->message = $data['message'];
        }
        if (isset($data['answer'])) {
            $this->answer = $data['answer'];
        }
        if (isset($data['time'])) {
            $this->time = DAO::fromMySQLTimestamp($data['time']);
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
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['time']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $clarification_id;

    /**
      * Autor de la clarificación.
      * @access public
      * @var int
     */
    public $author_id;

    /**
      * Usuario que recibirá el mensaje
      * @access public
      * @var ?int
     */
    public $receiver_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $message;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $answer;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $time = null;  // CURRENT_TIMESTAMP

    /**
      * Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema o al contest owner si no esta ligado a un problema.
      * @access public
      * @var ?int
     */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $problemset_id;

    /**
      * Sólo las clarificaciones que el problemsetter marque como publicables aparecerán en la lista que todos pueden ver.
      * @access public
      * @var bool
     */
    public $public = false;
}
