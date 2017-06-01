<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table QualityNomination_Reviewers.
 *
 * VO does not have any behaviour.
 * @access public
 */
class QualityNominationReviewers extends VO {
    /**
     * Constructor de QualityNominationReviewers
     *
     * Para construir un objeto de tipo QualityNominationReviewers debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = $data['qualitynomination_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime([]);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $qualitynomination_id;

    /**
      * El revisor al que fue asignado esta nominación
      * @access public
      * @var int(11)
      */
    public $user_id;
}
