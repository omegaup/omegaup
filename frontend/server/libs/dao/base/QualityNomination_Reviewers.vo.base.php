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
    const FIELD_NAMES = [
        'qualitynomination_id' => true,
        'user_id' => true,
    ];

    /**
     * Constructor de QualityNominationReviewers
     *
     * Para construir un objeto de tipo QualityNominationReviewers debera llamarse a el constructor
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
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = (int)$data['qualitynomination_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $qualitynomination_id;

    /**
      * El revisor al que fue asignado esta nominación
      * Llave Primaria
      * @access public
      * @var int
     */
    public $user_id;
}
