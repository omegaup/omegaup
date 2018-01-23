<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table QualityNomination_Log.
 *
 * VO does not have any behaviour.
 * @access public
 */
class QualityNominationLog extends VO {
    /**
     * Constructor de QualityNominationLog
     *
     * Para construir un objeto de tipo QualityNominationLog debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['qualitynomination_log_id'])) {
            $this->qualitynomination_log_id = $data['qualitynomination_log_id'];
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = $data['qualitynomination_id'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        if (isset($data['from_status'])) {
            $this->from_status = $data['from_status'];
        }
        if (isset($data['to_status'])) {
            $this->to_status = $data['to_status'];
        }
        if (isset($data['rationale'])) {
            $this->rationale = $data['rationale'];
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
    public $qualitynomination_log_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $qualitynomination_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $time;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('open','approved','denied')
      */
    public $from_status;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('open','approved','denied')
      */
    public $to_status;

    /**
      *  [Campo no documentado]
      * @access public
      * @var text
      */
    public $rationale;
}
