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
    function __construct(?array $data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['qualitynomination_log_id'])) {
            $this->qualitynomination_log_id = (int)$data['qualitynomination_log_id'];
        }
        if (isset($data['qualitynomination_id'])) {
            $this->qualitynomination_id = (int)$data['qualitynomination_id'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
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
    public $qualitynomination_log_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $qualitynomination_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $time = null;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $from_status = 'open';

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $to_status = 'open';

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $rationale;
}
