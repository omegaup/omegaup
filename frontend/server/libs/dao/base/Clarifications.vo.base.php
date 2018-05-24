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
    /**
     * Constructor de Clarifications
     *
     * Para construir un objeto de tipo Clarifications debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['clarification_id'])) {
            $this->clarification_id = $data['clarification_id'];
        }
        if (isset($data['author_id'])) {
            $this->author_id = $data['author_id'];
        }
        if (isset($data['receiver_id'])) {
            $this->receiver_id = $data['receiver_id'];
        }
        if (isset($data['message'])) {
            $this->message = $data['message'];
        }
        if (isset($data['answer'])) {
            $this->answer = $data['answer'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = $data['problem_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['public'])) {
            $this->public = $data['public'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['time']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $clarification_id;

    /**
      * Autor de la clarificación.
      * @access public
      * @var int(11)
      */
    public $author_id;

    /**
      * Usuario que recibirá el mensaje
      * @access public
      * @var int(11)
      */
    public $receiver_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var text
      */
    public $message;

    /**
      *  [Campo no documentado]
      * @access public
      * @var text,
      */
    public $answer;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $time;

    /**
      * Lo ideal es que la clarificacion le llegue al problemsetter que escribio el problema o al contest owner si no esta ligado a un problema.
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      * Sólo las clarificaciones que el problemsetter marque como publicables aparecerán en la lista que todos pueden ver.
      * @access public
      * @var tinyint(1)
      */
    public $public;
}
