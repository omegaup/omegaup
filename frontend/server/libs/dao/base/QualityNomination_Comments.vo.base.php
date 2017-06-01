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
    /**
     * Constructor de QualityNominationComments
     *
     * Para construir un objeto de tipo QualityNominationComments debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['qualitynomination_comment_id'])) {
            $this->qualitynomination_comment_id = $data['qualitynomination_comment_id'];
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = $data['qualitynomination_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['vote'])) {
            $this->vote = $data['vote'];
        }
        if (isset($data['contents'])) {
            $this->contents = $data['contents'];
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
    public $qualitynomination_comment_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $qualitynomination_id;

    /**
      * El usuario que emitió el comentario
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      * Fecha de creacion de este comentario
      * @access public
      * @var timestamp
      */
    public $time;

    /**
      * El voto emitido en este comentario. En el rango de [-2, +2]
      * @access public
      * @var tinyint(1)
      */
    public $vote;

    /**
      * El contenido del comentario
      * @access public
      * @var text
      */
    public $contents;
}
